<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

/**
 * Message reçu par un formulaire du site : formulaire de contact (nom, e-mail, téléphone,
 * sujet, message) ou formulaire avancé (champs libres : « data » contient les valeurs par
 * nom de champ et « _fields » la liste des champs {name, label, type}, dans l'ordre du formulaire).
 */
#[Fillable(['page_id', 'layout_id', 'widget_id', 'form_name', 'data', 'ip_hash', 'read_at'])]
class FormSubmission extends Model
{
    protected function casts(): array
    {
        return ['data' => 'array', 'read_at' => 'datetime'];
    }

    public function page(): BelongsTo
    {
        return $this->belongsTo(Page::class)->withTrashed();
    }

    public function layout(): BelongsTo
    {
        return $this->belongsTo(Layout::class)->withTrashed();
    }

    /** Champs à afficher : [libellé, valeur], dans l'ordre du formulaire. @return list<array{0: string, 1: string}> */
    public function fields(): array
    {
        $d = (array) $this->data;
        if ($this->isCustom()) {
            $out = [];
            foreach ($d['_fields'] as $f) {
                $value = $d[$f['name'] ?? ''] ?? null;
                if ($value !== null && $value !== '' && $value !== []) {
                    $out[] = [(string) ($f['label'] ?? $f['name']), is_array($value) ? implode(', ', $value) : (string) $value];
                }
            }

            return $out;
        }
        $labels = ['name' => __('Nom'), 'email' => __('E-mail'), 'phone' => __('Téléphone'), 'subject' => __('Sujet'), 'message' => __('Message')];

        return collect($labels)->filter(fn ($l, $key) => isset($d[$key]))->map(fn ($l, $key) => [$l, (string) $d[$key]])->values()->all();
    }

    /** Adresse e-mail de l'expéditeur (champ « email », ou premier champ e-mail du formulaire avancé). */
    public function senderEmail(): ?string
    {
        $d = (array) $this->data;
        if (! empty($d['email']) && filter_var($d['email'], FILTER_VALIDATE_EMAIL)) {
            return $d['email'];
        }
        foreach ($this->isCustom() ? $d['_fields'] : [] as $f) {
            if (($f['type'] ?? '') === 'email' && ! empty($d[$f['name']]) && is_string($d[$f['name']])) {
                return $d[$f['name']];
            }
        }

        return null;
    }

    /** Nom de l'expéditeur, s'il a été demandé. */
    public function senderName(): ?string
    {
        $d = (array) $this->data;
        foreach (['name', 'nom', 'prenom', 'nom-complet', 'full-name'] as $key) {
            if (! empty($d[$key]) && is_string($d[$key])) {
                return $d[$key];
            }
        }

        return null;
    }

    /** Aperçu pour la liste : sujet ou message, sinon les premières réponses. */
    public function excerpt(int $length = 140): string
    {
        $d = (array) $this->data;
        if (! empty($d['subject'])) {
            return Str::limit((string) $d['subject'], $length);
        }
        if (! empty($d['message']) && ! $this->isCustom()) {
            return Str::limit((string) $d['message'], $length);
        }

        return Str::limit(collect($this->fields())->map(fn ($f) => $f[0].' : '.$f[1])->implode(' · '), $length);
    }

    /** Message d'un formulaire avancé (champs libres) ? */
    public function isCustom(): bool
    {
        return is_array($this->data['_fields'] ?? null);
    }

    /** D'où vient le message : titre de la page ou nom du gabarit (popup…). */
    public function source(): string
    {
        return $this->page?->title ?? $this->layout?->name ?? '—';
    }
}
