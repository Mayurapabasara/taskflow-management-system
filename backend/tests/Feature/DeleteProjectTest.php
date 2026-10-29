<?php

namespace Tests\Feature;

use App\Actions\DeleteProject;
use App\Livewire\Projects;
use App\Models\Project;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Livewire\Livewire;
use Tests\TestCase;

class DeleteProjectTest extends TestCase
{
    use RefreshDatabase;

    public function test_incomplete_tasks_block_deletion_and_display_a_clear_error(): void
    {
        $project = $this->project();
        $pending = $project->tasks()->create(['title' => 'Pending']);
        $completed = $project->tasks()->create(['title' => 'Done', 'is_completed' => true]);

        Livewire::test(Projects::class)
            ->call('edit', $project->id)
            ->call('delete', $project->id)
            ->assertHasErrors('deletion')
            ->assertSee('This project cannot be deleted because it has incomplete tasks. Complete all tasks first.')
            ->assertDontSee('Project deleted.')
            ->assertSet('editingId', $project->id);

        $this->assertModelExists($project);
        $this->assertModelExists($pending);
        $this->assertModelExists($completed);
        $this->assertFalse($pending->fresh()->is_completed);
        $this->assertTrue($completed->fresh()->is_completed);
    }

    public function test_action_enforces_the_rule_without_livewire(): void
    {
        $project = $this->project();
        $task = $project->tasks()->create(['title' => 'Pending']);

        try {
            app(DeleteProject::class)->handle($project->id);
            $this->fail('Deletion should have been rejected.');
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey('deletion', $exception->errors());
        }

        $this->assertModelExists($project);
        $this->assertModelExists($task);
    }

    public function test_project_with_only_completed_tasks_is_deleted_with_its_tasks(): void
    {
        $project = $this->project();
        $first = $project->tasks()->create(['title' => 'First', 'is_completed' => true]);
        $second = $project->tasks()->create(['title' => 'Second', 'is_completed' => true]);
        $other = $this->project();
        $otherTask = $other->tasks()->create(['title' => 'Keep']);

        Livewire::test(Projects::class)
            ->call('delete', $project->id)
            ->assertHasNoErrors()
            ->assertSee('Project deleted.');

        $this->assertModelMissing($project);
        $this->assertModelMissing($first);
        $this->assertModelMissing($second);
        $this->assertModelExists($other);
        $this->assertModelExists($otherTask);
    }

    public function test_project_without_tasks_can_be_deleted(): void
    {
        $project = $this->project();

        Livewire::test(Projects::class)
            ->call('delete', $project->id)
            ->assertHasNoErrors()
            ->assertSee('Project deleted.');

        $this->assertModelMissing($project);
    }

    public function test_deletion_can_be_retried_after_all_tasks_are_completed(): void
    {
        $project = $this->project();
        $task = $project->tasks()->create(['title' => 'Pending']);
        $component = Livewire::test(Projects::class)
            ->call('delete', $project->id)
            ->assertHasErrors('deletion');

        $task->update(['is_completed' => true]);

        $component->call('delete', $project->id)
            ->assertHasNoErrors()
            ->assertSee('Project deleted.');

        $this->assertModelMissing($project);
        $this->assertModelMissing($task);
    }

    public function test_livewire_completion_changes_control_the_deletion_rule(): void
    {
        $project = $this->project();
        $task = $project->tasks()->create(['title' => 'Task']);
        $component = Livewire::test(Projects::class)->call('selectProject', $project->id);

        $component->call('toggleTask', $task->id)->call('toggleTask', $task->id)
            ->call('delete', $project->id)->assertHasErrors('deletion')
            ->assertSet('selectedProjectId', $project->id);
        $this->assertModelExists($project);
        $this->assertModelExists($task);

        $component->call('toggleTask', $task->id)->call('delete', $project->id)
            ->assertHasNoErrors()->assertSet('selectedProjectId', null)->assertSet('editingTaskId', null)
            ->assertSee('Project deleted.');
        $this->assertModelMissing($project);
        $this->assertModelMissing($task);
    }

    private function project(): Project
    {
        return Project::create(['name' => 'Assessment', 'description' => 'Deletion rule']);
    }
}
