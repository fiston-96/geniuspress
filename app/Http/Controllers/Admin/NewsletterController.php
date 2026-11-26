<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Mail\NewsletterMail;
use App\Models\NewsletterCampaign;
use App\Models\NewsletterSubscriber;
use App\Models\Setting;
use App\Newsletter\Newsletter;
use App\Newsletter\SendNewsletter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Throwable;

/** Newsletter dans l'administration : abonnés, campagnes, envoi. Réservé aux administrateurs. */
class NewsletterController extends Controller
{
    public function __construct(private Newsletter $newsletter) {}

    public function index(Request $request)
    {
        $status = in_array($request->query('statut'), ['subscribed', 'pending', 'unsubscribed'], true) ? $request->query('statut') : null;
        $search = trim((string) $request->query('q'));

        return view('admin.newsletter.index', [
            'counts' => NewsletterSubscriber::query()->selectRaw('status, count(*) as n')->groupBy('status')->pluck('n', 'status'),
            'subscribers' => NewsletterSubscriber::query()
                ->when($status, fn ($q) => $q->where('status', $status))
                ->when($search !== '', fn ($q) => $q->where(fn ($w) => $w->where('email', 'like', '%'.$search.'%')->orWhere('name', 'like', '%'.$search.'%')))
                ->latest('id')->paginate(30)->withQueryString(),
            'campaigns' => NewsletterCampaign::query()->latest('id')->limit(20)->get(),
            'status' => $status,
            'search' => $search,
            'perMinute' => Newsletter::perMinute(),
            'pending' => $this->newsletter->pending(),
        ]);
    }

    /** Abonnés inscrits, en CSV (UTF-8 avec BOM pour Excel). */
    public function export()
    {
        return response()->streamDownload(function () {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF");
            fputcsv($out, [__('E-mail'), __('Prénom'), __('Langue'), __('Inscrit le'), __('Source')], ';');
            NewsletterSubscriber::query()->subscribed()->orderBy('id')->chunkById(500, function ($chunk) use ($out) {
                foreach ($chunk as $s) {
                    fputcsv($out, [$s->email, $s->name, $s->locale, $s->confirmed_at?->toDateTimeString(), $s->source], ';');
                }
            });
            fclose($out);
        }, 'abonnes-newsletter-'.now()->format('Y-m-d').'.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    public function destroySubscriber(NewsletterSubscriber $subscriber)
    {
        $subscriber->delete();

        return back()->with('status', __('Abonné supprimé.'));
    }

    public function settings(Request $request)
    {
        $data = $request->validate(['newsletter_per_minute' => ['required', 'integer', 'min:1', 'max:600']]);
        Setting::write($data);

        return back()->with('status', __('Rythme d’envoi enregistré.'));
    }

    // ─── Campagnes ──────────────────────────────────────────────────────

    public function create()
    {
        return view('admin.newsletter.campaign', ['campaign' => new NewsletterCampaign, 'recipients' => $this->recipients(null)]);
    }

    public function store(Request $request)
    {
        $campaign = NewsletterCampaign::create($this->validated($request) + ['user_id' => $request->user()->id, 'status' => NewsletterCampaign::DRAFT]);

        return redirect()->route('admin.newsletter.campaigns.edit', $campaign)->with('status', __('Brouillon enregistré.'));
    }

    public function edit(NewsletterCampaign $campaign)
    {
        return view('admin.newsletter.campaign', ['campaign' => $campaign, 'recipients' => $this->recipients($campaign->locale)]);
    }

    public function update(Request $request, NewsletterCampaign $campaign)
    {
        abort_unless($campaign->isDraft(), 403, __('Une campagne envoyée ne se modifie plus.'));
        $campaign->update($this->validated($request));

        return back()->with('status', __('Brouillon enregistré.'));
    }

    public function destroy(NewsletterCampaign $campaign)
    {
        abort_if($campaign->status === NewsletterCampaign::SENDING, 403, __('Attendez la fin de l’envoi.'));
        $campaign->delete();

        return redirect()->route('admin.newsletter.index')->with('status', __('Campagne supprimée.'));
    }

    /** Envoi d'essai à l'administrateur connecté (le lien de désinscription n'y est pas actif). */
    public function test(Request $request, NewsletterCampaign $campaign)
    {
        $preview = new NewsletterSubscriber(['email' => $request->user()->email, 'name' => $request->user()->name, 'locale' => $request->user()->locale ?? 'fr']);
        $preview->token = Str::random(48);
        try {
            Mail::to($request->user()->email)->send(new NewsletterMail($campaign, $preview));
        } catch (Throwable $e) {
            report($e);

            return back()->with('warnings', [__('L’e-mail d’essai n’a pas pu partir. Vérifiez les réglages d’envoi des e-mails du serveur.')]);
        }

        return back()->with('status', __('E-mail d’essai envoyé à :email.', ['email' => $request->user()->email]));
    }

    public function send(NewsletterCampaign $campaign)
    {
        abort_unless($campaign->isDraft(), 403);
        $count = $this->newsletter->queue($campaign);
        if ($count === 0) {
            $campaign->update(['status' => NewsletterCampaign::SENT, 'sent_at' => now()]);

            return redirect()->route('admin.newsletter.index')->with('warnings', [__('Aucun abonné inscrit ne correspond : rien n’a été envoyé.')]);
        }
        config('queue.default') === 'sync' ? SendNewsletter::dispatchAfterResponse() : SendNewsletter::dispatch();

        return redirect()->route('admin.newsletter.index')->with('status', trans_choice(
            '{1} Envoi lancé à 1 abonné.|[2,*] Envoi lancé à :count abonnés, au rythme de :rate par minute.', $count, ['rate' => Newsletter::perMinute()]
        ));
    }

    private function validated(Request $request): array
    {
        $data = $request->validate([
            'subject' => ['required', 'string', 'max:190'],
            'content' => ['required', 'string', 'max:50000'],
            'locale' => ['nullable', 'in:'.implode(',', array_keys((array) config('geniuspress.ui_locales')))],
        ], [], ['subject' => __('objet'), 'content' => __('contenu')]);
        $data['content'] = strip_tags($data['content']);

        return $data;
    }

    private function recipients(?string $locale): int
    {
        return NewsletterSubscriber::query()->subscribed()->when($locale, fn ($q) => $q->where('locale', $locale))->count();
    }
}
