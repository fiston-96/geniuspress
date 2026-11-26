<?php

namespace App\Platform\Models;

use App\Notifications\ResetPasswordLink;
use Illuminate\Contracts\Translation\HasLocalePreference;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

/**
 * Exploitant de GeniusPress : se connecte à la console (/console, garde « operator »).
 * Ce n'est ni un utilisateur du CMS (users) ni un client (platform_customers).
 */
#[Fillable(['name', 'email', 'password', 'locale', 'status'])]
#[Hidden(['password', 'remember_token'])]
class Operator extends Authenticatable implements HasLocalePreference
{
    use Notifiable;

    protected $table = 'platform_operators';

    protected $attributes = ['status' => 'active', 'locale' => 'fr'];

    protected function casts(): array
    {
        return ['password' => 'hashed', 'last_login_at' => 'datetime'];
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function preferredLocale(): ?string
    {
        return $this->locale ?: null;
    }

    public function sendPasswordResetNotification($token): void
    {
        $this->notify(new ResetPasswordLink(route('console.password.reset', ['token' => $token, 'email' => $this->email]), 30));
    }
}
