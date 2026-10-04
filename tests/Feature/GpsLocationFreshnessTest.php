<?php

namespace Tests\Feature;

use App\Models\Ambulance;
use App\Models\Driver;
use App\Models\GpsLocation;
use App\Models\User;
use App\Models\VehicleDriverAssignment;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class GpsLocationFreshnessTest extends TestCase
{
    use RefreshDatabase;

    public function test_live_map_returns_timestamp_and_gps_freshness_for_each_vehicle(): void
    {
        config([
            'services.muniresq.location_fresh_seconds' => 60,
            'services.muniresq.location_stale_limit_minutes' => 5,
        ]);

        Carbon::setTestNow(Carbon::parse('2026-10-02 12:00:00'));
        /** @var User $admin */

        $role = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        $admin = User::factory()->create(['status' => 'approved']);
        $admin->assignRole($role);

        $ages = [
            'Fresh Unit' => 30,
            'Delayed Unit' => 120,
            'Stale Unit' => 301,
            'Missing Unit' => null,
        ];

        foreach ($ages as $vehicleName => $ageSeconds) {
            $driverUser = User::factory()->create(['status' => 'approved']);
            $driver = Driver::create([
                'user_id' => $driverUser->id,
                'badge_id' => 'GPS-' . $vehicleName,
                'contact_number' => '09123456789',
                'license_number' => 'LIC-' . $vehicleName,
                'license_expiry' => '2030-01-01',
                'status' => 'available',
            ]);
            $ambulance = Ambulance::create([
                'plate_number' => 'PLATE-' . $vehicleName,
                'vehicle_name' => $vehicleName,
                'vehicle_type' => 'ambulance',
                'status' => 'available',
            ]);

            VehicleDriverAssignment::create([
                'driver_id' => $driver->id,
                'ambulance_id' => $ambulance->id,
                'status' => 'active',
                'assigned_at' => now(),
            ]);

            if ($ageSeconds !== null) {
                GpsLocation::create([
                    'driver_id' => $driver->id,
                    'latitude' => 15.4866,
                    'longitude' => 120.9675,
                    'recorded_at' => now()->subSeconds($ageSeconds),
                ]);
            }
        }

        $response = $this->actingAs($admin)
            ->getJson(route('admin.dashboard.live-command-map'))
            ->assertOk();

        $vehicles = collect($response->json('ambulances'))->keyBy('name');

        $this->assertSame('2026-10-02T11:59:30.000000Z', $vehicles['Fresh Unit']['recorded_at']);
        $this->assertSame('fresh', $vehicles['Fresh Unit']['gps_status']);
        $this->assertSame('delayed', $vehicles['Delayed Unit']['gps_status']);
        $this->assertSame('stale', $vehicles['Stale Unit']['gps_status']);
        $this->assertNull($vehicles['Missing Unit']['recorded_at']);
        $this->assertSame('missing', $vehicles['Missing Unit']['gps_status']);
        $this->assertNull($vehicles['Missing Unit']['latitude']);

        $monitoringVehicles = collect($this->getJson(route('admin.gps.locations'))->json())
            ->keyBy('vehicle_name');
        $this->assertSame('delayed', $monitoringVehicles['Delayed Unit']['gps_status']);
        $this->assertSame('stale', $monitoringVehicles['Stale Unit']['gps_status']);
        $this->assertSame('missing', $monitoringVehicles['Missing Unit']['gps_status']);

        $this->get(route('admin.gps.monitoring'))
            ->assertOk()
            ->assertSee('GPS Monitoring')
            ->assertSee('GPS_STALE_LIMIT_SECONDS', false);

        $this->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee('liveVehicleList', false);
    }
}
