<?php

namespace Tests\Feature;

use App\Livewire\BackupManager;
use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BackupManagerPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_backup_manager_lists_projects_and_links_to_project_creation(): void
    {
        $project = Project::create(['name' => 'Nightly backup', 'description' => 'Protect production data']);
        $assignee = User::factory()->create(['name' => 'Database administrator']);
        $project->tasks()->create(['title' => 'Verify restoration', 'is_completed' => true, 'assigned_to' => $assignee->id]);
        $project->tasks()->create(['title' => 'Review retention']);

        $this->get('/')
            ->assertOk()
            ->assertSeeLivewire(BackupManager::class)
            ->assertSee('Nightly backup')
            ->assertSee('Database administrator')
            ->assertSee('Not configured')
            ->assertSee(route('projects.index').'#project-form', false)
            ->assertSee('Download backup, coming soon')
            ->assertDontSee(route('projects.tasks', $project), false);
    }

    public function test_backup_manager_shows_an_empty_state_when_no_projects_exist(): void
    {
        $this->get(route('backups.index'))
            ->assertOk()
            ->assertSee('No projects yet')
            ->assertSee('Add your first project to see it here.');
    }
}
