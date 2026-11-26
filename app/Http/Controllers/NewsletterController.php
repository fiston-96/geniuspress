<?php

namespace App\Http\Controllers;

use App\Builder\ContentSanitizer;
use App\Builder\Renderer;
use App\Models\Page;
use App\Newsletter\Newsletter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Validator;
use Throwable;

/**
 * Newsletter côté visiteurs : inscription depuis le widget, confirmation (lien de l'e-mail),
 * désinscription (lien de chaque e-mail, ou bouton « Se désinscrire » de la messagerie).
 */
class NewsletterController extends Controller
{
    public function __construct(private Newsletter $newsletter) {}

    public function subscribe(Request $request)
    {
        $page = Page::query()->published()->find((int) $request->input('page'));
        $widgetId = (string) $request->input('widget');
        $found = false;
        if ($page) {
            Renderer::walk(app(ContentSanitizer::class)->sanitize($page->content)['elements'], function ($node) use ($widgetId, &$found) {
                $found = $found || (($node['type'] ?? '') === 'newsletter' && ($node['id'] ?? '') === $widgetId);
            });
        }
        abort_unless($found, 404);

        $back = fn () => redirect()->to(url()->previous().'#gp-news-'.$widgetId);

        // Robots : champ piège rempli ou envoi trop rapide. Ils croient avoir réussi ; rien n'est enregistré.
        try {
            $age = time() - (int) Crypt::decryptString((string) $request->input('_ts'));
        } catch (Throwable) {
            $age = 0;
        }
        if (filled($request->input('website')) || $age < 3) {
            return $back()->with('gp_news_sent', $widgetId);
        }

        $validator = Validator::make($request->all(), [
            'email' => ['required', 'email', 'max:190'],
            'name' => ['nullable', 'string', 'max:120'],
        ], [], ['email' => __('adresse e-mail'), 'name' => __('prénom')]);
        if ($validator->fails()) {
            return $back()->withErrors($validator, 'gp_news_'.$widgetId)->withInput($request->only(['email', 'name']));
        }

        $this->newsletter->subscribe($request->input('email'), $request->input('name'), $page->locale ?? app()->getLocale(), $page->title, false, $request->ip());

        return $back()->with('gp_news_sent', $widgetId);
    }

    public function confirm(string $token)
    {
        $subscriber = $this->newsletter->confirm($token);

        return $this->page(
            $subscriber && $subscriber->status === 'subscribed' ? __('Inscription confirmée') : __('Lien non valable'),
            $subscriber && $subscriber->status === 'subscribed'
                ? __('Merci ! Vous recevrez nos prochaines nouvelles à :email.', ['email' => $subscriber->email])
                : __('Ce lien n’est plus valable. Inscrivez-vous à nouveau depuis le site.'),
        );
    }

    /** Page de désinscription : un bouton, pour qu'un logiciel qui ouvre les liens ne désinscrive personne. */
    public function showUnsubscribe(string $token)
    {
        return $this->page(__('Se désinscrire de la newsletter'), __('Vous ne recevrez plus nos e-mails. Vous pourrez vous réinscrire à tout moment depuis le site.'), $token);
    }

    /** Désinscription (bouton de la page, ou en un clic depuis la messagerie : List-Unsubscribe-Post). */
    public function unsubscribe(string $token)
    {
        $this->newsletter->unsubscribe($token);

        return $this->page(__('Vous êtes désinscrit'), __('Vous ne recevrez plus la newsletter. À bientôt peut-être !'));
    }

    private function page(string $title, string $message, ?string $unsubscribeToken = null)
    {
        return response()->view('newsletter.page', compact('title', 'message', 'unsubscribeToken'));
    }
}
