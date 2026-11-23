<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex">
    <title>@yield('title') · GeniusPress</title>
    @vite(['resources/css/app.css'])
</head>
<body class="min-h-screen bg-slate-50 text-slate-900 antialiased">
    <main class="flex min-h-screen items-center justify-center px-4 py-12">
        <div class="w-full max-w-md">
            <div class="mb-8 flex items-center justify-center gap-2">
                <span class="grid h-10 w-10 place-items-center rounded-xl bg-indigo-600 text-lg font-bold text-white">G</span>
                <span class="text-xl font-semibold tracking-tight">GeniusPress</span>
            </div>
            <div class="rounded-2xl border border-slate-200 bg-white p-8 shadow-sm">
                @yield('content')
            </div>
        </div>
    </main>
</body>
</html>
