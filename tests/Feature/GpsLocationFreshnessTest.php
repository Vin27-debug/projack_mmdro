<?php

namespace Tests\Feature;

use App\Models\Ambulance;
use App\Models\Driver;
use App\Models\GpsLocation;
use App\Models\User;
use App\Models\VulnerableArea;
use App\Models\VehicleDriverAssignment;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class GpsLocationFreshnessTest extends TestCase
{
    use RefreshDatabase;

    public function test_live_map_uses_latest_recorded_gps_location_and_excludes_archived_vehicles(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-10-02 12:00:00'));
        $admin = User::factory()->create(['status' => 'approved']);
        $admin->assignRole(Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']));

        $driverUser = User::factory()->create(['status' => 'approved']);
        $driver = Driver::create([
            'user_id' => $driverUser->id,
            'badge_id' => 'GPS-ORDER-' . $driverUser->id,
            'contact_number' => '09123456789',
            'license_number' => 'GPS-ORDER-LICENSE-' . $driverUser->id,
            'license_expiry' => '2030-01-01',
            'status' => Driver::STATUS_AVAILABLE,
        ]);
        $activeAmbulance = Ambulance::create([
            'plate_number' => 'GPS-ORDER-ACTIVE-' . $driver->id,
            'vehicle_name' => 'GPS Order Active Unit',
            'vehicle_type' => 'ambulance',
            'status' => Ambulance::STATUS_AVAILABLE,
        ]);
        Ambulance::create([
            'plate_number' => 'GPS-ORDER-ARCHIVED-' . $driver->id,
            'vehicle_name' => 'GPS Order Archived Unit',
            'vehicle_type' => 'ambulance',
            'status' => Ambulance::STATUS_AVAILABLE,
            'archived_at' => now(),
        ]);
        VehicleDriverAssignment::create([
            'driver_id' => $driver->id,
            'ambulance_id' => $activeAmbulance->id,
            'status' => 'active',
            'assigned_at' => now(),
        ]);

        GpsLocation::create([
            'driver_id' => $driver->id,
            'latitude' => 15.5,
            'longitude' => 120.9,
            'recorded_at' => now()->subSeconds(30),
        ]);
        GpsLocation::create([
            'driver_id' => $driver->id,
            'latitude' => 15.4,
            'longitude' => 120.8,
            'recorded_at' => now()->subSeconds(120),
        ]);

        $response = $this->actingAs($admin)
            ->getJson(route('admin.dashboard.live-command-map'))
            ->assertOk();

        $vehicles = collect($response->json('ambulances'))->keyBy('name');
        $this->assertCount(1, $vehicles);
        $this->assertArrayNotHasKey('GPS Order Archived Unit', $vehicles->all());
        $this->assertSame(15.5, $vehicles['GPS Order Active Unit']['latitude']);
        $this->assertSame('2026-10-02T11:59:30.000000Z', $vehicles['GPS Order Active Unit']['recorded_at']);
        $this->assertSame(15.5, $response->json('drivers.0.latitude'));
    }

    public function test_driver_gps_update_stores_the_reported_time_and_rejects_invalid_or_future_times(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-10-02 12:00:00'));
        $driverUser = User::factory()->create(['status' => 'approved']);
        $driverUser->assignRole(Role::firstOrCreate(['name' => 'driver', 'guard_name' => 'web']));
        $driver = Driver::create([
            'user_id' => $driverUser->id,
            'badge_id' => 'GPS-TIMESTAMP-' . $driverUser->id,
            'contact_number' => '09123456789',
            'license_number' => 'GPS-TIMESTAMP-LICENSE-' . $driverUser->id,
            'license_expiry' => '2030-01-01',
            'status' => Driver::STATUS_AVAILABLE,
        ]);
        $timestamp = now()->subSeconds(45);
        $payload = [
            'latitude' => 15.5,
            'longitude' => 120.9,
            'recorded_at' => $timestamp->toISOString(),
        ];

        $this->actingAs($driverUser)
            ->postJson(route('driver.gps.update'), $payload)
            ->assertOk();

        $location = $driver->gpsLocations()->latest('id')->firstOrFail();
        $this->assertTrue($location->recorded_at->equalTo($timestamp));

        $this->postJson(route('driver.gps.update'), [
            ...$payload,
            'recorded_at' => 'not-a-date',
        ])->assertUnprocessable();

        $this->postJson(route('driver.gps.update'), [
            ...$payload,
            'recorded_at' => now()->addSecond()->toISOString(),
        ])->assertUnprocessable();

        $staleTimestamp = now()->subMinutes(6);
        $this->postJson(route('driver.gps.update'), [
            ...$payload,
            'recorded_at' => $staleTimestamp->toISOString(),
        ])->assertOk();

        $staleLocation = $driver->gpsLocations()->latest('id')->firstOrFail();
        $this->assertTrue($staleLocation->recorded_at->equalTo($staleTimestamp));
        $this->assertSame(
            'stale',
            app(\App\Services\GpsFreshnessService::class)->metadata($staleLocation)['gps_status']
        );
        $this->assertSame(2, $driver->gpsLocations()->count());
    }

    public function test_driver_gps_update_defaults_a_missing_timestamp_to_server_time(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-10-02 12:00:00'));
        $driverUser = User::factory()->create(['status' => 'approved']);
        $driverUser->assignRole(Role::firstOrCreate(['name' => 'driver', 'guard_name' => 'web']));
        $driver = Driver::create([
            'user_id' => $driverUser->id,
            'badge_id' => 'GPS-MISSING-TIMESTAMP-' . $driverUser->id,
            'contact_number' => '09123456789',
            'license_number' => 'GPS-MISSING-TIMESTAMP-LICENSE-' . $driverUser->id,
            'license_expiry' => '2030-01-01',
            'status' => Driver::STATUS_AVAILABLE,
        ]);

        $this->actingAs($driverUser)
            ->postJson(route('driver.gps.update'), [
                'latitude' => 15.5,
                'longitude' => 120.9,
            ])
            ->assertOk();

        $this->assertTrue(
            $driver->gpsLocations()->latest('id')->firstOrFail()->recorded_at->equalTo(now())
        );
    }

    public function test_fleet_readiness_counts_only_dispatch_eligible_drivers_with_current_gps(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-10-02 12:00:00'));
        $admin = User::factory()->create(['status' => 'approved']);
        $admin->assignRole(Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']));

        foreach (['Fresh' => 300, 'Stale' => 301, 'Missing' => null] as $label => $ageSeconds) {
            $driverUser = User::factory()->create(['status' => 'approved']);
            $driver = Driver::create([
                'user_id' => $driverUser->id,
                'badge_id' => "GPS-ONLINE-{$label}-{$driverUser->id}",
                'contact_number' => '09123456789',
                'license_number' => "GPS-ONLINE-LICENSE-{$label}-{$driverUser->id}",
                'license_expiry' => '2030-01-01',
                'status' => Driver::STATUS_AVAILABLE,
            ]);

            if ($ageSeconds !== null) {
                GpsLocation::create([
                    'driver_id' => $driver->id,
                    'latitude' => 15.5,
                    'longitude' => 120.9,
                    'recorded_at' => now()->subSeconds($ageSeconds),
                ]);
            }
        }

        $this->actingAs($admin)
            ->getJson(route('admin.dashboard.fleet-readiness'))
            ->assertOk()
            ->assertJsonPath('drivers_online', 1);
    }

    public function test_live_map_does_not_return_future_gps_coordinates_for_markers(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-10-02 12:00:00'));
        $admin = User::factory()->create(['status' => 'approved']);
        $admin->assignRole(Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']));

        $driverUser = User::factory()->create(['status' => 'approved']);
        $driver = Driver::create([
            'user_id' => $driverUser->id,
            'badge_id' => 'GPS-FUTURE-MARKER-' . $driverUser->id,
            'contact_number' => '09123456789',
            'license_number' => 'GPS-FUTURE-MARKER-LICENSE-' . $driverUser->id,
            'license_expiry' => '2030-01-01',
            'status' => Driver::STATUS_AVAILABLE,
        ]);
        $ambulance = Ambulance::create([
            'plate_number' => 'GPS-FUTURE-MARKER-' . $driver->id,
            'vehicle_name' => 'Future Timestamp Unit',
            'vehicle_type' => 'ambulance',
            'status' => Ambulance::STATUS_AVAILABLE,
        ]);
        VehicleDriverAssignment::create([
            'driver_id' => $driver->id,
            'ambulance_id' => $ambulance->id,
            'status' => 'active',
            'assigned_at' => now(),
        ]);
        GpsLocation::create([
            'driver_id' => $driver->id,
            'latitude' => 15.4866,
            'longitude' => 120.9675,
            'recorded_at' => now()->addMinute(),
        ]);

        $response = $this->actingAs($admin)
            ->getJson(route('admin.dashboard.live-command-map'))
            ->assertOk();

        $unit = collect($response->json('ambulances'))->firstWhere('name', 'Future Timestamp Unit');
        $driverLocation = collect($response->json('drivers'))->firstWhere('id', $driver->id);
        $this->assertSame('invalid', $unit['gps_status']);
        $this->assertNull($unit['latitude']);
        $this->assertNull($unit['longitude']);
        $this->assertSame('invalid', $driverLocation['gps_status']);
        $this->assertNull($driverLocation['latitude']);
        $this->assertNull($driverLocation['longitude']);

        $this->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee("if (getGpsFreshness(item).status === 'invalid') return false;", false);
    }

    public function test_live_map_selects_highest_id_when_latest_recorded_at_is_tied(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-10-02 12:00:00'));
        $admin = User::factory()->create(['status' => 'approved']);
        $admin->assignRole(Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']));

        $driverUser = User::factory()->create(['status' => 'approved']);
        $driver = Driver::create([
            'user_id' => $driverUser->id,
            'badge_id' => 'GPS-TIED-TIMESTAMP-' . $driverUser->id,
            'contact_number' => '09123456789',
            'license_number' => 'GPS-TIED-TIMESTAMP-LICENSE-' . $driverUser->id,
            'license_expiry' => '2030-01-01',
            'status' => Driver::STATUS_AVAILABLE,
        ]);
        $ambulance = Ambulance::create([
            'plate_number' => 'GPS-TIED-TIMESTAMP-' . $driver->id,
            'vehicle_name' => 'Tied Timestamp Unit',
            'vehicle_type' => 'ambulance',
            'status' => Ambulance::STATUS_AVAILABLE,
        ]);
        VehicleDriverAssignment::create([
            'driver_id' => $driver->id,
            'ambulance_id' => $ambulance->id,
            'status' => 'active',
            'assigned_at' => now(),
        ]);
        $latestTimestamp = now()->subSeconds(30);
        GpsLocation::create([
            'driver_id' => $driver->id,
            'latitude' => 15.4,
            'longitude' => 120.8,
            'recorded_at' => $latestTimestamp,
        ]);
        $expectedLatestRow = GpsLocation::create([
            'driver_id' => $driver->id,
            'latitude' => 15.5,
            'longitude' => 120.9,
            'recorded_at' => $latestTimestamp,
        ]);

        $response = $this->actingAs($admin)
            ->getJson(route('admin.dashboard.live-command-map'))
            ->assertOk();

        $unit = collect($response->json('ambulances'))->firstWhere('name', 'Tied Timestamp Unit');
        $this->assertSame('fresh', $unit['gps_status']);
        $this->assertSame((float) $expectedLatestRow->latitude, $unit['latitude']);
        $this->assertSame((float) $expectedLatestRow->longitude, $unit['longitude']);

        $monitoringLocation = collect($this->getJson(route('admin.gps.locations'))->json())
            ->firstWhere('driver_id', $driver->id);
        $this->assertSame((float) $expectedLatestRow->latitude, $monitoringLocation['latitude']);
        $this->assertSame((float) $expectedLatestRow->longitude, $monitoringLocation['longitude']);
    }

    public function test_live_map_returns_timestamp_and_gps_freshness_for_each_vehicle(): void
    {
        config([
            'services.muniresq.location_fresh_seconds' => 60,
        ]);

        Carbon::setTestNow(Carbon::parse('2026-10-02 12:00:00'));
        /** @var User $admin */

        $role = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        $admin = User::factory()->create(['status' => 'approved']);
        $admin->assignRole($role);

        $ages = [
            'Fresh Unit' => 30,
            'Delayed Unit' => 120,
            'Boundary Unit' => 300,
            'Stale Unit' => 301,
            'Missing Unit' => null,
            'Future Unit' => -60,
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
        $this->assertSame('delayed', $vehicles['Boundary Unit']['gps_status']);
        $this->assertSame('stale', $vehicles['Stale Unit']['gps_status']);
        $this->assertNull($vehicles['Missing Unit']['recorded_at']);
        $this->assertSame('missing', $vehicles['Missing Unit']['gps_status']);
        $this->assertNull($vehicles['Missing Unit']['latitude']);
        $this->assertSame('invalid', $vehicles['Future Unit']['gps_status']);
        $this->assertNull($vehicles['Future Unit']['latitude']);
        $this->assertNull($vehicles['Future Unit']['longitude']);
        $this->assertNull($vehicles['Stale Unit']['latitude']);
        $this->assertNull($vehicles['Stale Unit']['longitude']);

        $monitoringVehicles = collect($this->getJson(route('admin.gps.locations'))->json())
            ->keyBy('vehicle_name');
        $this->assertSame('delayed', $monitoringVehicles['Delayed Unit']['gps_status']);
        $this->assertSame('delayed', $monitoringVehicles['Boundary Unit']['gps_status']);
        $this->assertSame('stale', $monitoringVehicles['Stale Unit']['gps_status']);
        $this->assertNull($monitoringVehicles['Stale Unit']['latitude']);
        $this->assertNull($monitoringVehicles['Stale Unit']['longitude']);
        $this->assertSame('missing', $monitoringVehicles['Missing Unit']['gps_status']);
        $this->assertSame('invalid', $monitoringVehicles['Future Unit']['gps_status']);

        $this->get(route('admin.gps.monitoring'))
            ->assertOk()
            ->assertSee('GPS Monitoring')
            ->assertSee('GPS_STALE_LIMIT_SECONDS = 300', false)
            ->assertSee('GPS LOCATION STALE — Tracking may be outdated.', false);

        $this->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee('liveVehicleList', false)
            ->assertSee('GPS LOCATION STALE — Tracking may be outdated.', false);
    }

    public function test_invalid_timestamp_is_reported_as_unavailable_instead_of_active(): void
    {
        $location = new GpsLocation();
        $location->setRawAttributes([
            'recorded_at' => 'not a valid timestamp',
            'accuracy' => null,
            'accuracy_meters' => null,
        ], true);

        $metadata = app(\App\Services\GpsFreshnessService::class)->metadata($location);

        $this->assertSame('invalid', $metadata['gps_status']);
        $this->assertNull($metadata['recorded_at']);
        $this->assertNull($metadata['gps_age_seconds']);
    }

    public function test_live_command_map_includes_active_geolocated_vulnerable_areas(): void
    {
        $role = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        $admin = User::factory()->create(['status' => 'approved']);
        $admin->assignRole($role);

        VulnerableArea::create([
            'name' => 'Riverside Community',
            'area_type' => 'Flood-prone community',
            'address' => 'Riverside barangay',
            'household_count' => 18,
            'population_count' => 64,
            'vulnerability_level' => 'High',
            'latitude' => 15.49,
            'longitude' => 120.97,
            'status' => 'active',
        ]);

        VulnerableArea::create([
            'name' => 'Inactive Area',
            'area_type' => 'Household Cluster',
            'address' => 'Inactive barangay',
            'household_count' => 1,
            'population_count' => 2,
            'vulnerability_level' => 'Critical',
            'latitude' => 15.5,
            'longitude' => 120.98,
            'status' => 'inactive',
        ]);

        $response = $this->actingAs($admin)
            ->getJson(route('admin.dashboard.live-command-map'))
            ->assertOk();

        $areas = $response->json('vulnerable_areas');
        $this->assertCount(1, $areas);
        $this->assertSame('Riverside Community', $areas[0]['name']);
        $this->assertSame('High', $areas[0]['vulnerability_level']);
        $this->assertSame(15.49, $areas[0]['latitude']);
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }
}
