<?php

namespace App\Http\Controllers\Admin;

use App\Builder\ContentSanitizer;
use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Site\Navigation;
use Illuminate\Http\Request;

/** Page « Apparence » : en-tête, menus par langue, pied de page, icône du site. */
class AppearanceController extends Controller
{
    public function edit()
    {
        $locales = (array) Setting::read('locales', ['fr']);

        return view('admin.appearance', [
            'nav' => Navigation::all(),
            'locales' => array_intersect_key(SettingController::AVAILABLE_LOCALES, array_flip($locales)),
            'faviconUrl' => Setting::read('favicon_url'),
        ]);
    }

    public function update(Request $request)
    {
        $request->validate([
            'locales' => ['array'],
            'favicon_url' => ['nullable', 'string', 'max:2000'],
        ]);

        Navigation::save($request->all(), (array) Setting::read('locales', ['fr']));
        Setting::write(['favicon_url' => ContentSanitizer::url($request->input('favicon_url'))]);

        return back()->with('status', __('Apparence enregistrée.'));
    }
}
