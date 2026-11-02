<div>
    <div class="mb-6 flex justify-end">
        <a href="{{ route('projects.index') }}#project-form" class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-700">+ Add Project</a>
    </div>

    @if (session()->has('status'))
        <div role="status" class="mb-6 rounded-lg border border-emerald-200 bg-emerald-50 p-4 text-emerald-800">{{ session('status') }}</div>
    @endif

    @error('deletion')
        <div role="alert" class="mb-6 rounded-lg border border-red-200 bg-red-50 p-4 text-red-800">{{ $message }}</div>
    @enderror

    <section class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm" aria-labelledby="projects-heading">
        <h1 id="projects-heading" class="sr-only">Projects</h1>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 text-left text-sm">
                <thead class="bg-slate-50 text-xs font-semibold uppercase tracking-wide text-slate-500">
                    <tr>
                        <th scope="col" class="px-6 py-4">Project</th>
                        <th scope="col" class="px-6 py-4">Number of tasks</th>
                        <th scope="col" class="px-6 py-4">Completed tasks</th>
                        <th scope="col" class="px-6 py-4">Status</th>
                        <th scope="col" class="px-6 py-4 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($projects as $project)
                        <tr wire:key="backup-project-{{ $project->id }}" class="hover:bg-slate-50">
                            <td class="px-6 py-4 font-semibold text-slate-900">{{ $project->name }}</td>
                            <td class="px-6 py-4 text-slate-600">{{ $project->tasks_count }}</td>
                            <td class="px-6 py-4 text-slate-600">{{ $project->completed_tasks_count }}</td>
                            <td class="px-6 py-4">
                                <span @class(['rounded-full px-3 py-1 text-xs font-semibold', 'bg-emerald-100 text-emerald-700' => $project->is_active, 'bg-slate-100 text-slate-600' => ! $project->is_active])>{{ $project->is_active ? 'Active' : 'Inactive' }}</span>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex justify-end gap-2">
                                    <a href="{{ route('projects.index', ['edit' => $project->id]) }}#project-form" class="rounded-lg bg-indigo-100 px-3 py-2 text-sm font-medium text-indigo-700 hover:bg-indigo-200">Edit</a>
                                    <button type="button" wire:click="delete({{ $project->id }})" wire:confirm="Delete this project and all its tasks? This cannot be undone." wire:loading.attr="disabled" aria-label="Delete {{ $project->name }}" class="rounded-lg bg-red-100 px-3 py-2 text-sm font-medium text-red-700 hover:bg-red-200 disabled:opacity-50">Delete</button>
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
