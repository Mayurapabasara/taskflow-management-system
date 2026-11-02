<div>
    <div class="mb-8 flex flex-wrap items-start justify-between gap-4">
        <div>
            <p class="mb-2 text-xs font-semibold uppercase tracking-widest text-indigo-700">Project overview</p>
            <h1 class="text-3xl font-bold tracking-tight">Backup Manager</h1>
            <p class="mt-2 text-slate-600">View all projects and open their tasks from one place.</p>
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
                        <th scope="col" class="px-6 py-4">Description</th>
                        <th scope="col" class="px-6 py-4">Progress</th>
                        <th scope="col" class="px-6 py-4 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($projects as $project)
                        <tr wire:key="backup-project-{{ $project->id }}" class="hover:bg-slate-50">
                            <td class="px-6 py-4 font-semibold text-slate-900">{{ $project->name }}</td>
                            <td class="max-w-md px-6 py-4 text-slate-600">{{ \Illuminate\Support\Str::limit($project->description, 100) }}</td>
                            <td class="px-6 py-4 text-slate-600">{{ $project->completed_tasks_count }} / {{ $project->tasks_count }} tasks</td>
                            <td class="px-6 py-4 text-right"><a href="{{ route('projects.tasks', $project) }}" class="font-medium text-indigo-700 hover:underline">Assign tasks</a></td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-6 py-12 text-center">
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
