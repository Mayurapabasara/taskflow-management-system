<?php

namespace Tests\Feature;

use App\Livewire\ProjectTasks;
use App\Models\Project;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProjectTasksPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_task_page_renders_for_a_project(): void
    {
        $project = Project::create(['name' => 'Website', 'description' => 'Launch the website']);
        $project->tasks()->create(['title' => 'Build homepage']);
        $this->get(route('backups.index'))->assertOk()
            ->assertSee(route('projects.tasks', $project), false)
            ->assertSee('Tasks')
            ->assertDontSee('Build homepage');
        $this->get(route('projects.tasks', $project))->assertOk()
            ->assertSeeLivewire(ProjectTasks::class)->assertDontSee('Website')->assertSee('Tasks')
            ->assertSee('Build homepage')->assertDontSee('← Back')->assertSee('+ New Task');
    }

    public function test_missing_project_task_page_returns_not_found(): void
    {
        $this->get(route('projects.tasks', 999))->assertNotFound();
    }
}
