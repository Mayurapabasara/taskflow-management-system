<?php

namespace App\Livewire;

use App\Actions\DeleteProject;
use App\Models\Project;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Locked;
use Livewire\Component;

class Projects extends Component
{
    #[Locked]
    public ?int $editingId = null;

    public string $name = '';

    public string $description = '';

    public bool $isActive = true;

    /** @var array<int, int|string> */
    public array $memberIds = [];

    public function save(): void
    {
        $this->name = trim($this->name);
        $this->description = trim($this->description);

        $data = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string', 'max:10000'],
            'isActive' => ['boolean'],
            'memberIds' => ['array'],
            'memberIds.*' => ['required', 'integer', 'distinct', 'exists:users,id'],
        ]);

        $projectData = [
            'name' => $data['name'],
            'description' => $data['description'],
            'is_active' => $data['isActive'],
        ];

        DB::transaction(function () use ($projectData, $data): void {
            if ($this->editingId !== null) {
                $project = Project::findOrFail($this->editingId);
                $project->update($projectData);
            } else {
                $project = Project::create($projectData);
            }

            $project->members()->sync($data['memberIds']);
        });
        $message = $this->editingId !== null ? 'Project updated.' : 'Project created.';

        $this->cancel();
        session()->flash('status', $message);
    }

    public function edit(int $id): void
    {
        $project = Project::findOrFail($id);
        $this->resetValidation();
        $this->editingId = $project->id;
        $this->name = $project->name;
        $this->description = $project->description;
        $this->isActive = $project->is_active;
        $this->memberIds = $project->members()->pluck('users.id')->all();
    }

    public function cancel(): void
    {
        $this->reset('editingId', 'name', 'description', 'isActive', 'memberIds');
        $this->resetValidation();
    }

    public function delete(int $id): void
    {
        $this->resetValidation('deletion');
        session()->forget('status');

        app(DeleteProject::class)->handle($id);

        if ($this->editingId === $id) {
            $this->cancel();
        }

        session()->flash('status', 'Project deleted.');
    }

    public function render(): View
    {
        return view('livewire.projects', [
            'users' => User::orderBy('name')->orderBy('id')->get(),
            'projects' => Project::with('members')->withCount([
                'tasks',
                'tasks as completed_tasks_count' => fn (Builder $query) => $query->where('is_completed', true),
            ])->latest('id')->get(),
        ]);
    }
}
