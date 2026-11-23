<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

/**
 * Crée un compte administrateur pour le développement local.
 *
 *   php artisan db:seed --class=DevAdminSeeder
 *
 * Identifiants lus dans .env (GP_DEV_ADMIN_EMAIL / GP_DEV_ADMIN_PASSWORD).
 * Sans mot de passe défini, un mot de passe aléatoire est généré et affiché.
 */
class DevAdminSeeder extends Seeder
{
    public function run(): void
    {
        if (app()->isProduction()) {
            $this->command?->error('DevAdminSeeder est réservé au développement.');

            return;
        }

        $email = env('GP_DEV_ADMIN_EMAIL', 'admin@geniuspress.test');
        $password = env('GP_DEV_ADMIN_PASSWORD') ?: Str::password(16, symbols: false);

        User::query()->updateOrCreate(
            ['email' => $email],
            ['name' => 'Admin Dev', 'password' => $password, 'role' => User::ROLE_ADMIN],
        );

        $this->command?->info("Compte administrateur : {$email}".(env('GP_DEV_ADMIN_PASSWORD') ? ' (mot de passe défini dans .env)' : " / mot de passe : {$password}"));
    }
}
