<?php

namespace App\Livewire;

use App\Models\Project;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Locked;
use Livewire\Component;

class ProjectTasks extends Component
{
    #[Locked]
    public ?int $selectedProjectId = null;

    #[Locked]
    public ?int $editingTaskId = null;

    public string $taskTitle = '';

    public function mount(Project $project): void
    {
        $this->selectedProjectId = $project->id;
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

    public function render(): View
    {
        return view('livewire.project-tasks', [
            'selectedProject' => Project::with(['tasks' => fn ($query) => $query->orderBy('id')])->findOrFail($this->selectedProjectId),
        ]);
    }
}
