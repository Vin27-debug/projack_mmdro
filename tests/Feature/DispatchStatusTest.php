<?php

namespace Tests\Feature;

use App\Models\Ambulance;
use App\Models\Dispatch;
use App\Models\Driver;
use App\Models\Incident;
use App\Models\IncidentReport;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class DispatchStatusTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_assign_a_dispatch_with_a_supported_status(): void
    {
        $role = Role::firstOrCreate(['name' => 'admin']);
        $user = User::factory()->create(['status' => 'approved']);
        $user->assignRole($role);

        $driver = Driver::create([
            'user_id' => $user->id,
            'badge_id' => 'AMB-100',
            'contact_number' => '09123456789',
            'license_number' => 'LIC-100',
            'license_expiry' => '2030-01-01',
        ]);

        $ambulance = Ambulance::create([
            'plate_number' => 'ABC-100',
            'vehicle_name' => 'Ambulance One',
            'vehicle_type' => 'ambulance',
            'status' => 'available',
        ]);

        $incident = Incident::create([
            'incident_number' => 'INC-0100',
            'reporter_name' => 'Jane Doe',
            'contact_number' => '09120000000',
            'incident_type' => 'Medical',
            'location' => 'Test Street',
            'description' => 'Sample incident',
            'status' => 'pending',
        ]);

        $response = $this->actingAs($user)->post(route('admin.dispatches.assign', $incident), [
            'driver_id' => $driver->id,
            'vehicle_id' => $ambulance->id,
        ]);

        $response->assertSessionHas('success');
        $this->assertDatabaseHas('dispatches', [
            'incident_id' => $incident->id,
            'driver_id' => $driver->id,
            'vehicle_id' => $ambulance->id,
            'status' => Dispatch::STATUS_ASSIGNED,
        ]);

        $this->assertDatabaseHas('incidents', [
            'id' => $incident->id,
            'driver_id' => $driver->id,
            'ambulance_id' => $ambulance->id,
            'status' => 'dispatched',
        ]);
    }

    public function test_driver_dashboard_shows_accept_and_decline_actions_for_assigned_dispatches(): void
    {
        $role = Role::firstOrCreate(['name' => 'driver']);
        $user = User::factory()->create(['status' => 'approved']);
        $user->assignRole($role);

        $driver = Driver::create([
            'user_id' => $user->id,
            'badge_id' => 'AMB-104',
            'contact_number' => '09123456793',
            'license_number' => 'LIC-104',
            'license_expiry' => '2030-01-01',
            'status' => 'available',
        ]);

        $ambulance = Ambulance::create([
            'plate_number' => 'ABC-104',
            'vehicle_name' => 'Ambulance Five',
            'vehicle_type' => 'ambulance',
            'status' => 'on_duty',
        ]);

        $incident = Incident::create([
            'incident_number' => 'INC-0104',
            'reporter_name' => 'Carol Doe',
            'contact_number' => '09120000004',
            'incident_type' => 'Medical',
            'location' => 'Test Plaza',
            'description' => 'Sample incident',
            'status' => 'dispatched',
            'driver_id' => $driver->id,
            'ambulance_id' => $ambulance->id,
        ]);

        Dispatch::create([
            'incident_id' => $incident->id,
            'driver_id' => $driver->id,
            'vehicle_id' => $ambulance->id,
            'status' => Dispatch::STATUS_ASSIGNED,
            'assigned_at' => now(),
        ]);

        $response = $this->actingAs($user)->get(route('driver.dashboard'));

        $response->assertOk();
        $response->assertSee('Accept Dispatch');
        $response->assertSee('Decline Dispatch');
        $response->assertDontSee('Mark En Route');
    }

    public function test_driver_dashboard_shows_next_emergency_action_in_sequence(): void
    {
        $role = Role::firstOrCreate(['name' => 'driver']);
        $user = User::factory()->create(['status' => 'approved']);
        $user->assignRole($role);

        $driver = Driver::create([
            'user_id' => $user->id,
            'badge_id' => 'AMB-105',
            'contact_number' => '09123456794',
            'license_number' => 'LIC-105',
            'license_expiry' => '2030-01-01',
            'status' => 'en_route',
        ]);

        $ambulance = Ambulance::create([
            'plate_number' => 'ABC-105',
            'vehicle_name' => 'Ambulance Six',
            'vehicle_type' => 'ambulance',
            'status' => 'on_duty',
        ]);

        $incident = Incident::create([
            'incident_number' => 'INC-0105',
            'reporter_name' => 'Dana Doe',
            'contact_number' => '09120000005',
            'incident_type' => 'Medical',
            'location' => 'Test Avenue',
            'description' => 'Sample incident',
            'status' => Incident::STATUS_DISPATCHED,
            'driver_id' => $driver->id,
            'ambulance_id' => $ambulance->id,
            'call_received_at' => now()->subMinutes(20),
            'response_at' => now()->subMinutes(14),
            'at_scene_at' => now()->subMinutes(7),
        ]);

        Dispatch::create([
            'incident_id' => $incident->id,
            'driver_id' => $driver->id,
            'vehicle_id' => $ambulance->id,
            'status' => Dispatch::STATUS_EN_ROUTE,
            'accepted_at' => now()->subMinutes(15),
            'en_route_at' => now()->subMinutes(14),
        ]);

        $response = $this->actingAs($user)->get(route('driver.dashboard'));

        $response->assertOk();
        $response->assertSee('Mark At Patient');
        $response->assertDontSee('Mark At Scene');
    }

    public function test_driver_gps_update_syncs_ambulance_coordinates_via_active_dispatch(): void
    {
        $role = Role::firstOrCreate(['name' => 'driver']);
        $user = User::factory()->create(['status' => 'approved']);
        $user->assignRole($role);

        $driver = Driver::create([
            'user_id' => $user->id,
            'badge_id' => 'AMB-102',
            'contact_number' => '09123456791',
            'license_number' => 'LIC-102',
            'license_expiry' => '2030-01-01',
            'status' => 'available',
        ]);

        $ambulance = Ambulance::create([
            'plate_number' => 'ABC-102',
            'vehicle_name' => 'Ambulance Three',
            'vehicle_type' => 'ambulance',
            'status' => 'on_duty',
        ]);

        $incident = Incident::create([
            'incident_number' => 'INC-0102',
            'reporter_name' => 'Alice Doe',
            'contact_number' => '09120000002',
            'incident_type' => 'Medical',
            'location' => 'Test Road',
            'description' => 'Sample incident',
            'status' => 'dispatched',
            'driver_id' => $driver->id,
            'ambulance_id' => $ambulance->id,
        ]);

        Dispatch::create([
            'incident_id' => $incident->id,
            'driver_id' => $driver->id,
            'vehicle_id' => $ambulance->id,
            'status' => Dispatch::STATUS_ASSIGNED,
            'assigned_at' => now(),
        ]);

        $response = $this->actingAs($user)->post(route('driver.gps.update'), [
            'latitude' => '15.421484',
            'longitude' => '120.842789',
        ]);

        $response->assertJsonPath('success', true);
        $this->assertDatabaseHas('ambulances', [
            'id' => $ambulance->id,
            'latitude' => '15.421484',
            'longitude' => '120.842789',
        ]);
    }

    public function test_driver_decline_dispatch_returns_the_incident_to_pending(): void
    {
        $role = Role::firstOrCreate(['name' => 'driver']);
        $user = User::factory()->create(['status' => 'approved']);
        $user->assignRole($role);

        $driver = Driver::create([
            'user_id' => $user->id,
            'badge_id' => 'AMB-103',
            'contact_number' => '09123456792',
            'license_number' => 'LIC-103',
            'license_expiry' => '2030-01-01',
            'status' => 'available',
        ]);

        $ambulance = Ambulance::create([
            'plate_number' => 'ABC-103',
            'vehicle_name' => 'Ambulance Four',
            'vehicle_type' => 'ambulance',
            'status' => 'on_duty',
        ]);

        $incident = Incident::create([
            'incident_number' => 'INC-0103',
            'reporter_name' => 'Bob Doe',
            'contact_number' => '09120000003',
            'incident_type' => 'Medical',
            'location' => 'Test Street',
            'description' => 'Sample incident',
            'status' => 'dispatched',
            'driver_id' => $driver->id,
            'ambulance_id' => $ambulance->id,
        ]);

        $dispatch = Dispatch::create([
            'incident_id' => $incident->id,
            'driver_id' => $driver->id,
            'vehicle_id' => $ambulance->id,
            'status' => Dispatch::STATUS_ASSIGNED,
            'assigned_at' => now(),
        ]);

        $response = $this->actingAs($user)->post(route('driver.dispatch.decline', $dispatch));

        $response->assertSessionHas('success');
        $this->assertDatabaseHas('dispatches', [
            'id' => $dispatch->id,
            'status' => Dispatch::STATUS_CANCELLED,
        ]);
        $this->assertDatabaseHas('incidents', [
            'id' => $incident->id,
            'status' => Incident::STATUS_PENDING,
        ]);
    }

    public function test_driver_can_switch_to_an_available_vehicle_when_accepting(): void
    {
        $this->withoutMiddleware(PreventRequestForgery::class);

        Role::firstOrCreate(['name' => 'driver']);
        $user = User::factory()->create(['status' => 'approved']);
        $user->assignRole('driver');
        $driver = Driver::create([
            'user_id' => $user->id,
            'badge_id' => 'AMB-106',
            'contact_number' => '09123456795',
            'license_number' => 'LIC-106',
            'license_expiry' => '2030-01-01',
            'status' => Driver::STATUS_ASSIGNED,
        ]);

        $oldVehicle = Ambulance::create([
            'plate_number' => 'ABC-106',
            'vehicle_name' => 'Old Vehicle',
            'vehicle_type' => 'ambulance',
            'status' => Ambulance::STATUS_ON_DUTY,
        ]);
        $newVehicle = Ambulance::create([
            'plate_number' => 'ABC-107',
            'vehicle_name' => 'Available Vehicle',
            'vehicle_type' => 'rescue_van',
            'status' => Ambulance::STATUS_AVAILABLE,
        ]);
        $incident = Incident::create([
            'incident_number' => 'INC-0106',
            'reporter_name' => 'Driver Test',
            'contact_number' => '09120000006',
            'incident_type' => 'Medical',
            'location' => 'Test Location',
            'description' => 'Switch vehicle',
            'status' => Incident::STATUS_DISPATCHED,
            'driver_id' => $driver->id,
            'ambulance_id' => $oldVehicle->id,
        ]);
        $dispatch = Dispatch::create([
            'incident_id' => $incident->id,
            'driver_id' => $driver->id,
            'vehicle_id' => $oldVehicle->id,
            'status' => Dispatch::STATUS_ASSIGNED,
            'assigned_at' => now(),
        ]);

        $response = $this->actingAs($user)->post(route('driver.dispatch.accept', $dispatch), [
            'vehicle_id' => $newVehicle->id,
        ]);

        $response->assertSessionHas('success');
        $this->assertDatabaseHas('dispatches', [
            'id' => $dispatch->id,
            'vehicle_id' => $newVehicle->id,
            'status' => Dispatch::STATUS_ACCEPTED,
        ]);
        $this->assertDatabaseHas('ambulances', [
            'id' => $newVehicle->id,
            'status' => Ambulance::STATUS_ON_DUTY,
        ]);
        $this->assertDatabaseHas('ambulances', [
            'id' => $oldVehicle->id,
            'status' => Ambulance::STATUS_AVAILABLE,
        ]);
    }

    public function test_driver_cannot_accept_with_a_vehicle_that_became_busy(): void
    {
        $this->withoutMiddleware(PreventRequestForgery::class);

        Role::firstOrCreate(['name' => 'driver']);
        $user = User::factory()->create(['status' => 'approved']);
        $user->assignRole('driver');
        $driver = Driver::create([
            'user_id' => $user->id,
            'badge_id' => 'AMB-108',
            'contact_number' => '09123456796',
            'license_number' => 'LIC-108',
            'license_expiry' => '2030-01-01',
            'status' => Driver::STATUS_ASSIGNED,
        ]);
        $vehicle = Ambulance::create([
            'plate_number' => 'ABC-108',
            'vehicle_name' => 'Busy Vehicle',
            'vehicle_type' => 'ambulance',
            'status' => Ambulance::STATUS_ON_DUTY,
        ]);
        $otherDriver = Driver::create([
            'user_id' => User::factory()->create(['status' => 'approved'])->id,
            'badge_id' => 'AMB-109',
            'contact_number' => '09123456797',
            'license_number' => 'LIC-109',
            'license_expiry' => '2030-01-01',
            'status' => Driver::STATUS_ASSIGNED,
        ]);
        $incident = Incident::create([
            'incident_number' => 'INC-0108',
            'reporter_name' => 'Driver Test',
            'contact_number' => '09120000008',
            'incident_type' => 'Medical',
            'location' => 'Test Location',
            'description' => 'Busy vehicle',
            'status' => Incident::STATUS_DISPATCHED,
            'driver_id' => $driver->id,
        ]);
        $otherIncident = Incident::create([
            'incident_number' => 'INC-0109',
            'reporter_name' => 'Other Driver',
            'contact_number' => '09120000009',
            'incident_type' => 'Medical',
            'location' => 'Test Location',
            'description' => 'Busy vehicle owner',
            'status' => Incident::STATUS_DISPATCHED,
            'driver_id' => $otherDriver->id,
        ]);
        $dispatch = Dispatch::create([
            'incident_id' => $incident->id,
            'driver_id' => $driver->id,
            'status' => Dispatch::STATUS_ASSIGNED,
            'assigned_at' => now(),
        ]);
        Dispatch::create([
            'incident_id' => $otherIncident->id,
            'driver_id' => $otherDriver->id,
            'vehicle_id' => $vehicle->id,
            'status' => Dispatch::STATUS_ACCEPTED,
            'assigned_at' => now(),
        ]);

        $response = $this->actingAs($user)->post(route('driver.dispatch.accept', $dispatch), [
            'vehicle_id' => $vehicle->id,
        ]);

        $response->assertSessionHas('error', 'This vehicle is no longer available. Please select another vehicle.');
        $this->assertDatabaseHas('dispatches', [
            'id' => $dispatch->id,
            'status' => Dispatch::STATUS_ASSIGNED,
            'vehicle_id' => null,
        ]);
    }

    public function test_admin_report_approval_closes_the_incident_and_completes_the_dispatch(): void
    {
        $role = Role::firstOrCreate(['name' => 'admin']);
        $user = User::factory()->create(['status' => 'approved']);
        $user->assignRole($role);

        $driver = Driver::create([
            'user_id' => $user->id,
            'badge_id' => 'AMB-101',
            'contact_number' => '09123456790',
            'license_number' => 'LIC-101',
            'license_expiry' => '2030-01-01',
            'status' => 'available',
        ]);

        $ambulance = Ambulance::create([
            'plate_number' => 'ABC-101',
            'vehicle_name' => 'Ambulance Two',
            'vehicle_type' => 'ambulance',
            'status' => 'on_duty',
        ]);

        $incident = Incident::create([
            'incident_number' => 'INC-0101',
            'reporter_name' => 'John Doe',
            'contact_number' => '09120000001',
            'incident_type' => 'Traffic',
            'location' => 'Test Avenue',
            'description' => 'Sample incident',
            'status' => 'completed',
            'driver_id' => $driver->id,
            'ambulance_id' => $ambulance->id,
        ]);

        $dispatch = Dispatch::create([
            'incident_id' => $incident->id,
            'driver_id' => $driver->id,
            'vehicle_id' => $ambulance->id,
            'status' => Dispatch::STATUS_ARRIVED,
            'assigned_at' => now(),
            'arrived_at' => now(),
        ]);

        $report = IncidentReport::create([
            'incident_id' => $incident->id,
            'driver_id' => $driver->id,
            'summary' => 'Patient stabilized',
            'actions_taken' => 'Transported to hospital',
            'casualties' => 'None',
            'remarks' => 'Completed',
            'submitted_at' => now(),
            'status' => 'pending',
        ]);

        $response = $this->actingAs($user)->post(route('admin.reports.approve', $report));

        $response->assertSessionHas('success');
        $this->assertDatabaseHas('incident_reports', [
            'id' => $report->id,
            'status' => 'approved',
        ]);
        $this->assertDatabaseHas('incidents', [
            'id' => $incident->id,
            'status' => 'closed',
        ]);
        $this->assertDatabaseHas('dispatches', [
            'id' => $dispatch->id,
            'status' => Dispatch::STATUS_COMPLETED,
        ]);
    }
}
