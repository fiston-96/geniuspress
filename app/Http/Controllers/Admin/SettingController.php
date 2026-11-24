<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AiRequest;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;

class SettingController extends Controller
{
    public const AVAILABLE_LOCALES = ['fr' => 'Français', 'en' => 'English', 'sw' => 'Kiswahili', 'ln' => 'Lingála', 'pt' => 'Português', 'es' => 'Español', 'ar' => 'العربية', 'rw' => 'Kinyarwanda'];

    public const FONTS = ['Inter', 'Poppins', 'Montserrat', 'Roboto', 'Open Sans', 'Lato', 'Nunito', 'Playfair Display', 'Merriweather'];

    public function edit()
    {
        return view('admin.settings', [
            'settings' => Setting::allValues(),
            'availableLocales' => self::AVAILABLE_LOCALES,
            'fonts' => self::FONTS,
            'aiConfigured' => app(\App\Ai\LlmGateway::class)->isConfigured(),
            'aiUsage' => AiRequest::query()->where('created_at', '>=', now()->startOfMonth())
                ->selectRaw('count(*) as requests, coalesce(sum(input_tokens),0) as input_tokens, coalesce(sum(output_tokens),0) as output_tokens')->first(),
        ]);
    }

    public function update(Request $request)
    {
        $color = ['required', 'regex:/^#[0-9a-fA-F]{6}$/'];
        $data = $request->validate([
            'site_name' => ['required', 'string', 'max:120'],
            'site_tagline' => ['nullable', 'string', 'max:255'],
            'logo_url' => ['nullable', 'string', 'max:2000', 'regex:#^(https?://|/(?!/))#'],
            'primary_color' => $color,
            'secondary_color' => $color,
            'accent_color' => $color,
            'font_family' => ['required', 'in:'.implode(',', self::FONTS)],
            'heading_font' => ['nullable', 'in:'.implode(',', self::FONTS)],
            'custom_colors' => ['nullable', 'array', 'max:'.\App\Builder\DesignTokens::MAX_CUSTOM_COLORS],
            'locales' => ['required', 'array', 'min:1'],
            'locales.*' => ['in:'.implode(',', array_keys(self::AVAILABLE_LOCALES))],
            'default_locale' => ['required', 'in:'.implode(',', array_keys(self::AVAILABLE_LOCALES))],
            'organization_description' => ['nullable', 'string', 'max:3000'],
            'forms_email' => ['nullable', 'email', 'max:190'],
            'anthropic_api_key' => ['nullable', 'string', 'max:300', 'regex:/^sk-ant-[A-Za-z0-9_\-]+$/'],
            'remove_api_key' => ['nullable', 'boolean'],
        ], ['anthropic_api_key.regex' => __('La clé API doit commencer par « sk-ant- ».')]);

        // La clé API est chiffrée en base et n'est jamais renvoyée au navigateur.
        $key = $data['anthropic_api_key'] ?? null;
        unset($data['anthropic_api_key'], $data['remove_api_key']);
        if ($request->boolean('remove_api_key')) {
            $data['anthropic_api_key'] = null;
        } elseif ($key) {
            $data['anthropic_api_key'] = Crypt::encryptString($key);
            $data['anthropic_api_key_hint'] = '…'.substr($key, -4);
        }

        // La langue par défaut est toujours la première de la liste.
        $locales = array_values(array_unique(array_merge([$data['default_locale']], $data['locales'])));
        unset($data['default_locale']);
        $data['locales'] = $locales;
        $data['heading_font'] ??= null;
        $data['custom_colors'] = \App\Builder\DesignTokens::customColors($request->input('custom_colors', []));

        Setting::write($data);

        return back()->with('status', __('Réglages enregistrés.'));
    }
}
