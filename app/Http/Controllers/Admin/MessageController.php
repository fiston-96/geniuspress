<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\FormSubmission;

/** Messages reçus par les formulaires de contact. */
class MessageController extends Controller
{
    public function index()
    {
        return view('admin.messages', [
            'messages' => FormSubmission::query()->with(['page:id,title,slug,locale', 'layout:id,name'])->latest()->paginate(30),
        ]);
    }

    public function show(FormSubmission $message)
    {
        $message->read_at ??= now();
        $message->save();

        return view('admin.message', ['message' => $message->load(['page:id,title,slug,locale', 'layout:id,name'])]);
    }

    public function destroy(FormSubmission $message)
    {
        $message->delete();

        return redirect()->route('admin.messages.index')->with('status', __('Message supprimé.'));
    }
}
