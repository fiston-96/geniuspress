<?php

namespace App\Platform\Console;

use App\Platform\Models\Operator;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

/**
 * Comptes de la console d'exploitation (/console). Pas d'inscription en ligne : un exploitant
 * se crée ici. Sans --password, un mot de passe est généré ; avec --password-file, il est écrit
 * dans ce fichier (droits 600) au lieu d'être affiché.
 */
class OperatorCommand extends Command
{
    protected $signature = 'geniuspress:operator
        {action : create, disable, enable ou list}
        {email? : adresse e-mail de l’exploitant}
        {--name= : nom affiché}
        {--password= : mot de passe (10 caractères minimum)}
        {--password-file= : fichier où écrire le mot de passe généré}';

    protected $description = 'Gère les comptes de la console d’exploitation';

    public function handle(): int
    {
        return match ($this->argument('action')) {
            'create' => $this->create(),
            'disable', 'enable' => $this->toggle($this->argument('action') === 'enable'),
            'list' => $this->listOperators(),
            default => $this->fail(__('Action inconnue : create, disable, enable ou list.')),
        };
    }

    private function create(): int
    {
        $email = Str::lower(trim((string) $this->argument('email')));
        if (! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->error(__('Adresse e-mail invalide.'));

            return self::FAILURE;
        }
        if (Operator::query()->where('email', $email)->exists()) {
            $this->error(__('Un exploitant existe déjà avec cette adresse.'));

            return self::FAILURE;
        }
        $password = (string) ($this->option('password') ?: Str::password(20, symbols: false));
        if (mb_strlen($password) < 10) {
            $this->error(__('Le mot de passe doit compter au moins 10 caractères.'));

            return self::FAILURE;
        }

        Operator::create(['name' => $this->option('name') ?: Str::before($email, '@'), 'email' => $email, 'password' => $password]);

        if ($file = $this->option('password-file')) {
            file_put_contents($file, __('Console GeniusPress').PHP_EOL.$email.PHP_EOL.$password.PHP_EOL);
            @chmod($file, 0600);
            $this->info(__('Exploitant créé. Mot de passe écrit dans :file.', ['file' => $file]));
        } elseif (! $this->option('password')) {
            $this->info(__('Exploitant créé. Mot de passe (affiché une seule fois) : :password', ['password' => $password]));
        } else {
            $this->info(__('Exploitant créé.'));
        }

        return self::SUCCESS;
    }

    private function toggle(bool $enable): int
    {
        $operator = Operator::query()->where('email', Str::lower((string) $this->argument('email')))->first();
        if (! $operator) {
            $this->error(__('Exploitant introuvable.'));

            return self::FAILURE;
        }
        $operator->forceFill(['status' => $enable ? 'active' : 'disabled', 'remember_token' => Str::random(60)])->save();
        $this->info($enable ? __('Compte réactivé.') : __('Compte désactivé.'));

        return self::SUCCESS;
    }

    private function listOperators(): int
    {
        $this->table([__('E-mail'), __('Nom'), __('État'), __('Dernière connexion')], Operator::query()->orderBy('id')->get()
            ->map(fn (Operator $o) => [$o->email, $o->name, $o->status, $o->last_login_at?->toDateTimeString() ?? '—'])->all());

        return self::SUCCESS;
    }
}
