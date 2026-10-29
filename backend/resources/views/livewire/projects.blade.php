<div>
    <div class="mb-8">
        <p class="mb-2 text-xs font-semibold uppercase tracking-widest text-indigo-700">Your workspace</p>
        <h1 class="text-3xl font-bold tracking-tight">Projects</h1>
        <p class="mt-2 text-slate-600">Organize your work and see how each project is progressing.</p>
    </div>

    <dl class="mb-8 grid grid-cols-1 gap-3 sm:grid-cols-3">
        <div class="rounded-xl border border-slate-200 bg-white p-5"><dt class="text-sm text-slate-600">Projects</dt><dd class="mt-2 text-3xl font-semibold tabular-nums">{{ $projects->count() }}</dd></div>
        <div class="rounded-xl border border-slate-200 bg-white p-5"><dt class="text-sm text-slate-600">Total tasks</dt><dd class="mt-2 text-3xl font-semibold tabular-nums">{{ $projects->sum('tasks_count') }}</dd></div>
        <div class="rounded-xl border border-indigo-200 bg-indigo-50 p-5"><dt class="text-sm text-indigo-700">Completed tasks</dt><dd class="mt-2 text-3xl font-semibold tabular-nums text-indigo-800">{{ $projects->sum('completed_tasks_count') }}</dd></div>
    </dl>

    <div wire:loading.delay role="status" class="mb-4 rounded-lg bg-indigo-50 px-4 py-3 text-sm font-medium text-indigo-800">
        <span aria-hidden="true" class="mr-2 inline-block h-3 w-3 rounded-full bg-indigo-500 motion-safe:animate-pulse"></span>Updating your workspace…
    </div>

    @if (session()->has('status'))
        <div role="status" class="mb-6 rounded-lg border border-emerald-200 bg-emerald-50 p-4 text-emerald-800">{{ session('status') }}</div>
    @endif

    @error('deletion')
        <div role="alert" class="mb-6 rounded-lg border border-red-200 bg-red-50 p-4 text-red-800">{{ $message }}</div>
    @enderror

    <div class="grid items-start gap-8 lg:grid-cols-3">
        <section class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm" aria-labelledby="project-form-title">
            <h2 id="project-form-title" class="text-lg font-semibold">{{ $editingId ? 'Edit project' : 'New project' }}</h2>
            <p class="mb-5 mt-1 text-sm text-slate-600">Give your project a name and a clear description. All fields are required.</p>
            <form wire:submit="save" class="space-y-5">
                <div>
                    <label for="project-name" class="mb-2 block text-sm font-medium">Name</label>
                    <input id="project-name" wire:model="name" type="text" maxlength="255" required
                        aria-invalid="{{ $errors->has('name') ? 'true' : 'false' }}" aria-describedby="name-error"
                        class="w-full rounded-lg border border-slate-300 px-3 py-2 focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-200">
                    <p id="name-error" aria-live="polite" class="mt-1 text-sm text-red-700">@error('name') {{ $message }} @enderror</p>
                </div>
                <div>
                    <label for="project-description" class="mb-2 block text-sm font-medium">Description</label>
                    <textarea id="project-description" wire:model="description" rows="5" maxlength="10000" required
                        aria-invalid="{{ $errors->has('description') ? 'true' : 'false' }}" aria-describedby="description-error"
                        class="w-full rounded-lg border border-slate-300 px-3 py-2 focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-200"></textarea>
                    <p id="description-error" aria-live="polite" class="mt-1 text-sm text-red-700">@error('description') {{ $message }} @enderror</p>
                </div>
                <div class="flex flex-wrap gap-3">
                    <button type="submit" wire:loading.attr="disabled" class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-700 disabled:opacity-50">
                        <span wire:loading.remove wire:target="save">{{ $editingId ? 'Save changes' : 'Create project' }}</span>
                        <span wire:loading wire:target="save">Saving…</span>
                    </button>
                    @if ($editingId)
                        <button type="button" wire:click="cancel" class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-medium hover:bg-slate-50">Cancel</button>
                    @endif
                </div>
            </form>
        </section>

        <section class="grid gap-4 sm:grid-cols-2 lg:col-span-2" aria-label="Project list">
            @forelse ($projects as $project)
                <article wire:key="project-{{ $project->id }}" @class(['flex min-w-0 flex-col rounded-xl border bg-white p-6 shadow-sm', 'border-indigo-400 ring-2 ring-indigo-100' => $selectedProjectId === $project->id, 'border-slate-200' => $selectedProjectId !== $project->id])>
                    @if ($selectedProjectId === $project->id)
                        <p class="mb-3 text-xs font-semibold uppercase tracking-wide text-indigo-700">Selected project</p>
                    @endif
                    <h2 class="break-words text-xl font-semibold">{{ $project->name }}</h2>
                    <p class="mt-2 whitespace-pre-line break-words text-sm leading-6 text-slate-600">{{ $project->description }}</p>
                    <div class="mt-5 flex items-center justify-between gap-3 text-sm text-slate-600">
                        <span>{{ $project->completed_tasks_count }} / {{ $project->tasks_count }} tasks completed</span>
                        <span>{{ $project->tasks_count ? round($project->completed_tasks_count / $project->tasks_count * 100) : 0 }}%</span>
                    </div>
                    <progress aria-label="Task progress for {{ $project->name }}" value="{{ $project->completed_tasks_count }}" max="{{ max(1, $project->tasks_count) }}" class="mt-2 h-2 w-full overflow-hidden rounded-full accent-indigo-600"></progress>
                    <div class="mt-auto flex flex-wrap gap-2 border-t border-slate-100 pt-4">
                        <button type="button" wire:click="selectProject({{ $project->id }})" wire:loading.attr="disabled" aria-pressed="{{ $selectedProjectId === $project->id ? 'true' : 'false' }}" class="rounded-lg bg-indigo-50 px-3 py-2 text-sm font-medium text-indigo-700 hover:bg-indigo-100 disabled:opacity-50">Tasks</button>
                        <button type="button" wire:click="edit({{ $project->id }})" wire:loading.attr="disabled" class="rounded-lg border border-slate-300 px-3 py-2 text-sm font-medium hover:bg-slate-50 disabled:opacity-50" aria-label="Edit {{ $project->name }}">Edit</button>
                        <button type="button" wire:click="delete({{ $project->id }})" wire:confirm="Delete this project and all its tasks? This cannot be undone." wire:loading.attr="disabled" class="rounded-lg px-3 py-2 text-sm font-medium text-red-700 hover:bg-red-50 disabled:opacity-50" aria-label="Delete {{ $project->name }}">Delete</button>
                    </div>
                </article>
            @empty
                <div class="rounded-xl border border-dashed border-slate-300 bg-white px-6 py-12 text-center sm:col-span-2">
                    <h2 class="text-lg font-semibold">No projects yet</h2>
                    <p class="mt-2 text-sm text-slate-600">Create your first project to get started.</p>
                </div>
            @endforelse
        </section>
    </div>

    @if ($selectedProject)
        <section wire:key="tasks-{{ $selectedProject->id }}" class="mt-8 rounded-xl border border-slate-200 bg-white p-6 shadow-sm" aria-labelledby="tasks-heading">
            <h2 id="tasks-heading" class="break-words text-xl font-semibold">Tasks for {{ $selectedProject->name }}</h2>
            <p class="mt-2 text-sm text-slate-600">Check a task to mark it complete. Changes are saved automatically.</p>
            <form wire:submit="saveTask" class="mt-5 space-y-3">
                <label for="task-title" class="block text-sm font-medium">{{ $editingTaskId ? 'Edit task title' : 'New task title' }}</label>
                <input id="task-title" type="text" wire:model="taskTitle" maxlength="255" required aria-describedby="task-title-error" aria-invalid="{{ $errors->has('taskTitle') ? 'true' : 'false' }}" class="w-full rounded-lg border border-slate-300 px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-200">
                <p id="task-title-error" aria-live="polite" class="text-sm text-red-700">@error('taskTitle') {{ $message }} @enderror</p>
                <div class="flex gap-3">
                    <button type="submit" wire:loading.attr="disabled" class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-700 disabled:opacity-50"><span wire:loading.remove wire:target="saveTask">{{ $editingTaskId ? 'Save task' : 'Add task' }}</span><span wire:loading wire:target="saveTask">Saving…</span></button>
                    @if ($editingTaskId)
                        <button type="button" wire:click="cancelTask" class="rounded-lg border border-slate-300 px-4 py-2 text-sm">Cancel</button>
                    @endif
                </div>
            </form>
            <ul class="mt-6 divide-y divide-slate-200">
                @forelse ($selectedProject->tasks as $task)
                    <li wire:key="task-{{ $task->id }}" class="flex flex-wrap items-center justify-between gap-3 py-4">
                        <input id="task-completed-{{ $task->id }}" type="checkbox" wire:click="toggleTask({{ $task->id }})" @checked($task->is_completed) wire:loading.attr="disabled" class="h-5 w-5 rounded accent-indigo-600 disabled:opacity-50">
                        <label for="task-completed-{{ $task->id }}" @class(['min-w-0 flex-1 break-words', 'line-through text-slate-500' => $task->is_completed])>{{ $task->title }}</label>
                        <div class="flex gap-2">
                            <button type="button" wire:click="editTask({{ $task->id }})" wire:loading.attr="disabled" aria-label="Edit task {{ $task->title }}" class="rounded-lg border border-slate-300 px-3 py-2 text-sm hover:bg-slate-50 disabled:opacity-50">Edit</button>
                            <button type="button" wire:click="deleteTask({{ $task->id }})" wire:confirm="Delete this task? This cannot be undone." wire:loading.attr="disabled" aria-label="Delete task {{ $task->title }}" class="rounded-lg px-3 py-2 text-sm text-red-700 hover:bg-red-50 disabled:opacity-50">Delete</button>
                        </div>
                    </li>
                @empty
                    <li class="py-6 text-sm text-slate-600">No tasks yet. Add the first task above.</li>
                @endforelse
            </ul>
        </section>
    @else
        <section class="mt-8 rounded-xl border border-dashed border-slate-300 px-6 py-10 text-center" aria-label="Task list">
            <h2 class="text-lg font-semibold">Your tasks, in focus</h2>
            <p class="mt-2 text-sm text-slate-600">Select Tasks on a project to view and manage its task list.</p>
        </section>
    @endif
</div>
