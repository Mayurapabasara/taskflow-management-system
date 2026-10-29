<?php

namespace App\Livewire;

use App\Actions\DeleteProject;
use App\Models\Project;
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

    #[Locked]
    public ?int $selectedProjectId = null;

    #[Locked]
    public ?int $editingTaskId = null;

    public string $taskTitle = '';

    public function selectProject(int $id): void
    {
        Project::findOrFail($id);
        $this->selectedProjectId = $id;
        $this->cancelTask();
    }

    public function saveTask(): void
    {
        $this->taskTitle = trim($this->taskTitle);
        $this->validate(['taskTitle' => ['required', 'string', 'max:255']], [], ['taskTitle' => 'title']);

        DB::transaction(function (): void {
            $project = Project::query()->lockForUpdate()->findOrFail($this->selectedProjectId);

            if ($this->editingTaskId !== null) {
                $project->tasks()->findOrFail($this->editingTaskId)->update(['title' => $this->taskTitle]);
            } else {
                $project->tasks()->create(['title' => $this->taskTitle]);
            }
        }, attempts: 3);

        $this->cancelTask();
        session()->flash('status', 'Task saved.');
    }

    public function editTask(int $id): void
    {
        $task = Project::findOrFail($this->selectedProjectId)->tasks()->findOrFail($id);
        $this->resetValidation('taskTitle');
        $this->editingTaskId = $task->id;
        $this->taskTitle = $task->title;
    }

    public function cancelTask(): void
    {
        $this->reset('editingTaskId', 'taskTitle');
        $this->resetValidation('taskTitle');
    }

    public function deleteTask(int $id): void
    {
        DB::transaction(function () use ($id): void {
            $project = Project::query()->lockForUpdate()->findOrFail($this->selectedProjectId);
            $project->tasks()->findOrFail($id)->delete();
        }, attempts: 3);

        if ($this->editingTaskId === $id) {
            $this->cancelTask();
        }

        session()->flash('status', 'Task deleted.');
    }

    public function toggleTask(int $id): void
    {
        DB::transaction(function () use ($id): void {
            $project = Project::query()->lockForUpdate()->findOrFail($this->selectedProjectId);
            $task = $project->tasks()->lockForUpdate()->findOrFail($id);
            $task->update(['is_completed' => ! $task->is_completed]);
        }, attempts: 3);
    }

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
        $this->resetValidation('deletion');
        session()->forget('status');

        app(DeleteProject::class)->handle($id);

        if ($this->selectedProjectId === $id) {
            $this->selectedProjectId = null;
            $this->cancelTask();
        }

        if ($this->editingId === $id) {
            $this->cancel();
        }

        session()->flash('status', 'Project deleted.');
    }

    public function render(): View
    {
        return view('livewire.projects', [
            'selectedProject' => $this->selectedProjectId === null
                ? null
                : Project::with(['tasks' => fn ($query) => $query->orderBy('id')])->findOrFail($this->selectedProjectId),
            'projects' => Project::withCount([
                'tasks',
                'tasks as completed_tasks_count' => fn (Builder $query) => $query->where('is_completed', true),
            ])->latest('id')->get(),
        ]);
    }
}
