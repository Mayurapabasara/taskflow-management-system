<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>{{ isset($project) ? $project->name.' · Tasks' : 'Projects' }} · Inbizsy</title>
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        @livewireStyles
    </head>
    <body class="min-h-screen bg-slate-50 font-sans text-slate-900 antialiased">
        <a href="#main-content" class="sr-only focus:not-sr-only focus:fixed focus:left-4 focus:top-4 focus:z-50 focus:rounded-lg focus:bg-indigo-700 focus:px-4 focus:py-3 focus:text-white">Skip to content</a>
        <div class="min-h-screen lg:flex">
            <aside class="hidden w-64 shrink-0 flex-col border-r border-slate-200 bg-white lg:flex">
                <div class="border-b border-slate-100 px-6 py-7">
                    <a href="{{ route('backups.index') }}" class="inline-flex items-center gap-3" aria-label="Inbizsy project workspace">
                        <span aria-hidden="true" class="relative block h-10 w-10">
                            <span class="absolute left-1 top-1 h-4 w-4 -rotate-12 bg-sky-400"></span>
                            <span class="absolute right-1 top-0 h-4 w-4 rotate-45 bg-orange-400"></span>
                            <span class="absolute bottom-1 left-3 h-4 w-4 rotate-45 bg-lime-500"></span>
                        </span>
                        <span>
                            <span class="block text-xl font-bold leading-none tracking-tight text-slate-950">Inbizsy</span>
                            <span class="mt-1 block text-[10px] font-medium uppercase tracking-[0.16em] text-slate-400">Project Workspace</span>
                        </span>
                    </a>
                </div>

                <nav class="flex-1 px-4 py-7" aria-label="Project navigation">
                    <p class="px-3 text-[10px] font-semibold uppercase tracking-[0.16em] text-slate-400">Project management</p>
                    <div class="mt-3 space-y-1">
                        <a href="{{ route('backups.index') }}" @class([
                            'flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium transition',
                            'bg-indigo-50 text-indigo-700' => request()->routeIs('backups.index'),
                            'text-slate-600 hover:bg-slate-50 hover:text-slate-950' => ! request()->routeIs('backups.index'),
                        ]) @if (request()->routeIs('backups.index')) aria-current="page" @endif>
                            <svg aria-hidden="true" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M3 11.25 12 4l9 7.25v8.25a1.5 1.5 0 0 1-1.5 1.5h-15A1.5 1.5 0 0 1 3 19.5v-8.25Z"/><path stroke-linecap="round" stroke-linejoin="round" d="M9 21v-6h6v6"/></svg>
                            Project Dashboard
                        </a>
                    </div>
                </nav>

                <div class="border-t border-slate-100 px-7 py-5 text-xs text-slate-400">Inbizsy Project Suite</div>
            </aside>

            <div class="min-w-0 flex-1">
                <header class="border-b border-slate-200 bg-white lg:hidden">
                    <div class="flex items-center justify-between px-4 py-4 sm:px-6">
                        <a href="{{ route('backups.index') }}" class="text-xl font-bold tracking-tight text-slate-950">Inbizsy</a>
                        <span class="text-xs font-semibold uppercase tracking-[0.16em] text-slate-400">Project workspace</span>
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
            </div>
        </div>
        @livewireScripts
    </body>
</html>
