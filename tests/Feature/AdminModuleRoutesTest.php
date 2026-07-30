<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class AdminModuleRoutesTest extends TestCase
{
    use RefreshDatabase;

    public function test_core_admin_modules_render(): void
    {
        Role::create(['name' => 'admin']);

        $admin = User::factory()->create([
            'status' => 'approved',
        ]);
        $admin->assignRole('admin');

        $this->actingAs($admin);

        $this->get('/admin/operations-center')->assertOk();
        $this->get('/admin/audit-logs')->assertOk();
        $this->get('/admin/incident-reports')->assertOk();
        $this->get('/admin/vehicle-maintenance')->assertOk();
        $this->get('/admin/reports-center')->assertOk();
        $this->get('/admin/reports/pdf/view')->assertOk();
    }
}
