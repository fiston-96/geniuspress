<?php

namespace App\Install;

/** Vérifie que le serveur peut faire fonctionner GeniusPress. */
class Requirements
{
    public const PHP_VERSION = '8.3.0';

    /** @return list<array{label: string, ok: bool, required: bool, detail: string}> */
    public function check(EnvironmentFile $env): array
    {
        $checks = [];
        $add = function (string $label, bool $ok, bool $required, string $detail = '') use (&$checks) {
            $checks[] = compact('label', 'ok', 'required', 'detail');
        };

        $add(__('PHP :version ou plus récent', ['version' => '8.3']), version_compare(PHP_VERSION, self::PHP_VERSION, '>='), true, __('Version installée : :version', ['version' => PHP_VERSION]));

        foreach (['pdo', 'mbstring', 'openssl', 'tokenizer', 'xml', 'ctype', 'fileinfo', 'filter'] as $ext) {
            $add(__('Extension PHP :name', ['name' => $ext]), extension_loaded($ext), true);
        }
        $add(__('Pilote de base de données (pdo_mysql ou pdo_sqlite)'), extension_loaded('pdo_mysql') || extension_loaded('pdo_sqlite'), true,
            implode(', ', array_filter(['pdo_mysql', 'pdo_sqlite'], 'extension_loaded')) ?: __('aucun'));
        foreach (['intl' => __('formats de dates et de nombres'), 'curl' => __('assistant IA'), 'gd' => __('images'), 'zip' => __('export, import et plugins')] as $ext => $use) {
            $add(__('Extension PHP :name', ['name' => $ext]), extension_loaded($ext), false, __('Recommandée : :use', ['use' => $use]));
        }

        $add(__('Fichier .env modifiable'), $env->isWritable(), true, $env->path());
        foreach (['storage', 'storage/app', 'storage/framework', 'storage/logs', 'bootstrap/cache'] as $dir) {
            $add(__('Dossier :dir modifiable', ['dir' => $dir]), is_dir(base_path($dir)) && is_writable(base_path($dir)), true);
        }
        $add(__('Dossier :dir modifiable', ['dir' => 'public']), is_writable(public_path()), false, __('Recommandé : lien vers les fichiers envoyés'));

        return $checks;
    }

    public function passes(array $checks): bool
    {
        foreach ($checks as $c) {
            if ($c['required'] && ! $c['ok']) {
                return false;
            }
        }

        return true;
    }
}
