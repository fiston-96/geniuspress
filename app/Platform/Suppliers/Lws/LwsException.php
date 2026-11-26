<?php

namespace App\Platform\Suppliers\Lws;

use App\Platform\PlatformException;

/** Erreur renvoyée par l'API LWS (message en français ; code = statut LWS ou HTTP, 0 sans réponse). */
class LwsException extends PlatformException {}
