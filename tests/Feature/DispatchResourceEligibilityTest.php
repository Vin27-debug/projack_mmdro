<?php

namespace Tests\Feature;

use App\Models\Ambulance;
use App\Models\Driver;
use App\Models\GpsLocation;
use App\Models\Incident;
use App\Models\User;
use App\Models\VehicleDriverAssignment;
use App\Services\DispatchRecommendationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class DispatchResourceEligibilityTest extends TestCase
{
    use RefreshDatabase;

    public function test_super_admin_sees_approved_active_drivers_without_gps(): void
    {
        $superAdmin = $this->createUserWithRole('super-admin');
        $driver = $this->createDriver('LIST-NO-GPS', gps: false);

        $this->actingAs($superAdmin)
            ->get(route('superadmin.drivers'))
            ->assertOk()
            ->assertSee($driver->badge_id)
            ->assertSee($driver->user->name);

        $this->assertDatabaseMissing('gps_locations', ['driver_id' => $driver->id]);
    }

    public function test_fresh_gps_makes_an_approved_available_driver_dispatch_eligible(): void
    {
        $driver = $this->createDriver('GPS-FRESH');

        $eligible = app(DispatchRecommendationService::class)->eligibleDrivers();

        $this->assertTrue($eligible->contains('id', $driver->id));
    }

    public function test_stale_and_missing_gps_drivers_are_excluded_from_dispatch(): void
    {
        $staleDriver = $this->createDriver('GPS-STALE', gps: false);
        $this->recordGps($staleDriver, now()->subMinutes(6));
        $missingDriver = $this->createDriver('GPS-MISSING', gps: false);

        $eligible = app(DispatchRecommendationService::class)->eligibleDrivers();

        $this->assertFalse($eligible->contains('id', $staleDriver->id));
        $this->assertFalse($eligible->contains('id', $missingDriver->id));
    }

    public function test_available_vehicle_is_listed_for_dispatch(): void
    {
        $admin = $this->createUserWithRole('admin');
        $driver = $this->createDriver('VEHICLE-LIST');
        $vehicle = $this->createVehicle('VEHICLE-LIST');
        $incident = $this->createIncident('INC-VEHICLE-LIST');

        $this->actingAs($admin)
            ->get(route('admin.incidents.dispatch.form', $incident))
            ->assertOk()
            ->assertSee($driver->badge_id)
            ->assertSee($vehicle->plate_number);
    }

    public function test_vehicle_assigned_to_an_unavailable_driver_is_not_dispatch_eligible(): void
    {
        $unavailableDriver = $this->createDriver(
            'VEHICLE-RESERVED',
            status: Driver::STATUS_EN_ROUTE
        );
        $vehicle = $this->createVehicle('VEHICLE-RESERVED');

        VehicleDriverAssignment::create([
            'driver_id' => $unavailableDriver->id,
            'ambulance_id' => $vehicle->id,
            'status' => 'active',
            'assigned_at' => now(),
        ]);

        $eligible = app(DispatchRecommendationService::class)->eligibleVehicles();

        $this->assertFalse($eligible->contains('id', $vehicle->id));
    }

    public function test_eligible_driver_and_available_vehicle_appear_on_dispatch_page(): void
    {
        $admin = $this->createUserWithRole('admin');
        $driver = $this->createDriver('DISPATCH-READY');
        $vehicle = $this->createVehicle('DISPATCH-READY');
        $incident = $this->createIncident('INC-DISPATCH-READY');

        $this->actingAs($admin)
            ->get(route('admin.incidents.dispatch.form', $incident))
            ->assertOk()
            ->assertSee($driver->badge_id)
            ->assertSee($vehicle->plate_number)
            ->assertSee('Dispatch Incident')
            ->assertDontSee('No eligible drivers are currently within a fresh GPS window.')
            ->assertDontSee('No eligible vehicles are available for dispatch.');
    }

    private function createUserWithRole(string $role): User
    {
        Role::firstOrCreate(['name' => $role, 'guard_name' => 'web']);
        $user = User::factory()->create(['status' => 'approved']);
        $user->assignRole($role);

        return $user;
    }

    private function createDriver(
        string $badge,
        bool $gps = true,
        string $status = Driver::STATUS_AVAILABLE
    ): Driver {
        $user = $this->createUserWithRole('driver');
        $driver = Driver::create([
            'user_id' => $user->id,
            'badge_id' => $badge,
            'contact_number' => '09123456789',
            'status' => $status,
            'management_status' => Driver::MANAGEMENT_STATUS_ACTIVE,
        ]);

        if ($gps) {
            $this->recordGps($driver, now());
        }

        return $driver;
    }

    private function recordGps(Driver $driver, $recordedAt): void
    {
        GpsLocation::create([
            'driver_id' => $driver->id,
            'latitude' => 14.5995,
            'longitude' => 120.9842,
            'recorded_at' => $recordedAt,
        ]);
    }

    private function createVehicle(string $plate): Ambulance
    {
        return Ambulance::create([
            'plate_number' => $plate,
            'vehicle_name' => 'Test Vehicle ' . $plate,
            'vehicle_type' => 'ambulance',
            'status' => Ambulance::STATUS_AVAILABLE,
        ]);
    }

    private function createIncident(string $number): Incident
    {
        return Incident::create([
            'incident_number' => $number,
            'reporter_name' => 'Eligibility Test',
            'incident_type' => 'Medical Emergency',
            'location' => 'Test Location',
            'status' => Incident::STATUS_PENDING,
        ]);
    }
}
