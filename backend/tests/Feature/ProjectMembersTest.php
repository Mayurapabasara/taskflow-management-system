<?php

namespace Tests\Feature;

use App\Livewire\Projects;
use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ProjectMembersTest extends TestCase
{
    use RefreshDatabase;

    public function test_members_can_be_selected_replaced_and_removed(): void
    {
        $first = User::factory()->create();
        $second = User::factory()->create();
        $third = User::factory()->create();

        $component = Livewire::test(Projects::class)
            ->set('name', 'Team project')->set('description', 'Description')
            ->set('memberIds', [(string) $first->id, (string) $second->id])
            ->call('save')->assertHasNoErrors()->assertSet('memberIds', []);

        $project = Project::sole();
        $this->assertEqualsCanonicalizing([$first->id, $second->id], $project->members->modelKeys());
        $this->assertStringContainsString($first->name.', '.$second->name, $component->html());

        $component->call('edit', $project->id)
            ->assertSet('memberIds', [$first->id, $second->id])
            ->set('memberIds', [$third->id])->call('save')->assertHasNoErrors();
        $this->assertSame([$third->id], $project->fresh()->members->modelKeys());

        $component->call('edit', $project->id)->set('memberIds', [])
            ->call('save')->assertHasNoErrors();
        $this->assertCount(0, $project->fresh()->members);
    }

    public function test_invalid_members_preserve_project_details_and_memberships(): void
    {
        $user = User::factory()->create();
        $project = Project::create(['name' => 'Original', 'description' => 'Description']);
        $project->members()->attach($user);

        foreach ([[999999], ['invalid'], [$user->id, $user->id]] as $selection) {
            Livewire::test(Projects::class)->call('edit', $project->id)
                ->set('name', 'Changed')->set('memberIds', $selection)
                ->call('save')->assertHasErrors('memberIds.0');
            $this->assertSame('Original', $project->fresh()->name);
            $this->assertSame([$user->id], $project->fresh()->members->modelKeys());
        }

        Livewire::test(Projects::class)->set('name', 'New')->set('description', 'Description')
            ->set('memberIds', [999999])->call('save')->assertHasErrors('memberIds.0');
        $this->assertDatabaseCount('projects', 1);

        Livewire::test(Projects::class)->call('edit', $project->id)->call('cancel')
            ->assertSet('memberIds', []);
    }

    public function test_deleting_projects_or_users_removes_only_their_memberships(): void
    {
        $first = User::factory()->create();
        $second = User::factory()->create();
        $project = Project::create(['name' => 'Team', 'description' => 'Description']);
        $other = Project::create(['name' => 'Other', 'description' => 'Description']);
        $project->members()->attach([$first->id, $second->id]);
        $other->members()->attach($second);

        $first->delete();
        $this->assertSame([$second->id], $project->fresh()->members->modelKeys());
        Livewire::test(Projects::class)->call('delete', $project->id);
        $this->assertDatabaseMissing('project_user', ['project_id' => $project->id]);
        $this->assertSame([$second->id], $other->fresh()->members->modelKeys());
        $this->assertModelExists($second);
    }
}
