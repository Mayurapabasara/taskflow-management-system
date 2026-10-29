<?php

namespace Tests\Feature;

use App\Livewire\Projects;
use App\Models\Project;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Features\SupportLockedProperties\CannotUpdateLockedPropertyException;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class ProjectsTest extends TestCase
{
    use RefreshDatabase;

    public function test_project_page_renders_the_component_and_empty_state(): void
    {
        $this->get(route('projects.index'))
            ->assertOk()
            ->assertSeeLivewire(Projects::class)
            ->assertSee('No projects yet');
    }

    public function test_projects_can_be_created_with_trimmed_input(): void
    {
        Livewire::test(Projects::class)
            ->set('name', '  Assessment  ')
            ->set('description', '  Build TaskFlow.  ')
            ->call('save')
            ->assertHasNoErrors()
            ->assertSet('name', '')
            ->assertSet('description', '')
            ->assertSee('Project created.')
            ->assertSee('Assessment');

        $this->assertDatabaseHas('projects', ['name' => 'Assessment', 'description' => 'Build TaskFlow.']);
    }

    public function test_blank_project_fields_are_rejected(): void
    {
        Livewire::test(Projects::class)
            ->set('name', '   ')
            ->set('description', '   ')
            ->call('save')
            ->assertHasErrors(['name' => 'required', 'description' => 'required']);

        $this->assertDatabaseCount('projects', 0);
    }

    public function test_oversized_fields_are_rejected(): void
    {
        Livewire::test(Projects::class)
            ->set('name', str_repeat('a', 256))
            ->set('description', str_repeat('a', 10001))
            ->call('save')
            ->assertHasErrors(['name' => 'max', 'description' => 'max']);

        $this->assertDatabaseCount('projects', 0);
    }

    public function test_projects_can_be_edited_without_changing_another_project(): void
    {
        $project = $this->project();
        $other = $this->project('Other');

        Livewire::test(Projects::class)
            ->call('edit', $project->id)
            ->assertSet('name', $project->name)
            ->assertSet('description', $project->description)
            ->set('name', 'Updated')
            ->set('description', 'Updated description')
            ->call('save')
            ->assertHasNoErrors()
            ->assertSet('editingId', null)
            ->assertSee('Project updated.');

        $this->assertDatabaseHas('projects', ['id' => $project->id, 'name' => 'Updated', 'description' => 'Updated description']);
        $this->assertSame('Other', $other->fresh()->name);
        $this->assertDatabaseCount('projects', 2);
    }

    public function test_invalid_edits_preserve_saved_data_and_can_be_cancelled(): void
    {
        $project = $this->project();

        Livewire::test(Projects::class)
            ->call('edit', $project->id)
            ->set('name', '')
            ->call('save')
            ->assertHasErrors(['name' => 'required'])
            ->call('cancel')
            ->assertSet('editingId', null)
            ->assertSet('name', '')
            ->assertSet('description', '')
            ->assertHasNoErrors();

        $this->assertSame('Assessment', $project->fresh()->name);
    }

    public function test_project_list_shows_counts_scoped_to_each_project(): void
    {
        $project = $this->project();
        $project->tasks()->create(['title' => 'Done', 'is_completed' => true]);
        $project->tasks()->create(['title' => 'Pending']);
        $other = $this->project('Other');
        $other->tasks()->create(['title' => 'Other pending']);

        Livewire::test(Projects::class)
            ->assertSee('Assessment')
            ->assertSee('Project description')
            ->assertSee('1 / 2 tasks completed')
            ->assertSee('50%')
            ->assertSee('0 / 1 tasks completed');
    }

    public function test_deleting_a_project_resets_its_edit_form_and_preserves_other_projects(): void
    {
        $project = $this->project();
        $project->tasks()->create(['title' => 'Done', 'is_completed' => true]);
        $other = $this->project('Other');

        Livewire::test(Projects::class)
            ->call('edit', $project->id)
            ->call('delete', $project->id)
            ->assertSet('editingId', null)
            ->assertSee('Project deleted.')
            ->assertDontSee('Assessment');

        $this->assertModelMissing($project);
        $this->assertModelExists($other);
        $this->assertDatabaseCount('tasks', 0);
    }

    public function test_project_content_is_escaped(): void
    {
        $this->project('<script>alert(1)</script>');

        Livewire::test(Projects::class)
            ->assertSee('&lt;script&gt;alert(1)&lt;/script&gt;', false)
            ->assertDontSee('<script>alert(1)</script>', false);
    }

    public function test_missing_projects_return_not_found(): void
    {
        Livewire::test(Projects::class)->call('edit', 999)->assertNotFound();
        Livewire::test(Projects::class)->call('delete', 999)->assertNotFound();
    }

    #[DataProvider('invalidProjectFields')]
    public function test_each_invalid_field_is_rejected_on_creation_and_update(string $field, string $value, string $rule): void
    {
        $project = $this->project();

        Livewire::test(Projects::class)->set('name', 'Valid name')->set('description', 'Valid description')
            ->set($field, $value)->call('save')->assertHasErrors([$field => $rule]);

        Livewire::test(Projects::class)->call('edit', $project->id)
            ->set($field, $value)->call('save')->assertHasErrors([$field => $rule]);

        $this->assertDatabaseCount('projects', 1);
        $this->assertDatabaseHas('projects', ['id' => $project->id, 'name' => 'Assessment', 'description' => 'Project description']);
    }

    public static function invalidProjectFields(): array
    {
        return [
            'missing name' => ['name', '', 'required'],
            'whitespace name' => ['name', '   ', 'required'],
            'long name' => ['name', str_repeat('a', 256), 'max'],
            'missing description' => ['description', '', 'required'],
            'whitespace description' => ['description', " \n\t ", 'required'],
            'long description' => ['description', str_repeat('a', 10001), 'max'],
        ];
    }

    public function test_project_fields_accept_their_maximum_lengths(): void
    {
        Livewire::test(Projects::class)->set('name', str_repeat('n', 255))
            ->set('description', str_repeat('d', 10000))->call('save')->assertHasNoErrors();

        $this->assertDatabaseHas('projects', ['name' => str_repeat('n', 255), 'description' => str_repeat('d', 10000)]);
    }

    public function test_client_cannot_replace_the_project_editing_id(): void
    {
        $project = $this->project();
        $other = $this->project('Other');
        $this->expectException(CannotUpdateLockedPropertyException::class);

        Livewire::test(Projects::class)->call('edit', $project->id)->set('editingId', $other->id);
    }

    private function project(string $name = 'Assessment'): Project
    {
        return Project::create(['name' => $name, 'description' => 'Project description']);
    }
}
