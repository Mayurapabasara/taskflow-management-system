<?php

namespace App\Actions;

use App\Models\Project;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class DeleteProject
{
    public function handle(int $projectId): void
    {
        DB::transaction(function () use ($projectId): void {
            // Lock the parent first: InnoDB foreign-key checks then block new tasks.
            $project = Project::query()->lockForUpdate()->findOrFail($projectId);

            // Lock every task, including completed ones, to prevent status changes
            // between checking the rule and deleting the project.
            $tasks = $project->tasks()->orderBy('id')->lockForUpdate()->get();

            if ($tasks->contains(fn ($task) => ! $task->is_completed)) {
                throw ValidationException::withMessages([
                    'deletion' => 'This project cannot be deleted because it has incomplete tasks. Complete all tasks first.',
                ]);
            }

            // The foreign key removes associated tasks within this transaction.
            $project->delete();
        }, attempts: 3);
    }
}
