<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuditAssignmentWebTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleAndPermissionSeeder::class);
    }

    public function test_audit_assignment_create_page_can_be_rendered(): void
    {
        $admin = User::factory()->create(['jenis_kelamin' => 'L']);
        $admin->assignRole('super_admin');

        $response = $this->actingAs($admin)->get('/audit-assignment/create');
        if ($response->getStatusCode() !== 200) {
            if ($response->exception) {
                dump($response->exception->getMessage());
                dump($response->exception->getFile() . ':' . $response->exception->getLine());
                dump($response->exception->getTraceAsString());
            }
        }
        $response->assertOk();
    }
}
