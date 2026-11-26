<?php

namespace App\Notifications;

use App\Models\NewsletterSubscriber;

/** Nouvel abonné confirmé (cloche des administrateurs). */
class NewSubscriber extends Notice
{
    public function __construct(private NewsletterSubscriber $subscriber) {}

    public function title(): array
    {
        return ['Nouvel abonné à la newsletter : :email', ['email' => $this->subscriber->email]];
    }

    public function body(): ?array
    {
        return $this->subscriber->source ? ['Inscrit depuis : :source', ['source' => $this->subscriber->source]] : null;
    }

    public function url(): ?string
    {
        return route('admin.newsletter.index');
    }

    public function icon(): string
    {
        return 'newsletter';
    }
}
