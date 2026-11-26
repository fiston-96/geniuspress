<?php

namespace App\Mail;

use App\Models\NewsletterCampaign;
use App\Models\NewsletterSubscriber;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Mail\Mailables\Headers;

/**
 * Une campagne envoyée à un abonné. Le contenu est du Markdown simple écrit dans
 * l'administration (les balises HTML sont retirées). Désinscription en un clic : lien en bas
 * de l'e-mail et en-têtes List-Unsubscribe (boutons des messageries).
 */
class NewsletterMail extends Mailable
{
    use Queueable;

    public function __construct(public NewsletterCampaign $campaign, public NewsletterSubscriber $subscriber) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: $this->campaign->subject);
    }

    public function headers(): Headers
    {
        $url = route('newsletter.unsubscribe', $this->subscriber->token);

        return new Headers(text: [
            'List-Unsubscribe' => '<'.$url.'>',
            'List-Unsubscribe-Post' => 'List-Unsubscribe=One-Click',
        ]);
    }

    public function content(): Content
    {
        return new Content(markdown: 'mail.newsletter', with: [
            'body' => strip_tags($this->campaign->content),
            'unsubscribeUrl' => route('newsletter.unsubscribe', $this->subscriber->token),
        ]);
    }
}
