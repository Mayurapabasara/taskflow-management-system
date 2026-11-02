<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>{{ isset($project) ? $project->name.' · Tasks' : (isset($backupManager) ? 'Backup Manager' : 'Projects') }} · TaskFlow</title>
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        @livewireStyles
    </head>
    <body class="min-h-screen bg-slate-50 font-sans text-slate-900 antialiased">
        <a href="#main-content" class="sr-only focus:not-sr-only focus:fixed focus:left-4 focus:top-4 focus:z-50 focus:rounded-lg focus:bg-indigo-700 focus:px-4 focus:py-3 focus:text-white">Skip to content</a>
        <header class="border-b border-slate-200 bg-white">
            <div class="mx-auto max-w-6xl px-4 py-5 sm:px-6">
                <div class="flex items-center justify-between gap-4">
                    <a href="{{ route('backups.index') }}" class="inline-flex items-center gap-3 text-xl font-bold tracking-tight text-indigo-700"><span aria-hidden="true" class="grid h-9 w-9 place-items-center rounded-xl bg-indigo-600 text-sm text-white">TF</span>TaskFlow</a>
                    <a href="{{ route('projects.index') }}" class="text-sm font-medium text-slate-600 hover:text-indigo-700">Projects</a>
                </div>
            </div>
        </header>
        <main id="main-content" tabindex="-1" class="mx-auto max-w-6xl px-4 py-8 sm:px-6 sm:py-12">
            @isset($project)
                <livewire:project-tasks :project="$project" />
            @elseif (isset($backupManager))
                <livewire:backup-manager />
            @else
                <livewire:projects />
            @endisset
        </main>
        @livewireScripts
    </body>
</html>
