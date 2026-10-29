<div>
    <div class="mb-8">
        <h1 class="text-3xl font-bold tracking-tight">Projects</h1>
        <p class="mt-2 text-slate-600">Organize your work and see how each project is progressing.</p>
    </div>

    @if (session()->has('status'))
        <div role="status" class="mb-6 rounded-lg border border-emerald-200 bg-emerald-50 p-4 text-emerald-800">{{ session('status') }}</div>
    @endif

    @error('deletion')
        <div role="alert" class="mb-6 rounded-lg border border-red-200 bg-red-50 p-4 text-red-800">{{ $message }}</div>
    @enderror

    <div class="grid items-start gap-8 lg:grid-cols-3">
        <section class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm" aria-labelledby="project-form-title">
            <h2 id="project-form-title" class="mb-5 text-lg font-semibold">{{ $editingId ? 'Edit project' : 'New project' }}</h2>
            <form wire:submit="save" class="space-y-5">
                <div>
                    <label for="project-name" class="mb-2 block text-sm font-medium">Name</label>
                    <input id="project-name" wire:model="name" type="text" maxlength="255" required
                        aria-invalid="{{ $errors->has('name') ? 'true' : 'false' }}" aria-describedby="name-error"
                        class="w-full rounded-lg border border-slate-300 px-3 py-2 focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-200">
                    <p id="name-error" class="mt-1 text-sm text-red-700">@error('name') {{ $message }} @enderror</p>
                </div>
                <div>
                    <label for="project-description" class="mb-2 block text-sm font-medium">Description</label>
                    <textarea id="project-description" wire:model="description" rows="5" maxlength="10000" required
                        aria-invalid="{{ $errors->has('description') ? 'true' : 'false' }}" aria-describedby="description-error"
                        class="w-full rounded-lg border border-slate-300 px-3 py-2 focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-200"></textarea>
                    <p id="description-error" class="mt-1 text-sm text-red-700">@error('description') {{ $message }} @enderror</p>
                </div>
                <div class="flex flex-wrap gap-3">
                    <button type="submit" wire:loading.attr="disabled" class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-700 disabled:opacity-50">
                        {{ $editingId ? 'Save changes' : 'Create project' }}
                    </button>
                    @if ($editingId)
                        <button type="button" wire:click="cancel" class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-medium hover:bg-slate-50">Cancel</button>
                    @endif
                </div>
            </form>
        </section>

        <section class="space-y-4 lg:col-span-2" aria-label="Project list">
            @forelse ($projects as $project)
                <article wire:key="project-{{ $project->id }}" class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
                    <h2 class="break-words text-xl font-semibold">{{ $project->name }}</h2>
                    <p class="mt-2 whitespace-pre-line break-words text-sm leading-6 text-slate-600">{{ $project->description }}</p>
                    <div class="mt-5 flex items-center justify-between gap-3 text-sm text-slate-600">
                        <span>{{ $project->completed_tasks_count }} / {{ $project->tasks_count }} tasks completed</span>
                        <span>{{ $project->tasks_count ? round($project->completed_tasks_count / $project->tasks_count * 100) : 0 }}%</span>
                    </div>
                    <progress aria-label="Task progress for {{ $project->name }}" value="{{ $project->completed_tasks_count }}" max="{{ max(1, $project->tasks_count) }}" class="mt-2 h-2 w-full overflow-hidden rounded-full accent-indigo-600"></progress>
                    <div class="mt-5 flex gap-3 border-t border-slate-100 pt-4">
                        <button type="button" wire:click="edit({{ $project->id }})" wire:loading.attr="disabled" class="rounded-lg border border-slate-300 px-3 py-2 text-sm font-medium hover:bg-slate-50 disabled:opacity-50" aria-label="Edit {{ $project->name }}">Edit</button>
                        <button type="button" wire:click="delete({{ $project->id }})" wire:confirm="Delete this project and all its tasks? This cannot be undone." wire:loading.attr="disabled" class="rounded-lg px-3 py-2 text-sm font-medium text-red-700 hover:bg-red-50 disabled:opacity-50" aria-label="Delete {{ $project->name }}">Delete</button>
                    </div>
                </article>
            @empty
                <div class="rounded-xl border border-dashed border-slate-300 bg-white px-6 py-12 text-center">
                    <h2 class="text-lg font-semibold">No projects yet</h2>
                    <p class="mt-2 text-sm text-slate-600">Create your first project to get started.</p>
                </div>
            @endforelse
        </section>
    </div>
</div>
