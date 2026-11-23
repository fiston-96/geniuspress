<?php

namespace App\Http\Controllers;

use App\Builder\DefaultContent;
use App\Models\Page;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rules\Password;

class InstallController extends Controller
{
    public function show()
    {
        abort_if(User::query()->exists(), 404);

        return view('install');
    }

    public function store(Request $request)
    {
        abort_if(User::query()->exists(), 404);

        $data = $request->validate([
            'site_name' => ['required', 'string', 'max:120'],
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:190'],
            'password' => ['required', 'confirmed', Password::min(10)->letters()->numbers()],
        ]);

        $user = DB::transaction(function () use ($data) {
            $user = User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => $data['password'],
                'role' => User::ROLE_ADMIN,
            ]);

            Setting::write(['site_name' => $data['site_name']]);

            Page::create([
                'title' => 'Accueil',
                'slug' => 'accueil',
                'is_home' => true,
                'status' => Page::STATUS_PUBLISHED,
                'published_at' => now(),
                'author_id' => $user->id,
                'content' => DefaultContent::home($data['site_name']),
            ]);

            return $user;
        });

        Cache::forever('gp_installed', true);
        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route('admin.dashboard')->with('status', __('Bienvenue sur GeniusPress !'));
    }
}
