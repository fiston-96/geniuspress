<?php

namespace App\Models;

use App\Notifications\ResetPasswordLink;
use Database\Factories\UserFactory;
use Illuminate\Contracts\Translation\HasLocalePreference;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable(['name', 'email', 'password', 'role', 'locale'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable implements HasLocalePreference
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    public const ROLE_ADMIN = 'admin';
    public const ROLE_EDITOR = 'editor';

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function isAdmin(): bool
    {
        return $this->role === self::ROLE_ADMIN;
    }

    /** Langue des e-mails et notifications : celle de l'interface choisie par la personne. */
    public function preferredLocale(): ?string
    {
        return $this->locale ?: null;
    }

    /** Mot de passe oublié : lien vers la page de l'administration (config/auth.php, courtier users). */
    public function sendPasswordResetNotification($token): void
    {
        $this->notify(new ResetPasswordLink(route('password.reset', ['token' => $token, 'email' => $this->email])));
    }

    public function pages(): HasMany
    {
        return $this->hasMany(Page::class, 'author_id');
    }
}
