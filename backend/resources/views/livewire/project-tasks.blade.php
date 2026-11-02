<div>
    <div class="mb-8 flex flex-wrap items-start justify-between gap-4">
        <div>
            <p class="mb-2 text-xs font-semibold uppercase tracking-widest text-indigo-700">Project tasks</p>
            <h1 class="break-words text-3xl font-bold tracking-tight">Tasks</h1>
            <p class="mt-2 whitespace-pre-line break-words text-slate-600">{{ $selectedProject->description }}</p>
            <p class="mt-3 text-sm font-medium text-indigo-700">{{ $selectedProject->tasks->where('is_completed', true)->count() }} / {{ $selectedProject->tasks->count() }} tasks completed</p>
        </div>
        <div class="flex flex-wrap gap-3">
            <button type="button" wire:click="startCreatingTask" wire:loading.attr="disabled" class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-700 disabled:opacity-50">+ New Task</button>
        </div>
    </div>

    @if (session()->has('status'))
        <div role="status" class="mb-6 rounded-lg border border-emerald-200 bg-emerald-50 p-4 text-emerald-800">{{ session('status') }}</div>
    @endif

    <div wire:loading.delay role="status" class="mb-4 text-sm text-indigo-700">Updating tasks…</div>

    @if ($showTaskForm)
        <div class="fixed inset-0 z-50 grid place-items-center bg-slate-950/40 p-4" role="dialog" aria-modal="true" aria-labelledby="task-form-title">
        <section class="w-full max-w-2xl rounded-xl bg-white p-6 shadow-xl">
            <h2 id="task-form-title" class="text-lg font-semibold">{{ $editingTaskId ? 'Edit task' : 'New task' }}</h2>
            <form wire:submit="saveTask" class="mt-5 grid gap-4 sm:grid-cols-2">
                <div>
                    <label for="task-title" class="mb-2 block text-sm font-medium">Task</label>
                    <input id="task-title" type="text" wire:model="taskTitle" maxlength="255" required aria-describedby="task-title-error" aria-invalid="{{ $errors->has('taskTitle') ? 'true' : 'false' }}" class="w-full rounded-lg border border-slate-300 px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-200">
                    <p id="task-title-error" aria-live="polite" class="mt-1 text-sm text-red-700">@error('taskTitle') {{ $message }} @enderror</p>
                </div>
                @if (! $editingTaskId)
                    <div>
                        <label for="task-assignee" class="mb-2 block text-sm font-medium">Assign to</label>
                        <select id="task-assignee" wire:model="assignedTo" aria-describedby="task-assignee-error" aria-invalid="{{ $errors->has('assignedTo') ? 'true' : 'false' }}" class="w-full rounded-lg border border-slate-300 px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-200">
                            <option value="">Unassigned</option>
                            @foreach ($users as $user)
                                <option wire:key="assignee-{{ $user->id }}" value="{{ $user->id }}">{{ $user->name }} ({{ $user->email }})</option>
                            @endforeach
                        </select>
                        <p id="task-assignee-error" aria-live="polite" class="mt-1 text-sm text-red-700">@error('assignedTo') {{ $message }} @enderror</p>
                    </div>
                @endif
                @if (! $editingTaskId && $users->isEmpty())
                    <p class="text-sm text-slate-600 sm:col-span-2">No team members are available yet. You can save the task as unassigned.</p>
                @endif
                <div class="flex gap-3 sm:col-span-2">
                    <button type="submit" wire:loading.attr="disabled" class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-700 disabled:opacity-50"><span wire:loading.remove wire:target="saveTask">{{ $editingTaskId ? 'Save task' : 'Create task' }}</span><span wire:loading wire:target="saveTask">Saving…</span></button>
                    <button type="button" wire:click="cancelTask" class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50">Cancel</button>
                </div>
            </form>
        </section>
        </div>
    @endif

    @if ($deletingTaskId !== null)
        <div class="fixed inset-0 z-50 grid place-items-center bg-slate-950/40 p-4" role="dialog" aria-modal="true" aria-labelledby="delete-task-title">
            <section class="w-full max-w-md rounded-xl bg-white p-6 shadow-xl">
                <h2 id="delete-task-title" class="text-xl font-semibold">Delete task?</h2>
                <p class="mt-2 text-slate-600">Delete “{{ $deletingTaskTitle }}”? This cannot be undone.</p>
                <div class="mt-6 flex justify-end gap-3">
                    <button type="button" wire:click="cancelDeleteTask" class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50">Cancel</button>
                    <button type="button" wire:click="deleteTask" wire:loading.attr="disabled" class="rounded-lg bg-red-600 px-4 py-2 text-sm font-semibold text-white hover:bg-red-700 disabled:opacity-50">Delete task</button>
                </div>
            </section>
        </div>
    @endif

    <section wire:key="tasks-{{ $selectedProject->id }}" class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm" aria-labelledby="tasks-heading">
        <h2 id="tasks-heading" class="sr-only">Task list</h2>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 text-left text-sm">
                <thead class="bg-slate-50 text-xs font-semibold uppercase tracking-wide text-slate-500">
                    <tr>
                        <th scope="col" class="px-6 py-4">Task</th>
                        <th scope="col" class="px-6 py-4">Status</th>
                        <th scope="col" class="px-6 py-4 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($selectedProject->tasks as $task)
                        <tr wire:key="task-{{ $task->id }}" class="hover:bg-slate-50">
                            <td class="px-6 py-4">
                                <label for="task-completed-{{ $task->id }}" @class(['block break-words font-medium', 'line-through text-slate-500' => $task->is_completed])>{{ $task->title }}</label>
                            </td>
                            <td class="px-6 py-4">
                                <label class="inline-flex cursor-pointer items-center gap-2">
                                    <input id="task-completed-{{ $task->id }}" type="checkbox" wire:click="toggleTask({{ $task->id }})" @checked($task->is_completed) wire:loading.attr="disabled" class="h-4 w-4 rounded accent-indigo-600 disabled:opacity-50">
                                    <span @class(['rounded-full px-3 py-1 text-xs font-semibold', 'bg-emerald-100 text-emerald-700' => $task->is_completed, 'bg-indigo-100 text-indigo-700' => ! $task->is_completed])>{{ $task->is_completed ? 'Completed' : 'Active' }}</span>
                                </label>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex justify-end gap-2">
                                    <button type="button" wire:click="editTask({{ $task->id }})" wire:loading.attr="disabled" aria-label="Edit task {{ $task->title }}" title="Edit task" class="grid h-8 w-8 place-items-center rounded-md bg-indigo-100/70 text-indigo-500 transition hover:bg-indigo-200 hover:text-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-400 disabled:opacity-50">
                                        <svg aria-hidden="true" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M13 5H3a1 1 0 0 0-1 1v15a1 1 0 0 0 1 1h15a1 1 0 0 0 1-1v-6" />
                                            <path d="m16 3 5 5M9 13l-1 5 5-1L23 7a2 2 0 0 0 0-3l-2-2a2 2 0 0 0-3 0Z" />
                                        </svg>
                                    </button>
                                    <button type="button" wire:click="confirmDeleteTask({{ $task->id }})" wire:loading.attr="disabled" aria-label="Delete task {{ $task->title }}" title="Delete task" class="grid h-8 w-8 place-items-center rounded-md bg-red-100/70 text-red-500 transition hover:bg-red-200 hover:text-red-700 focus:outline-none focus:ring-2 focus:ring-red-400 disabled:opacity-50">
                                        <svg aria-hidden="true" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M3 6h18M9 6V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2M5 6l1 14a1 1 0 0 0 1 1h10a1 1 0 0 0 1-1l1-14M10 10v7M14 10v7" />
                                        </svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="px-6 py-12 text-center text-sm text-slate-600">No tasks yet. Add the first task above.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
</div>
