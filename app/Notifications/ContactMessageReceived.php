<?php

namespace App\Notifications;

use App\Models\FormSubmission;
use Illuminate\Support\Str;

/** Nouveau message d'un formulaire de contact (cloche des administrateurs ; l'e-mail part déjà à part). */
class ContactMessageReceived extends Notice
{
    public function __construct(private FormSubmission $submission, private string $page) {}

    public function title(): array
    {
        return ['Nouveau message de :name', ['name' => $this->submission->data['name'] ?? $this->submission->data['email'] ?? '?']];
    }

    public function body(): ?array
    {
        return ['Page « :page » : :excerpt', ['page' => $this->page, 'excerpt' => Str::limit((string) ($this->submission->data['message'] ?? ''), 120)]];
    }

    public function url(): ?string
    {
        return route('admin.messages.show', $this->submission);
    }

    public function icon(): string
    {
        return 'mail';
    }
}
