<?php

namespace App\Livewire;

use App\Models\Project;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Component;

class BackupManager extends Component
{
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
