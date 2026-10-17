<?php

namespace Tests\Feature\Security;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class PermissionManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        // Pastikan super_admin role dan permission terdaftar
        Role::findOrCreate(User::ROLE_SUPER_ADMIN);
        Role::findOrCreate(User::ROLE_IRBAN);
        Role::findOrCreate(User::ROLE_OPD);
    }

    public function test_guest_cannot_access_permissions_page(): void
    {
        $response = $this->get(route('permissions.index'));
        $response->assertRedirect(route('login'));
    }

    public function test_super_admin_can_view_permissions_index(): void
    {
        $superAdmin = User::factory()->create();
        $superAdmin->assignRole(User::ROLE_SUPER_ADMIN);

        $response = $this->actingAs($superAdmin)->get(route('permissions.index'));

        $response->assertOk();
        $response->assertSee('Hak Akses & Peran Pengguna', false);
        $response->assertSee('Daftar Peran');
        $response->assertSee('Katalog Hak Akses');
    }

    public function test_super_admin_can_view_edit_role_page(): void
    {
        $superAdmin = User::factory()->create();
        $superAdmin->assignRole(User::ROLE_SUPER_ADMIN);

        $irbanRole = Role::findByName(User::ROLE_IRBAN);

        $response = $this->actingAs($superAdmin)->get(route('permissions.edit', $irbanRole));

        $response->assertOk();
        $response->assertSee('Konfigurasi Hak Akses:');
        $response->assertSee('Pilih Semua');
    }

    public function test_super_admin_cannot_edit_super_admin_role_directly(): void
    {
        $superAdmin = User::factory()->create();
        $superAdmin->assignRole(User::ROLE_SUPER_ADMIN);

        $superAdminRole = Role::findByName(User::ROLE_SUPER_ADMIN);

        $response = $this->actingAs($superAdmin)->get(route('permissions.edit', $superAdminRole));

        $response->assertStatus(403);
    }

    public function test_can_update_role_permissions(): void
    {
        $superAdmin = User::factory()->create();
        $superAdmin->assignRole(User::ROLE_SUPER_ADMIN);

        $perm1 = Permission::findOrCreate('lhp.view');
        $perm2 = Permission::findOrCreate('lhp.create');

        $role = Role::findByName(User::ROLE_IRBAN);

        $response = $this->actingAs($superAdmin)->put(route('permissions.update', $role), [
            'permissions' => ['lhp.view', 'lhp.create'],
        ]);

        $response->assertRedirect(route('permissions.index'));
        $response->assertSessionHas('success');

        $this->assertTrue($role->fresh()->hasPermissionTo('lhp.view'));
        $this->assertTrue($role->fresh()->hasPermissionTo('lhp.create'));
    }

    public function test_cannot_delete_protected_role(): void
    {
        $superAdmin = User::factory()->create();
        $superAdmin->assignRole(User::ROLE_SUPER_ADMIN);

        $role = Role::findByName(User::ROLE_IRBAN);

        $response = $this->actingAs($superAdmin)->delete(route('permissions.destroy', $role));

        $response->assertSessionHas('error');
        $this->assertDatabaseHas('roles', ['name' => User::ROLE_IRBAN]);
    }
}
