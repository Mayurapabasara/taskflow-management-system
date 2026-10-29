<?php

namespace Tests\Feature;

use App\Livewire\Projects;
use App\Models\Project;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Features\SupportLockedProperties\CannotUpdateLockedPropertyException;
use Livewire\Livewire;
use Tests\TestCase;

class TasksTest extends TestCase
{
    use RefreshDatabase;

    public function test_only_selected_project_tasks_are_listed(): void
    {
        $first = $this->project();
        $second = $this->project();
        $first->tasks()->create(['title' => 'First task']);
        $second->tasks()->create(['title' => 'Second task']);

        Livewire::test(Projects::class)
            ->assertDontSee('First task')
            ->call('selectProject', $first->id)
            ->assertSee('First task')
            ->assertDontSee('Second task')
            ->call('selectProject', $second->id)
            ->assertSee('Second task')
            ->assertDontSee('First task');
    }

    public function test_tasks_are_created_in_the_selected_project_and_counts_update(): void
    {
        $project = $this->project();

        Livewire::test(Projects::class)
            ->call('selectProject', $project->id)
            ->assertSee('No tasks yet.')
            ->set('taskTitle', '  New task  ')
            ->call('saveTask')
            ->assertHasNoErrors()
            ->assertSet('taskTitle', '')
            ->assertSee('New task')
            ->assertSee('0 / 1 tasks completed');

        $this->assertDatabaseHas('tasks', ['project_id' => $project->id, 'title' => 'New task', 'is_completed' => false]);
    }

    public function test_task_title_validation_applies_to_creation_and_editing(): void
    {
        $project = $this->project();
        $task = $project->tasks()->create(['title' => 'Original']);
        $component = Livewire::test(Projects::class)->call('selectProject', $project->id);

        foreach (['   ' => 'required', str_repeat('a', 256) => 'max'] as $title => $rule) {
            $component->set('taskTitle', $title)->call('saveTask')->assertHasErrors(['taskTitle' => $rule]);
        }

        $component->call('editTask', $task->id)->set('taskTitle', '')->call('saveTask')
            ->assertHasErrors(['taskTitle' => 'required']);

        $this->assertSame('Original', $task->fresh()->title);
        $this->assertDatabaseCount('tasks', 1);
    }

    public function test_titles_can_be_edited_without_changing_completion_or_project(): void
    {
        $project = $this->project();
        $task = $project->tasks()->create(['title' => 'Original', 'is_completed' => true]);

        Livewire::test(Projects::class)->call('selectProject', $project->id)
            ->call('editTask', $task->id)->assertSet('taskTitle', 'Original')
            ->set('taskTitle', ' Updated ')->call('saveTask')->assertHasNoErrors()
            ->assertSet('editingTaskId', null)->assertSee('Updated');

        $this->assertDatabaseHas('tasks', ['id' => $task->id, 'project_id' => $project->id, 'title' => 'Updated', 'is_completed' => true]);
    }

    public function test_deleting_a_task_clears_its_edit_form_and_updates_counts(): void
    {
        $project = $this->project();
        $task = $project->tasks()->create(['title' => 'Remove me']);
        $otherTask = $this->project()->tasks()->create(['title' => 'Keep me']);

        Livewire::test(Projects::class)->call('selectProject', $project->id)
            ->call('editTask', $task->id)->call('deleteTask', $task->id)
            ->assertHasNoErrors()->assertSet('editingTaskId', null)->assertSet('taskTitle', '')
            ->assertSee('No tasks yet.')->assertSee('0 / 0 tasks completed');

        $this->assertModelMissing($task);
        $this->assertModelExists($otherTask);
    }

    public function test_another_projects_tasks_cannot_be_edited_or_deleted(): void
    {
        $project = $this->project();
        $otherTask = $this->project()->tasks()->create(['title' => 'Protected']);

        Livewire::test(Projects::class)->call('selectProject', $project->id)
            ->call('editTask', $otherTask->id)->assertNotFound();
        Livewire::test(Projects::class)->call('selectProject', $project->id)
            ->call('deleteTask', $otherTask->id)->assertNotFound();

        $this->assertSame('Protected', $otherTask->fresh()->title);
    }

    public function test_switching_projects_clears_task_edit_state_and_errors(): void
    {
        $project = $this->project();
        $other = $this->project();
        $task = $project->tasks()->create(['title' => 'Original']);

        Livewire::test(Projects::class)->call('selectProject', $project->id)
            ->call('editTask', $task->id)->set('taskTitle', '')->call('saveTask')
            ->assertHasErrors('taskTitle')->call('selectProject', $other->id)
            ->assertHasNoErrors()->assertSet('editingTaskId', null)->assertSet('taskTitle', '');
    }

    public function test_client_cannot_replace_the_task_editing_id(): void
    {
        $project = $this->project();
        $task = $project->tasks()->create(['title' => 'Original']);
        $otherTask = $this->project()->tasks()->create(['title' => 'Protected']);

        $this->expectException(CannotUpdateLockedPropertyException::class);

        Livewire::test(Projects::class)->call('selectProject', $project->id)
            ->call('editTask', $task->id)->set('editingTaskId', $otherTask->id);
    }

    public function test_task_operations_require_a_selected_existing_project(): void
    {
        Livewire::test(Projects::class)->set('taskTitle', 'Orphan')->call('saveTask')->assertNotFound();
        Livewire::test(Projects::class)->call('selectProject', 999)->assertNotFound();
        $this->assertDatabaseCount('tasks', 0);
    }

    public function test_completion_toggles_both_directions_with_reactive_counts_and_styling(): void
    {
        $project = $this->project();
        $task = $project->tasks()->create(['title' => 'Toggle this task']);

        $component = Livewire::test(Projects::class)->call('selectProject', $project->id)
            ->assertSee('0 / 1 tasks completed')->assertSee('0%')
            ->assertDontSee('line-through');
        $this->assertCheckboxState($component->html(), $task->id, false);

        $component->call('toggleTask', $task->id)->assertHasNoErrors()
            ->assertSee('1 / 1 tasks completed')->assertSee('100%')->assertSee('line-through');
        $this->assertTrue($task->fresh()->is_completed);
        $this->assertCheckboxState($component->html(), $task->id, true);

        $component->call('toggleTask', $task->id)->assertHasNoErrors()
            ->assertSee('0 / 1 tasks completed')->assertSee('0%')->assertDontSee('line-through');
        $this->assertFalse($task->fresh()->is_completed);
        $this->assertCheckboxState($component->html(), $task->id, false);
    }

    public function test_completion_changes_are_scoped_to_the_selected_project(): void
    {
        $project = $this->project();
        $task = $project->tasks()->create(['title' => 'Selected']);
        $other = $this->project();
        $otherTask = $other->tasks()->create(['title' => 'Other']);

        Livewire::test(Projects::class)->call('selectProject', $project->id)
            ->call('toggleTask', $task->id)
            ->assertSee('1 / 1 tasks completed')->assertSee('0 / 1 tasks completed');
        $this->assertFalse($otherTask->fresh()->is_completed);

        Livewire::test(Projects::class)->call('selectProject', $project->id)
            ->call('toggleTask', $otherTask->id)->assertNotFound();
        $this->assertFalse($otherTask->fresh()->is_completed);
    }

    public function test_completion_requires_an_existing_task_and_selected_project(): void
    {
        $project = $this->project();
        $task = $project->tasks()->create(['title' => 'Unchanged']);

        Livewire::test(Projects::class)->call('toggleTask', $task->id)->assertNotFound();
        Livewire::test(Projects::class)->call('selectProject', $project->id)
            ->call('toggleTask', 999)->assertNotFound();

        $this->assertFalse($task->fresh()->is_completed);
    }

    private function assertCheckboxState(string $html, int $taskId, bool $checked): void
    {
        $document = new \DOMDocument;
        @$document->loadHTML($html);
        $checkbox = $document->getElementById('task-completed-'.$taskId);
        $this->assertNotNull($checkbox);
        $this->assertSame($checked, $checkbox->hasAttribute('checked'));
    }

    private function project(): Project
    {
        return Project::create(['name' => 'Project', 'description' => 'Task CRUD']);
    }
}
