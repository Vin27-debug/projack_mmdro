<?php

namespace Tests\Feature;

use App\Models\Ambulance;
use App\Models\Dispatch;
use App\Models\Driver;
use App\Models\Incident;
use App\Models\VehicleMaintenance;
use App\Models\User;
use App\Services\DispatchRecommendationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class DriverStatusAndArchiveTest extends TestCase
{
    use RefreshDatabase;

    public function test_driver_registration_without_approval_works_without_license_details(): void
    {
        $this->post(route('driver.register.store'), [
            'name' => 'License Optional Driver',
            'email' => 'license-optional@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'contact_number' => '09123456000',
        ])->assertSessionHasNoErrors();

        $driverUser = User::where('email', 'license-optional@example.com')->firstOrFail();
        $this->assertNull($driverUser->driver->license_number);
        $this->assertNull($driverUser->driver->license_expiry);

        $this->assertSame('approved', $driverUser->fresh()->status);
        $this->assertTrue($driverUser->fresh()->hasRole('driver'));
        $this->assertNull($driverUser->driver->fresh()->license_number);
        $this->assertNull($driverUser->driver->fresh()->license_expiry);
    }

    public function test_active_driver_is_eligible_and_suspended_driver_cannot_be_assigned(): void
    {
        $admin = $this->createAdmin();
        $activeDriver = $this->createDriver('Active Driver', 'AMB-ACTIVE');
        $suspendedDriver = $this->createDriver('Suspended Driver', 'AMB-SUSPENDED', 'suspended');
        $vehicle = $this->createVehicle('UNIT-STATUS');
        $incident = $this->createIncident('INC-STATUS');

        $this->actingAs($admin)
            ->get(route('admin.dispatches.index'))
            ->assertOk()
            ->assertSee('Active Driver')
            ->assertDontSee('Suspended Driver');

        $response = $this->actingAs($admin)->post(route('admin.dispatches.assign', $incident), [
            'driver_id' => $activeDriver->id,
            'ambulance_id' => $vehicle->id,
        ]);
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('dispatches', [
            'incident_id' => $incident->id,
            'driver_id' => $activeDriver->id,
            'vehicle_id' => $vehicle->id,
        ]);

        $secondIncident = $this->createIncident('INC-SUSPENDED');
        $this->actingAs($admin)->post(route('admin.dispatches.assign', $secondIncident), [
            'driver_id' => $suspendedDriver->id,
            'ambulance_id' => $this->createVehicle('UNIT-SUSPENDED')->id,
        ])->assertSessionHas('error');

        $this->assertDatabaseMissing('dispatches', [
            'incident_id' => $secondIncident->id,
            'driver_id' => $suspendedDriver->id,
        ]);
        $this->assertFalse(Driver::dispatchEligible()->whereKey($suspendedDriver->id)->exists());
    }

    public function test_suspending_driver_preserves_existing_incident_history_and_excludes_recommendations(): void
    {
        $superAdmin = $this->createSuperAdmin();
        $driver = $this->createDriver('History Driver', 'AMB-HISTORY');
        $incident = $this->createIncident('INC-HISTORY');
        $dispatch = Dispatch::create([
            'incident_id' => $incident->id,
            'driver_id' => $driver->id,
            'status' => Dispatch::STATUS_COMPLETED,
            'completed_at' => now(),
        ]);

        $this->actingAs($superAdmin)
            ->post(route('superadmin.drivers.status', $driver), ['status' => 'suspended'])
            ->assertSessionHasNoErrors();

        $this->assertSame('approved', $driver->user->fresh()->status);
        $this->assertSame('suspended', $driver->fresh()->management_status);
        $this->assertDatabaseHas('incidents', ['id' => $incident->id]);
        $this->assertDatabaseHas('dispatches', ['id' => $dispatch->id, 'driver_id' => $driver->id]);

        $recommendation = app(DispatchRecommendationService::class)->recommend(
            $incident,
            collect([$driver->fresh()->load('user')]),
            collect()
        );
        $this->assertSame([], $recommendation['eligibleDrivers']);

        $this->actingAs($driver->user)
            ->delete('/profile', ['password' => 'password'])
            ->assertRedirect('/profile')
            ->assertSessionHasErrorsIn('userDeletion', 'account');
        $this->assertDatabaseHas('drivers', ['id' => $driver->id]);
        $this->assertDatabaseHas('incidents', ['id' => $incident->id]);
    }

    public function test_driver_archive_hides_from_active_list_and_can_be_restored_without_losing_history(): void
    {
        $superAdmin = $this->createSuperAdmin();
        $driver = $this->createDriver('HistoryOnlyDriver42', 'AMB-ARCHIVE');
        $incident = $this->createIncident('INC-DRIVER-ARCHIVE');
        $dispatch = Dispatch::create([
            'incident_id' => $incident->id,
            'driver_id' => $driver->id,
            'status' => Dispatch::STATUS_ASSIGNED,
        ]);

        $this->actingAs($superAdmin)
            ->post(route('superadmin.drivers.archive', $driver))
            ->assertSessionHas('error');
        $this->assertNull($driver->fresh()->archived_at);

        $dispatch->update([
            'status' => Dispatch::STATUS_COMPLETED,
            'completed_at' => now(),
        ]);

        $this->post(route('superadmin.drivers.archive', $driver))
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('drivers', ['id' => $driver->id]);
        $this->assertDatabaseHas('incidents', ['id' => $incident->id]);
        $this->assertDatabaseHas('dispatches', ['id' => $dispatch->id]);

        $this->get(route('superadmin.drivers'))
            ->assertOk()
            ->assertDontSee('HistoryOnlyDriver42');
        $this->get(route('superadmin.drivers', ['archived' => 1]))
            ->assertOk()
            ->assertSee('HistoryOnlyDriver42')
            ->assertSee('Restore');

        $this->post(route('superadmin.drivers.restore', $driver))
            ->assertSessionHasNoErrors();
        $this->assertNull($driver->fresh()->archived_at);
        $this->get(route('superadmin.drivers'))
            ->assertOk()
            ->assertSee('HistoryOnlyDriver42');
    }

    public function test_vehicle_archive_and_maintenance_archive_preserve_history_and_support_restore(): void
    {
        $admin = $this->createAdmin();
        $vehicle = $this->createVehicle('UNIT-ARCHIVE');
        $incident = $this->createIncident('INC-VEHICLE-ARCHIVE');
        $dispatch = Dispatch::create([
            'incident_id' => $incident->id,
            'driver_id' => $this->createDriver('Vehicle History Driver', 'AMB-VEHICLE-HISTORY')->id,
            'vehicle_id' => $vehicle->id,
            'status' => Dispatch::STATUS_EN_ROUTE,
        ]);
        $maintenance = VehicleMaintenance::create([
            'ambulance_id' => $vehicle->id,
            'maintenance_type' => 'Annual Inspection',
            'description' => 'Historical maintenance entry',
            'scheduled_date' => now()->toDateString(),
            'status' => 'completed',
        ]);

        $this->actingAs($admin)
            ->post(route('admin.ambulances.archive', $vehicle))
            ->assertSessionHas('error');
        $this->assertNull($vehicle->fresh()->archived_at);

        $dispatch->update([
            'status' => Dispatch::STATUS_COMPLETED,
            'completed_at' => now(),
        ]);

        $this->post(route('admin.ambulances.archive', $vehicle))
            ->assertSessionHasNoErrors();
        $this->assertDatabaseHas('ambulances', ['id' => $vehicle->id]);
        $this->assertDatabaseHas('dispatches', ['id' => $dispatch->id, 'vehicle_id' => $vehicle->id]);
        $this->get(route('admin.ambulances.index'))->assertOk()->assertDontSee('UNIT-ARCHIVE');
        $this->get(route('admin.ambulances.index', ['archived' => 1]))
            ->assertOk()
            ->assertSee('UNIT-ARCHIVE')
            ->assertSee('Restore');

        $this->post(route('admin.ambulances.restore', $vehicle))->assertSessionHasNoErrors();
        $this->assertNull($vehicle->fresh()->archived_at);

        $this->post(route('admin.maintenance.archive', $maintenance))
            ->assertSessionHasNoErrors();
        $this->assertDatabaseHas('vehicle_maintenances', [
            'id' => $maintenance->id,
            'ambulance_id' => $vehicle->id,
        ]);
        $this->get(route('admin.maintenance.index'))->assertOk()->assertDontSee('Annual Inspection');
        $this->get(route('admin.maintenance.index', ['archived' => 1]))
            ->assertOk()
            ->assertSee('Annual Inspection')
            ->assertSee('Restore');

        $this->post(route('admin.maintenance.restore', $maintenance))->assertSessionHasNoErrors();
        $this->assertNull($maintenance->fresh()->archived_at);
    }

    private function createSuperAdmin(): User
    {
        Role::firstOrCreate(['name' => 'super-admin', 'guard_name' => 'web']);
        $user = User::factory()->create(['status' => 'approved']);
        $user->assignRole('super-admin');

        return $user;
    }

    private function createAdmin(): User
    {
        Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        $user = User::factory()->create(['status' => 'approved']);
        $user->assignRole('admin');

        return $user;
    }

    private function createDriver(string $name, string $badge, string $managementStatus = 'active'): Driver
    {
        $user = User::factory()->create([
            'name' => $name,
            'status' => 'approved',
        ]);

        return Driver::create([
            'user_id' => $user->id,
            'badge_id' => $badge,
            'contact_number' => '09123456789',
            'status' => Driver::STATUS_AVAILABLE,
            'management_status' => $managementStatus,
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
            'reporter_name' => 'Archive Test',
            'incident_type' => 'Medical Emergency',
            'location' => 'Test Location',
            'status' => Incident::STATUS_PENDING,
        ]);
    }
}
