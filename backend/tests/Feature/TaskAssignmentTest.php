<?php

namespace Tests\Feature;

use App\Livewire\ProjectTasks;
use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class TaskAssignmentTest extends TestCase
{
    use RefreshDatabase;

    public function test_task_can_be_assigned_reassigned_and_unassigned(): void
    {
        $project = Project::create(['name' => 'Website', 'description' => 'Launch']);
        $first = User::factory()->create(['name' => 'First member']);
        $second = User::factory()->create(['name' => 'Second member']);
        $component = Livewire::test(ProjectTasks::class, ['project' => $project])
            ->set('taskTitle', 'Build homepage')->set('assignedTo', (string) $first->id)
            ->call('saveTask')->assertHasNoErrors()->assertSet('assignedTo', '');
        $task = $project->tasks()->firstOrFail();
        $this->assertSame($first->id, $task->assignee->id);

        $component->call('editTask', $task->id)->assertSet('assignedTo', (string) $first->id)
            ->set('assignedTo', (string) $second->id)->call('saveTask')->assertHasNoErrors();
        $this->assertSame($second->id, $task->fresh()->assignee->id);

        $component->call('editTask', $task->id)->set('assignedTo', '')->call('saveTask')
            ->assertHasNoErrors()->assertSee('Unassigned');
        $this->assertNull($task->fresh()->assigned_to);
    }

    public function test_invalid_assignees_do_not_change_saved_tasks(): void
    {
        $project = Project::create(['name' => 'Website', 'description' => 'Launch']);
        $user = User::factory()->create();
        $task = $project->tasks()->create(['title' => 'Original', 'assigned_to' => $user->id]);
        $component = Livewire::test(ProjectTasks::class, ['project' => $project]);
        foreach (['999999', 'invalid'] as $value) {
            $component->set('taskTitle', 'New task')->set('assignedTo', $value)
                ->call('saveTask')->assertHasErrors('assignedTo');
            $component->call('editTask', $task->id)->set('assignedTo', $value)
                ->call('saveTask')->assertHasErrors('assignedTo');
        }
        $this->assertDatabaseCount('tasks', 1);
        $this->assertSame($user->id, $task->fresh()->assigned_to);
        $component->call('cancelTask')->assertSet('assignedTo', '')->assertHasNoErrors();
    }

    public function test_deleting_an_assignee_preserves_the_task_as_unassigned(): void
    {
        $project = Project::create(['name' => 'Website', 'description' => 'Launch']);
        $user = User::factory()->create();
        $task = $project->tasks()->create(['title' => 'Keep task', 'assigned_to' => $user->id]);
        $user->delete();
        $this->assertModelExists($task);
        $this->assertNull($task->fresh()->assigned_to);
    }
}
