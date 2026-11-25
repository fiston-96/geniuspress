<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Transfer\SiteExporter;
use App\Transfer\SiteImporter;
use Illuminate\Http\Request;
use Throwable;

/** Page « Sauvegarde » : exporter le site dans un zip, ou importer un zip. */
class TransferController extends Controller
{
    public function index()
    {
        return view('admin.transfer', [
            'maxUpload' => min(self::bytes(ini_get('upload_max_filesize')), self::bytes(ini_get('post_max_size'))),
        ]);
    }

    public function export(SiteExporter $exporter)
    {
        @set_time_limit(300);
        $path = $exporter->export();

        return response()->download($path, basename($path), ['Content-Type' => 'application/zip'])->deleteFileAfterSend();
    }

    public function import(Request $request, SiteImporter $importer)
    {
        $request->validate([
            'archive' => ['required', 'file', 'mimes:zip', 'max:512000'],
            'mode' => ['required', 'in:merge,replace'],
        ]);
        @set_time_limit(300);

        try {
            $report = $importer->import(
                $request->file('archive')->getRealPath(),
                $request->user(),
                replace: $request->input('mode') === 'replace',
                withSettings: $request->boolean('settings'),
            );
        } catch (Throwable $e) {
            report($e);

            return back()->withErrors(['archive' => __('Import impossible : :error', ['error' => $e->getMessage()])]);
        }

        return back()->with('status', __('Import terminé : :pages pages et :media médias.', ['pages' => $report['pages'], 'media' => $report['media']]).($report['posts'] + $report['layouts'] ? ' '.__('Aussi : :posts articles, :terms catégories et étiquettes, :layouts gabarits du thème.', ['posts' => $report['posts'], 'terms' => $report['terms'], 'layouts' => $report['layouts']]) : ''))
            ->with('warnings', $report['warnings']);
    }

    private static function bytes(string|false $value): int
    {
        $value = trim((string) $value);
        $unit = strtolower(substr($value, -1));
        $n = (int) $value;

        return match ($unit) {
            'g' => $n * 1024 ** 3,
            'm' => $n * 1024 ** 2,
            'k' => $n * 1024,
            default => $n,
        };
    }
}
