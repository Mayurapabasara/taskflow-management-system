<div>
    <a href="{{ route('projects.index') }}" class="mb-6 inline-flex text-sm font-medium text-indigo-700 hover:underline">← Back to projects</a>
    <p class="mb-2 text-xs font-semibold uppercase tracking-widest text-indigo-700">Project workspace</p>
    <h1 class="break-words text-3xl font-bold tracking-tight">{{ $selectedProject->name }}</h1>
    <p class="mt-2 whitespace-pre-line break-words text-slate-600">{{ $selectedProject->description }}</p>
    <p class="my-6 text-sm font-medium text-indigo-700">{{ $selectedProject->tasks->where('is_completed', true)->count() }} / {{ $selectedProject->tasks->count() }} tasks completed · {{ $selectedProject->tasks->count() ? round($selectedProject->tasks->where('is_completed', true)->count() / $selectedProject->tasks->count() * 100) : 0 }}%</p>
    @if (session()->has('status'))
        <div role="status" class="mb-6 rounded-lg border border-emerald-200 bg-emerald-50 p-4 text-emerald-800">{{ session('status') }}</div>
    @endif
    <div wire:loading.delay role="status" class="mb-4 text-sm text-indigo-700">Updating tasks…</div>
    <section wire:key="tasks-{{ $selectedProject->id }}" class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm" aria-labelledby="tasks-heading">
        <h2 id="tasks-heading" class="break-words text-xl font-semibold">Tasks for {{ $selectedProject->name }}</h2>
        <p class="mt-2 text-sm text-slate-600">Check a task to mark it complete. Changes are saved automatically.</p>
        <form wire:submit="saveTask" class="mt-5 space-y-3">
            <label for="task-title" class="block text-sm font-medium">{{ $editingTaskId ? 'Edit task title' : 'New task title' }}</label>
            <input id="task-title" type="text" wire:model="taskTitle" maxlength="255" required aria-describedby="task-title-error" aria-invalid="{{ $errors->has('taskTitle') ? 'true' : 'false' }}" class="w-full rounded-lg border border-slate-300 px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-200">
            <p id="task-title-error" aria-live="polite" class="text-sm text-red-700">@error('taskTitle') {{ $message }} @enderror</p>
            <label for="task-assignee" class="block text-sm font-medium">Assign to</label>
            <select id="task-assignee" wire:model="assignedTo" aria-describedby="task-assignee-error" aria-invalid="{{ $errors->has('assignedTo') ? 'true' : 'false' }}" class="w-full rounded-lg border border-slate-300 px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-200">
                <option value="">Unassigned</option>
                @foreach ($users as $user)
                    <option wire:key="assignee-{{ $user->id }}" value="{{ $user->id }}">{{ $user->name }} ({{ $user->email }})</option>
                @endforeach
            </select>
            <p id="task-assignee-error" aria-live="polite" class="text-sm text-red-700">@error('assignedTo') {{ $message }} @enderror</p>
            @if ($users->isEmpty())
                <p class="text-sm text-slate-600">No team members available yet. You can save tasks as unassigned.</p>
            @endif
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
                    <span class="rounded-full bg-slate-100 px-3 py-1 text-xs text-slate-600">{{ $task->assignee?->name ?? 'Unassigned' }}</span>
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
</div>
