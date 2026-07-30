<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class GpsHistoryAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_access_gps_history_page(): void
    {
        $role = Role::firstOrCreate(['name' => 'admin']);
        $user = User::factory()->create(['status' => 'approved']);
        $user->assignRole($role);

        $response = $this->actingAs($user)->get('/admin/gps-history');

        $response->assertOk();
        $response->assertSee('GPS History');
    }

    public function test_super_admin_can_access_gps_history_page(): void
    {
        $role = Role::firstOrCreate(['name' => 'super-admin']);
        $user = User::factory()->create(['status' => 'approved']);
        $user->assignRole($role);

        $response = $this->actingAs($user)->get('/admin/gps-history');

        $response->assertOk();
        $response->assertSee('GPS History');
    }
}
