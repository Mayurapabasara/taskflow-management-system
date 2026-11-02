<div>
    <div class="mb-6 flex justify-end">
        <button type="button" wire:click="startCreatingProject" class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-700">+ Add Project</button>
    </div>

    @if (session()->has('status'))
        <div role="status" class="mb-6 rounded-lg border border-emerald-200 bg-emerald-50 p-4 text-emerald-800">{{ session('status') }}</div>
    @endif

    @error('deletion')
        <div role="alert" class="mb-6 rounded-lg border border-red-200 bg-red-50 p-4 text-red-800">{{ $message }}</div>
    @enderror

    @if ($showProjectForm)
        <div class="fixed inset-0 z-50 grid place-items-center bg-slate-950/40 p-4" role="dialog" aria-modal="true" aria-labelledby="project-modal-title">
            <section class="w-full max-w-xl rounded-xl bg-white p-6 shadow-xl">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <h2 id="project-modal-title" class="text-xl font-semibold">{{ $editingId ? 'Edit Project' : 'New Project' }}</h2>
                        <p class="mt-1 text-sm text-slate-600">Enter the project details below.</p>
                    </div>
                    <button type="button" wire:click="cancelProjectForm" aria-label="Close project form" class="rounded-lg px-3 py-1 text-slate-500 hover:bg-slate-100">×</button>
                </div>
                <form wire:submit="saveProject" class="mt-6 space-y-5">
                    <div>
                        <label for="project-name" class="mb-2 block text-sm font-medium">Name</label>
                        <input id="project-name" type="text" wire:model="name" maxlength="255" required aria-describedby="name-error" aria-invalid="{{ $errors->has('name') ? 'true' : 'false' }}" class="w-full rounded-lg border border-slate-300 px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-200">
                        <p id="name-error" aria-live="polite" class="mt-1 text-sm text-red-700">@error('name') {{ $message }} @enderror</p>
                    </div>
                    <div>
                        <label for="project-description" class="mb-2 block text-sm font-medium">Description</label>
                        <textarea id="project-description" wire:model="description" rows="4" maxlength="10000" required aria-describedby="description-error" aria-invalid="{{ $errors->has('description') ? 'true' : 'false' }}" class="w-full rounded-lg border border-slate-300 px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-200"></textarea>
                        <p id="description-error" aria-live="polite" class="mt-1 text-sm text-red-700">@error('description') {{ $message }} @enderror</p>
                    </div>
                    <label class="inline-flex items-center gap-3 text-sm font-medium">
                        <input type="checkbox" wire:model="isActive" class="h-4 w-4 rounded accent-indigo-600">
                        Project is active
                    </label>
                    <div class="flex justify-end gap-3">
                        <button type="button" wire:click="cancelProjectForm" class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50">Cancel</button>
                        <button type="submit" wire:loading.attr="disabled" class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-700 disabled:opacity-50"><span wire:loading.remove wire:target="saveProject">{{ $editingId ? 'Save changes' : 'Create project' }}</span><span wire:loading wire:target="saveProject">Saving…</span></button>
                    </div>
                </form>
            </section>
        </div>
    @endif

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
                                    <button type="button" wire:click="edit({{ $project->id }})" title="Edit project" aria-label="Edit {{ $project->name }}" class="grid h-8 w-8 place-items-center rounded-md bg-indigo-100/70 text-indigo-500 transition hover:bg-indigo-200 hover:text-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-400">
                                        <svg aria-hidden="true" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M13 5H3a1 1 0 0 0-1 1v15a1 1 0 0 0 1 1h15a1 1 0 0 0 1-1v-6" /><path d="m16 3 5 5M9 13l-1 5 5-1L23 7a2 2 0 0 0 0-3l-2-2a2 2 0 0 0-3 0Z" /></svg><span class="sr-only">Edit</span>
                                    </button>
                                    <a href="{{ route('projects.tasks', $project) }}" title="Open tasks" aria-label="Open tasks for {{ $project->name }}" class="grid h-8 w-8 place-items-center rounded-md bg-sky-100 text-sky-700 transition hover:bg-sky-200 focus:outline-none focus:ring-2 focus:ring-sky-400">
                                        <svg aria-hidden="true" class="h-4 w-4" viewBox="0 0 24 24" fill="currentColor"><path d="M11.99 2C6.47 2 2 6.48 2 12s4.47 10 9.99 10C17.52 22 22 17.52 22 12S17.52 2 11.99 2ZM16.2 16.2 11 13V7h1.5v5.2l4.5 2.7-.8 1.3Z" /></svg><span class="sr-only">Tasks</span>
                                    </a>
                                    <button type="button" wire:click="delete({{ $project->id }})" wire:confirm="Delete this project and all its tasks? This cannot be undone." wire:loading.attr="disabled" title="Delete project" aria-label="Delete {{ $project->name }}" class="grid h-8 w-8 place-items-center rounded-md bg-red-100 text-red-700 transition hover:bg-red-200 focus:outline-none focus:ring-2 focus:ring-red-400 disabled:opacity-50">
                                        <svg aria-hidden="true" class="h-4 w-4" viewBox="0 0 24 24" fill="currentColor"><path d="M6 19c0 1.1.9 2 2 2h8c1.1 0 2-.9 2-2V7H6v12ZM8.46 11.12l1.41-1.41L12 11.88l2.12-2.17 1.41 1.41L13.41 13.3l2.12 2.12-1.41 1.41L12 14.71l-2.12 2.12-1.41-1.41 2.12-2.12-2.13-2.18ZM15.5 4l-1-1h-5l-1 1H5v2h14V4h-3.5Z" /></svg><span class="sr-only">Delete</span>
                                    </button>
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
