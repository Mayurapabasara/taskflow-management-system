<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Projects · TaskFlow</title>
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        @livewireStyles
    </head>
    <body class="min-h-screen bg-slate-50 font-sans text-slate-900 antialiased">
        <header class="border-b border-slate-200 bg-white">
            <div class="mx-auto max-w-6xl px-4 py-5 sm:px-6">
                <a href="{{ route('projects.index') }}" class="text-xl font-bold tracking-tight text-indigo-700">TaskFlow</a>
            </div>
        </header>
        <main class="mx-auto max-w-6xl px-4 py-8 sm:px-6">
            <livewire:projects />
        </main>
        @livewireScripts
    </body>
</html>
