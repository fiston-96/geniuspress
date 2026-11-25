<?php

namespace App\Hosting\Http;

use App\Hosting\HostingAccounts;
use App\Hosting\HostingException;
use App\Hosting\Site;
use App\Hosting\SiteProvisioner;
use App\Hosting\Trials;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

/** Parcours « Essayer GeniusPress » d'un compte connecté de la plateforme. */
class TrialController extends Controller
{
    public function __construct(private Trials $trials, private SiteProvisioner $provisioner, private HostingAccounts $accounts) {}

    public function show(Request $request)
    {
        $user = $request->user();
        $site = $this->trials->siteFor($user);

        return view('hosting.trial', [
            'site' => $site,
            'status' => $this->statusOf($site),
            'refusal' => $site ? null : $this->trials->refusal($user),
            // Dernière tentative échouée (dans l'heure) : on le dit au lieu de réafficher le formulaire sans explication.
            'failed' => ! $site && Site::query()->where('account_id', $this->accounts->accountId($user))
                ->where('status', Site::STATUS_FAILED)->where('updated_at', '>=', now()->subHour())->exists(),
            'suggested' => $this->trials->suggestSlug((string) $user->name),
            'domain' => config('hosting.domain'),
            'plan' => $this->accounts->trialPlan($user),
            'upgradeUrl' => $site ? $this->accounts->upgradeUrl($site) : null,
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'slug' => ['required', 'string', 'max:40'],
        ]);
        $slug = strtolower(trim($data['slug']));
        if ($error = $this->trials->slugError($slug)) {
            throw ValidationException::withMessages(['slug' => $error]);
        }

        try {
            $this->trials->start($request->user(), $data['name'], $slug);
        } catch (HostingException $e) {
            return back()->withInput()->with('warnings', [$e->getMessage()]);
        }

        return redirect()->route('hosting.trial')->with('status', __('Votre site d’essai est en préparation. Il sera prêt dans quelques secondes.'));
    }

    /** État du site, interrogé par la page pendant la préparation. */
    public function status(Request $request)
    {
        $site = $this->trials->siteFor($request->user());

        return response()->json(['status' => $this->statusOf($site)]);
    }

    /** Connexion en un clic à l'administration du site. */
    public function open(Request $request)
    {
        $site = $this->trials->siteFor($request->user());
        if (! $site || ! in_array($this->statusOf($site), [Site::STATUS_ACTIVE, Site::STATUS_EXPIRED], true)) {
            return redirect()->route('hosting.trial')->with('warnings', [__('Ce site n’est pas disponible.')]);
        }

        return redirect()->away($this->provisioner->loginUrl($site));
    }

    /** État affiché : « securing » tant que le certificat HTTPS du site n'est pas prêt. */
    private function statusOf(?Site $site): ?string
    {
        $status = $site?->effectiveStatus();

        return $status === Site::STATUS_ACTIVE && $site->awaitingCertificate() ? 'securing' : $status;
    }
}
