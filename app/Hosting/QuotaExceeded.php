<?php

namespace App\Hosting;

use App\Ai\AiException;
use Illuminate\Http\Request;

/**
 * Quota d'un site hébergé atteint. Message en français, affiché tel quel ; hérite
 * d'AiException pour que l'assistant IA le montre comme ses propres erreurs.
 */
class QuotaExceeded extends AiException
{
    public function render(Request $request)
    {
        if ($request->expectsJson()) {
            return response()->json(['message' => $this->getMessage()], 422);
        }

        return back()->withInput()->with('warnings', [$this->getMessage()]);
    }
}
