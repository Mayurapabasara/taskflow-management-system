<?php

namespace Tests\Feature;

use App\Livewire\BackupManager;
use App\Models\Project;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class BackupManagerPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_landing_page_lists_project_details_with_edit_and_delete_actions(): void
    {
        $project = Project::create(['name' => 'Nightly backup', 'description' => 'Protect production data']);
        $project->tasks()->create(['title' => 'Verify restoration', 'is_completed' => true]);
        $project->tasks()->create(['title' => 'Review retention']);
        $inactive = Project::create(['name' => 'Archived project', 'description' => 'Paused', 'is_active' => false]);

        $this->get('/')
            ->assertOk()
            ->assertSeeLivewire(BackupManager::class)
            ->assertSee('Nightly backup')
            ->assertSee('2')
            ->assertSee('1')
            ->assertSee('Active')
            ->assertSee('Inactive')
            ->assertSee('+ Add Project')
            ->assertSee('Edit')
            ->assertSee('Delete')
            ->assertDontSee('Backup Manager')
            ->assertDontSee('Database')
            ->assertDontSee('Host')
            ->assertDontSee('Port');

        $this->assertModelExists($inactive);
    }

    public function test_add_and_edit_actions_open_a_project_popup(): void
    {
        $project = Project::create(['name' => 'Existing', 'description' => 'Original']);

        $component = Livewire::test(BackupManager::class)
            ->call('startCreatingProject')
            ->assertSet('showProjectForm', true)
            ->assertSee('New Project')
            ->set('name', 'Created in popup')
            ->set('description', 'Created without leaving the landing page')
            ->call('saveProject')
            ->assertHasNoErrors()
            ->assertSet('showProjectForm', false)
            ->assertSee('Project created.');

        $component->call('edit', $project->id)
            ->assertSet('showProjectForm', true)
            ->assertSet('editingId', $project->id)
            ->assertSet('name', 'Existing')
            ->set('name', 'Updated in popup')
            ->call('saveProject')
            ->assertHasNoErrors()
            ->assertSee('Project updated.');

        $this->assertDatabaseHas('projects', ['name' => 'Created in popup']);
        $this->assertDatabaseHas('projects', ['id' => $project->id, 'name' => 'Updated in popup']);
    }

    public function test_landing_page_delete_action_removes_a_project_without_tasks(): void
    {
        $project = Project::create(['name' => 'Remove', 'description' => 'Unused']);

        Livewire::test(BackupManager::class)->call('delete', $project->id)
            ->assertHasNoErrors()->assertSee('Project deleted.');

        $this->assertModelMissing($project);
    }

    public function test_backup_manager_shows_an_empty_state_when_no_projects_exist(): void
    {
        $this->get(route('backups.index'))
            ->assertOk()
            ->assertSee('No projects yet')
            ->assertSee('Add your first project to see it here.');
    }
}
