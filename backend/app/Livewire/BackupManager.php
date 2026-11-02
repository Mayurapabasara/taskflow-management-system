<?php

namespace App\Livewire;

use App\Actions\DeleteProject;
use App\Models\Project;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Attributes\Locked;
use Livewire\Component;

class BackupManager extends Component
{
    #[Locked]
    public ?int $editingId = null;

    public string $name = '';

    public string $description = '';

    public bool $isActive = true;

    public bool $showProjectForm = false;

    public function startCreatingProject(): void
    {
        $this->cancelProjectForm();
        $this->showProjectForm = true;
    }

    public function saveProject(): void
    {
        $this->name = trim($this->name);
        $this->description = trim($this->description);

        $data = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string', 'max:10000'],
            'isActive' => ['boolean'],
        ]);

        $projectData = [
            'name' => $data['name'],
            'description' => $data['description'],
            'is_active' => $data['isActive'],
        ];

        if ($this->editingId !== null) {
            Project::findOrFail($this->editingId)->update($projectData);
            $message = 'Project updated.';
        } else {
            Project::create($projectData);
            $message = 'Project created.';
        }

        $this->cancelProjectForm();
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
        $this->showProjectForm = true;
    }

    public function cancelProjectForm(): void
    {
        $this->reset('editingId', 'name', 'description', 'isActive', 'showProjectForm');
        $this->resetValidation();
    }

    public function delete(int $id): void
    {
        $this->resetValidation('deletion');
        session()->forget('status');

        app(DeleteProject::class)->handle($id);

        session()->flash('status', 'Project deleted.');
    }

    public function render(): View
    {
        return view('livewire.backup-manager', [
            'projects' => Project::withCount([
                'tasks',
                'tasks as completed_tasks_count' => fn (Builder $query) => $query->where('is_completed', true),
            ])->latest('id')->get(),
        ]);
    }
}
