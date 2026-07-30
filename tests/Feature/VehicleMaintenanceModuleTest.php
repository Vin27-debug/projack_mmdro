<?php

namespace Tests\Feature;

use App\Models\Ambulance;
use App\Models\User;
use App\Models\VehicleMaintenance;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class VehicleMaintenanceModuleTest extends TestCase
{
    use RefreshDatabase;

    public function test_vehicle_maintenance_index_shows_dashboard_cards_and_records(): void
    {
        Role::create(['name' => 'admin']);

        $admin = User::factory()->create([
            'status' => 'approved',
        ]);
        $admin->assignRole('admin');

        Ambulance::create([
            'plate_number' => 'ABC-123',
            'vehicle_name' => 'Ambulance 1',
            'vehicle_type' => 'ambulance',
            'status' => 'maintenance',
        ]);

        VehicleMaintenance::create([
            'ambulance_id' => 1,
            'maintenance_type' => 'Brake Service',
            'scheduled_date' => now()->toDateString(),
            'cost' => 180.50,
            'notes' => 'Routine brake inspection',
            'status' => 'scheduled',
        ]);

        $response = $this->actingAs($admin)->get('/admin/vehicle-maintenance');

        $response->assertOk();
        $response->assertSee('Total Vehicles');
        $response->assertSee('Vehicles Under Maintenance');
        $response->assertSee('Brake Service');
    }
}
