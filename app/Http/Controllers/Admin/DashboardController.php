<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Media;
use App\Models\Page;

class DashboardController extends Controller
{
    public function __invoke()
    {
        return view('admin.dashboard', [
            'stats' => [
                'pages' => Page::count(),
                'published' => Page::published()->count(),
                'drafts' => Page::where('status', Page::STATUS_DRAFT)->count(),
                'media' => Media::count(),
            ],
            'recent' => Page::latest('updated_at')->limit(6)->get(),
        ]);
    }
}
