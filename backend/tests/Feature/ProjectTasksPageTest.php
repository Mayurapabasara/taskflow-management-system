<?php

namespace Tests\Feature;

use App\Livewire\ProjectTasks;
use App\Models\Project;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProjectTasksPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_projects_link_to_a_separate_task_page(): void
    {
        $project = Project::create(['name' => 'Website', 'description' => 'Launch the website']);
        $project->tasks()->create(['title' => 'Build homepage']);
        $this->get(route('backups.index'))->assertOk()
            ->assertSee(route('projects.tasks', $project), false)
            ->assertDontSee('Build homepage');
        $this->get(route('projects.tasks', $project))->assertOk()
            ->assertSeeLivewire(ProjectTasks::class)->assertSee('Website')
            ->assertSee('Build homepage')->assertSee('← Back')->assertSee('+ New Task');
    }

    public function test_missing_project_task_page_returns_not_found(): void
    {
        $this->get(route('projects.tasks', 999))->assertNotFound();
    }
}
