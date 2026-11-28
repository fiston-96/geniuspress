<?php

use Illuminate\Foundation\Application;
use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

// Determine if the application is in maintenance mode...
if (file_exists($maintenance = __DIR__.'/../storage/framework/maintenance.php')) {
    require $maintenance;
}

// Site servi depuis le dossier de GeniusPress (et non depuis public/) : le .htaccess racine renvoie
// chaque adresse vers public/. Les adresses visibles ne contiennent pas « /public » : on donne à
// Laravel le bon dossier de base (ex. http://localhost/geniuspress/), sinon il le perd.
$script = $_SERVER['SCRIPT_NAME'] ?? '';
$uri = strtok($_SERVER['REQUEST_URI'] ?? '', '?');
if (str_ends_with($script, '/public/index.php') && ! str_starts_with((string) $uri, substr($script, 0, -strlen('/index.php')))) {
    $_SERVER['SCRIPT_NAME'] = $_SERVER['PHP_SELF'] = substr($script, 0, -strlen('/public/index.php')).'/index.php';
}
unset($script, $uri);

// Register the Composer autoloader...
require __DIR__.'/../vendor/autoload.php';

// Bootstrap Laravel and handle the request...
/** @var Application $app */
$app = require_once __DIR__.'/../bootstrap/app.php';

$app->handleRequest(Request::capture());
