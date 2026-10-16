<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\DB;

/**
 * Pantalla de roles (RoleController). Columnas reales: roles.id y roles.role_description.
 */
class RoleTest extends FeatureTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->actingAs($this->createUser(self::ROLE_1));
    }

    public function test_index_shows_ids_and_links_to_edit_and_delete(): void
    {
        $this->get('/stock/roles')
            ->assertOk()
            ->assertSee('<td>1</td>', false)
            ->assertSee(route('roles.edit', 4), false)
            ->assertSee(route('roles.destroy', 4), false);
    }

    public function test_index_search_filters_by_description(): void
    {
        $response = $this->get('/stock/roles?searchText=mana')->assertOk();

        $this->assertSame(['manager'], collect($response->viewData('roles')->items())->pluck('role_description')->all());
    }

    public function test_create_form_renders(): void
    {
        $this->get('/stock/roles/create')
            ->assertOk()
            ->assertSee('name="role_description"', false)
            ->assertSee(route('roles.store'), false);
    }

    public function test_store_creates_role(): void
    {
        $this->post('/stock/roles', ['role_description' => 'auditor'])
            ->assertRedirect('/stock/roles');

        $this->assertDatabaseHas('roles', ['role_description' => 'auditor']);
    }

    public function test_store_validates_description(): void
    {
        $this->post('/stock/roles', ['role_description' => ''])
            ->assertSessionHasErrors('role_description');

        $this->post('/stock/roles', ['role_description' => str_repeat('x', 31)])
            ->assertSessionHasErrors('role_description');
    }

    public function test_edit_form_renders_current_description(): void
    {
        $this->get('/stock/roles/4/edit')
            ->assertOk()
            ->assertSee('value="manager"', false)
            ->assertSee(route('roles.update', 4), false);
    }

    public function test_update_changes_description(): void
    {
        $id = DB::table('roles')->insertGetId(['role_description' => 'temporal']);

        $this->put("/stock/roles/{$id}", ['role_description' => 'renombrado'])
            ->assertRedirect('/stock/roles');

        $this->assertSame('renombrado', DB::table('roles')->where('id', $id)->value('role_description'));
    }

    public function test_destroy_deletes_unused_role(): void
    {
        $id = DB::table('roles')->insertGetId(['role_description' => 'temporal']);

        $this->delete("/stock/roles/{$id}")->assertRedirect('/stock/roles');

        $this->assertDatabaseMissing('roles', ['id' => $id]);
    }

    public function test_destroy_role_in_use_fails_and_keeps_role(): void
    {
        // Comportamiento actual: users.role_id tiene FK a roles.id sin cascada,
        // así que borrar un rol asignado a usuarios falla (500) y el rol se conserva.
        $this->delete('/stock/roles/1')->assertStatus(500);

        $this->assertDatabaseHas('roles', ['id' => 1, 'role_description' => 'admin']);
    }

    public function test_user_role_relation_uses_role_id(): void
    {
        $user = $this->createUser(self::ROLE_4);

        $this->assertSame('manager', $user->roles->role_description);
        $this->assertTrue(\App\Models\Role::find(4)->users->contains('id', $user->id));
    }
}
