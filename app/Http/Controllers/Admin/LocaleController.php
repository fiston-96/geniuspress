<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/** Change la langue de l'interface pour l'utilisateur connecté. */
class LocaleController extends Controller
{
    public function __invoke(Request $request)
    {
        $data = $request->validate([
            'locale' => ['required', Rule::in(array_keys(config('geniuspress.ui_locales')))],
        ]);

        $request->user()->update(['locale' => $data['locale']]);
        app()->setLocale($data['locale']);

        return back()->with('status', __('Langue de l’interface modifiée.'));
    }
}
