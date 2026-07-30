<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class AdminDashboardEmptyDataTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_dashboard_renders_with_empty_data(): void
    {
        Role::create(['name' => 'admin']);

        $admin = User::factory()->create([
            'status' => 'approved',
        ]);
        $admin->assignRole('admin');

        $this->actingAs($admin);

        $response = $this->get('/admin/dashboard');

        $response->assertStatus(200);
        $response->assertSee('Municipal Emergency Operations Center');
        $response->assertSee('No active panic alerts at this time.');
    }
}
