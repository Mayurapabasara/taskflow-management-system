<?php

namespace App\Livewire;

use App\Actions\DeleteProject;
use App\Models\Project;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Component;

class BackupManager extends Component
{
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
