<?php

namespace Tests\Feature;

use App\Models\Project;
use App\Models\Task;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class ProjectTaskRelationshipsTest extends TestCase
{
    use RefreshDatabase;

    public function test_projects_have_tasks_and_each_task_belongs_to_its_project(): void
    {
        $project = Project::create(['name' => 'TaskFlow', 'description' => 'Assessment project']);
        $otherProject = Project::create(['name' => 'Other', 'description' => 'Separate project']);
        $task = $project->tasks()->create(['title' => 'Build schema']);
        $otherProject->tasks()->create(['title' => 'Other task']);

        $this->assertTrue($task->project->is($project));
        $this->assertCount(1, $project->tasks);
        $this->assertTrue($project->tasks->first()->is($task));
        $this->assertFalse($task->is_completed);
        $this->assertFalse($task->fresh()->is_completed);

        $task->update(['is_completed' => true]);

        $this->assertTrue($task->fresh()->is_completed);
    }

    public function test_database_defaults_tasks_to_incomplete(): void
    {
        $project = Project::create(['name' => 'TaskFlow', 'description' => 'Assessment project']);
        $id = DB::table('tasks')->insertGetId(['project_id' => $project->id, 'title' => 'Default status']);

        $this->assertFalse(Task::findOrFail($id)->is_completed);
    }

    public function test_project_and_task_timestamps_are_persisted_and_updated(): void
    {
        $this->freezeSecond();
        $project = Project::create(['name' => 'TaskFlow', 'description' => 'Assessment project']);
        $task = $project->tasks()->create(['title' => 'Initial title']);
        $createdAt = now();

        $this->assertTrue($project->fresh()->created_at->equalTo($createdAt));
        $this->assertTrue($task->fresh()->created_at->equalTo($createdAt));

        $this->travel(1)->minute();
        $project->update(['name' => 'Updated project']);
        $task->update(['title' => 'Updated task']);

        foreach ([$project->fresh(), $task->fresh()] as $model) {
            $this->assertTrue($model->created_at->equalTo($createdAt));
            $this->assertTrue($model->updated_at->equalTo(now()));
        }
    }

    public function test_tasks_cannot_reference_a_missing_project(): void
    {
        $this->expectException(QueryException::class);

        Task::create(['project_id' => 999, 'title' => 'Orphan task']);
    }

    public function test_tasks_require_a_project(): void
    {
        $this->expectException(QueryException::class);

        Task::create(['title' => 'Missing project']);
    }

    public function test_database_cascades_project_deletion_without_affecting_other_projects(): void
    {
        $project = Project::create(['name' => 'TaskFlow', 'description' => 'Assessment project']);
        $task = $project->tasks()->create(['title' => 'Completed task', 'is_completed' => true]);
        $otherProject = Project::create(['name' => 'Other', 'description' => 'Separate project']);
        $otherTask = $otherProject->tasks()->create(['title' => 'Keep this task']);

        DB::table('projects')->where('id', $project->id)->delete();

        $this->assertModelMissing($project);
        $this->assertModelMissing($task);
        $this->assertModelExists($otherProject);
        $this->assertModelExists($otherTask);
    }
}
