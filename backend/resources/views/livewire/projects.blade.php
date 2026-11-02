<div class="mx-auto max-w-6xl">
    <div class="mb-8">
        <p class="mb-2 text-xs font-semibold uppercase tracking-widest text-indigo-700">Project setup</p>
        <h1 class="text-3xl font-bold tracking-tight">{{ $editingId ? 'Edit Project' : 'New Project' }}</h1>
        <p class="mt-2 text-slate-600">Create projects and review their task progress.</p>
    </div>

    @if (session()->has('status'))
        <div role="status" class="mb-6 rounded-lg border border-emerald-200 bg-emerald-50 p-4 text-emerald-800">{{ session('status') }}</div>
    @endif

    @error('deletion')
        <div role="alert" class="mb-6 rounded-lg border border-red-200 bg-red-50 p-4 text-red-800">{{ $message }}</div>
    @enderror

    <section id="project-form" class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm" aria-labelledby="project-form-title">
        <h2 id="project-form-title" class="text-lg font-semibold">Project details</h2>
        <p class="mb-5 mt-1 text-sm text-slate-600">Give the project a name and clear description. Both fields are required.</p>
        <form wire:submit="save" class="grid gap-5 sm:grid-cols-2">
            <div>
                <label for="project-name" class="mb-2 block text-sm font-medium">Name</label>
                <input id="project-name" wire:model="name" type="text" maxlength="255" required
                    aria-invalid="{{ $errors->has('name') ? 'true' : 'false' }}" aria-describedby="name-error"
                    class="w-full rounded-lg border border-slate-300 px-3 py-2 focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-200">
                <p id="name-error" aria-live="polite" class="mt-1 text-sm text-red-700">@error('name') {{ $message }} @enderror</p>
            </div>
            <div>
                <label for="project-description" class="mb-2 block text-sm font-medium">Description</label>
                <textarea id="project-description" wire:model="description" rows="3" maxlength="10000" required
                    aria-invalid="{{ $errors->has('description') ? 'true' : 'false' }}" aria-describedby="description-error"
                    class="w-full rounded-lg border border-slate-300 px-3 py-2 focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-200"></textarea>
                <p id="description-error" aria-live="polite" class="mt-1 text-sm text-red-700">@error('description') {{ $message }} @enderror</p>
            </div>
            <label class="inline-flex items-center gap-3 text-sm font-medium sm:col-span-2">
                <input type="checkbox" wire:model="isActive" class="h-4 w-4 rounded accent-indigo-600">
                Project is active
            </label>
            <div class="flex flex-wrap gap-3 sm:col-span-2">
                <button type="submit" wire:loading.attr="disabled" class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-700 disabled:opacity-50">
                    <span wire:loading.remove wire:target="save">{{ $editingId ? 'Save changes' : 'Create project' }}</span>
                    <span wire:loading wire:target="save">Saving…</span>
                </button>
                @if ($editingId)
                    <button type="button" wire:click="cancel" class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50">Cancel</button>
                @endif
            </div>
        </form>
    </section>

    <section class="mt-8 overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm" aria-labelledby="projects-heading">
        <h2 id="projects-heading" class="sr-only">Projects</h2>
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
                        <tr wire:key="project-{{ $project->id }}" class="hover:bg-slate-50">
                            <td class="px-6 py-4 font-semibold text-slate-900">{{ $project->name }}</td>
                            <td class="px-6 py-4 text-slate-600">{{ $project->tasks_count }}</td>
                            <td class="px-6 py-4 text-slate-600">{{ $project->completed_tasks_count }}</td>
                            <td class="px-6 py-4">
                                <span @class(['rounded-full px-3 py-1 text-xs font-semibold', 'bg-emerald-100 text-emerald-700' => $project->is_active, 'bg-slate-100 text-slate-600' => ! $project->is_active])>{{ $project->is_active ? 'Active' : 'Inactive' }}</span>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex justify-end gap-2">
                                    <button type="button" wire:click="edit({{ $project->id }})" wire:loading.attr="disabled" aria-label="Edit {{ $project->name }}" title="Edit project" class="grid h-8 w-8 place-items-center rounded-md bg-indigo-100 text-indigo-700 transition hover:bg-indigo-200 focus:outline-none focus:ring-2 focus:ring-indigo-400 disabled:opacity-50"><svg aria-hidden="true" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="m16.862 3.487 3.651 3.651M4.5 19.5l4.22-.844L19.33 8.046a2.582 2.582 0 0 0-3.651-3.651L5.07 15.005 4.5 19.5Z" /></svg><span class="sr-only">Edit</span></button>
                                    <button type="button" wire:click="delete({{ $project->id }})" wire:confirm="Delete this project and all its tasks? This cannot be undone." wire:loading.attr="disabled" aria-label="Delete {{ $project->name }}" title="Delete project" class="grid h-8 w-8 place-items-center rounded-md bg-red-100 text-red-700 transition hover:bg-red-200 focus:outline-none focus:ring-2 focus:ring-red-400 disabled:opacity-50"><svg aria-hidden="true" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673A2.25 2.25 0 0 1 15.916 21H8.084a2.25 2.25 0 0 1-2.244-1.327L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.8-.284m-12.456.45c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.8-.284m7.932 0a48.67 48.67 0 0 0-7.932 0M14.25 3.75h-4.5" /></svg><span class="sr-only">Delete</span></button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-12 text-center">
                                <p class="font-semibold text-slate-900">No projects yet</p>
                                <p class="mt-2 text-slate-600">Create your first project above.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
</div>
