<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class SuperAdminAdminManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_super_admin_can_create_list_and_approve_an_admin(): void
    {
        Role::create(['name' => 'super-admin', 'guard_name' => 'web']);
        Role::create(['name' => 'admin', 'guard_name' => 'web']);

        $superAdmin = User::factory()->create(['status' => 'approved']);
        $superAdmin->assignRole('super-admin');

        $response = $this->actingAs($superAdmin)->post(route('admins.store'), [
            'name' => 'Test Government Admin',
            'employee_id' => 'TEST-ADMIN-001',
            'position' => 'Administrative Officer',
            'department' => 'Municipal Government Office',
            'office' => 'Municipal Hall',
            'contact_number' => '09000000001',
            'email' => 'test-admin@example.test',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $admin = User::where('email', 'test-admin@example.test')->firstOrFail();

        $response->assertRedirect(route('admins.index'));
        $this->assertSame('pending', $admin->status);
        $this->assertNotNull($admin->created_at);
        $this->assertTrue($admin->hasRole('admin'));

        $this->actingAs($superAdmin)->get(route('admins.index'))
            ->assertOk()
            ->assertSee('Admin Management')
            ->assertSee('Test Government Admin');

        $this->actingAs($superAdmin)->post(route('admins.approve', $admin))
            ->assertRedirect();

        $admin->refresh();
        $this->assertSame('approved', $admin->status);
        $this->assertSame($superAdmin->id, $admin->approved_by);
        $this->assertNotNull($admin->approved_at);
    }
}
