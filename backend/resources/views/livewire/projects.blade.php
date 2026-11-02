<div class="mx-auto max-w-2xl">
    <div class="mb-8">
        <p class="mb-2 text-xs font-semibold uppercase tracking-widest text-indigo-700">Project setup</p>
        <h1 class="text-3xl font-bold tracking-tight">New Project</h1>
        <p class="mt-2 text-slate-600">Create a project, then assign its tasks from Backup Manager.</p>
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
            <button type="submit" wire:loading.attr="disabled" class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-700 disabled:opacity-50">
                <span wire:loading.remove wire:target="save">Create project</span>
                <span wire:loading wire:target="save">Creating…</span>
            </button>
        </form>
    </section>
</div>
