<?php

namespace App\Livewire;

use App\Models\Project;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Attributes\Locked;
use Livewire\Component;

class Projects extends Component
{
    #[Locked]
    public ?int $editingId = null;

    public string $name = '';

    public string $description = '';

    public function save(): void
    {
        $this->name = trim($this->name);
        $this->description = trim($this->description);

        $data = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string', 'max:10000'],
        ]);

        if ($this->editingId !== null) {
            Project::findOrFail($this->editingId)->update($data);
            $message = 'Project updated.';
        } else {
            Project::create($data);
            $message = 'Project created.';
        }

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
    }

    public function cancel(): void
    {
        $this->reset('editingId', 'name', 'description');
        $this->resetValidation();
    }

    public function delete(int $id): void
    {
        Project::findOrFail($id)->delete();

        if ($this->editingId === $id) {
            $this->cancel();
        }

        session()->flash('status', 'Project deleted.');
    }

    public function render(): View
    {
        return view('livewire.projects', [
            'projects' => Project::withCount([
                'tasks',
                'tasks as completed_tasks_count' => fn (Builder $query) => $query->where('is_completed', true),
            ])->latest('id')->get(),
        ]);
    }
}
