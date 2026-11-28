<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Media;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class MediaController extends Controller
{
    private const MIMES = 'jpg,jpeg,png,gif,webp,svg,pdf,mp4,webm';

    /** Vidéos (fonds vidéo des conteneurs) : jusqu'à 30 Mo ; images et documents : 10 Mo. */
    private const VIDEOS = ['mp4', 'webm'];

    public function index(Request $request)
    {
        $media = Media::query()->latest()->paginate(48);

        if ($request->wantsJson()) {
            return $media;
        }

        return view('admin.media.index', compact('media'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'file' => ['required', 'file', 'max:30720', 'mimes:'.self::MIMES],
            'alt' => ['nullable', 'string', 'max:300'],
        ]);

        $file = $request->file('file');
        $ext = strtolower($file->guessExtension() ?: $file->getClientOriginalExtension());
        abort_unless(in_array($ext, explode(',', self::MIMES), true), 422, __('Type de fichier non autorisé.'));
        abort_if(! in_array($ext, self::VIDEOS, true) && $file->getSize() > 10 * 1024 * 1024, 422, __('Les images et documents sont limités à 10 Mo.'));

        // Les SVG peuvent contenir du JavaScript : on refuse ceux qui en contiennent.
        if ($ext === 'svg' && preg_match('/<script|on\w+\s*=|javascript:/i', (string) file_get_contents($file->getRealPath()))) {
            abort(422, __('Ce fichier SVG contient du code et a été refusé.'));
        }

        $name = Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME)) ?: 'fichier';
        $path = $file->storeAs('uploads/'.now()->format('Y/m'), $name.'-'.Str::lower(Str::random(6)).'.'.$ext, 'public');

        [$w, $h] = in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp'], true) ? (@getimagesize($file->getRealPath()) ?: [null, null]) : [null, null];

        $media = Media::create([
            'user_id' => $request->user()->id,
            'disk' => 'public',
            'path' => $path,
            'original_name' => Str::limit($file->getClientOriginalName(), 250, ''),
            'mime_type' => $file->getMimeType() ?? 'application/octet-stream',
            'size' => $file->getSize(),
            'width' => $w,
            'height' => $h,
            'alt' => $request->input('alt'),
        ]);

        return $request->wantsJson()
            ? response()->json($media, 201)
            : back()->with('status', __('Fichier ajouté.'));
    }

    public function update(Request $request, Media $media)
    {
        $media->update($request->validate(['alt' => ['nullable', 'string', 'max:300']]));

        return $request->wantsJson() ? $media : back()->with('status', __('Texte alternatif enregistré.'));
    }

    public function destroy(Request $request, Media $media)
    {
        Storage::disk($media->disk)->delete($media->path);
        $media->delete();

        return $request->wantsJson() ? response()->noContent() : back()->with('status', __('Fichier supprimé.'));
    }
}
