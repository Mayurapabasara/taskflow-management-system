<div>
    <div class="mb-8 flex flex-wrap items-start justify-between gap-4">
        <div>
            <p class="mb-2 text-xs font-semibold uppercase tracking-widest text-indigo-700">Project overview</p>
            <h1 class="text-3xl font-bold tracking-tight">Backup Manager</h1>
            <p class="mt-2 text-slate-600">View project assignments and backup connection details in one place.</p>
        </div>
        <a href="{{ route('projects.index') }}#project-form" class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-700">+ Add Project</a>
    </div>

    <section class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm" aria-labelledby="backup-projects-heading">
        <h2 id="backup-projects-heading" class="sr-only">All projects</h2>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 text-left text-sm">
                <thead class="bg-slate-50 text-xs font-semibold uppercase tracking-wide text-slate-500">
                    <tr>
                        <th scope="col" class="px-6 py-4">Project</th>
                        <th scope="col" class="px-6 py-4">Database</th>
                        <th scope="col" class="px-6 py-4">Host</th>
                        <th scope="col" class="px-6 py-4">Port</th>
                        <th scope="col" class="px-6 py-4 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($projects as $project)
                        <tr wire:key="backup-project-{{ $project->id }}" class="hover:bg-slate-50">
                            <td class="px-6 py-4 font-semibold text-slate-900">{{ $project->name }}</td>
                            <td class="px-6 py-4 text-slate-600">{{ $project->tasks->pluck('assignee.name')->filter()->unique()->implode(', ') ?: 'Unassigned' }}</td>
                            <td class="px-6 py-4 text-slate-500">Not configured</td>
                            <td class="px-6 py-4 text-slate-500">Not configured</td>
                            <td class="px-6 py-4">
                                <div class="flex justify-end gap-2" aria-label="Project actions are not available yet">
                                    <button type="button" disabled title="Download backup (coming soon)" aria-label="Download backup, coming soon" class="grid h-8 w-8 place-items-center rounded-md bg-emerald-100 text-emerald-700 opacity-60">↓</button>
                                    <button type="button" disabled title="Edit backup (coming soon)" aria-label="Edit backup, coming soon" class="grid h-8 w-8 place-items-center rounded-md bg-indigo-100 text-indigo-700 opacity-60">✎</button>
                                    <a href="{{ route('projects.tasks', $project) }}" title="Assign tasks" aria-label="Assign tasks for {{ $project->name }}" class="grid h-8 w-8 place-items-center rounded-md bg-sky-100 text-sky-700 hover:bg-sky-200 focus:outline-none focus:ring-2 focus:ring-sky-400">◉</a>
                                    <button type="button" disabled title="Delete backup (coming soon)" aria-label="Delete backup, coming soon" class="grid h-8 w-8 place-items-center rounded-md bg-red-100 text-red-700 opacity-60">⌫</button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-12 text-center">
                                <p class="font-semibold text-slate-900">No projects yet</p>
                                <p class="mt-2 text-slate-600">Add your first project to see it here.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
</div>
