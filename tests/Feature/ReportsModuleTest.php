<?php

namespace Tests\Feature;

use App\Models\Ambulance;
use App\Models\Dispatch;
use App\Models\Driver;
use App\Models\Incident;
use App\Models\IncidentReport;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ReportsModuleTest extends TestCase
{
    use RefreshDatabase;

    public function test_driver_performance_report_includes_dispatch_metrics(): void
    {
        $role = Role::firstOrCreate(['name' => 'admin']);
        $admin = User::factory()->create(['status' => 'approved']);
        $admin->assignRole($role);

        $driverUser = User::factory()->create(['status' => 'approved']);
        $driver = Driver::create([
            'user_id' => $driverUser->id,
            'badge_id' => 'DRV-001',
            'contact_number' => '09123456789',
            'license_number' => 'LIC-001',
            'license_expiry' => '2030-01-01',
            'status' => 'available',
        ]);

        $ambulance = Ambulance::create([
            'plate_number' => 'ABC-123',
            'vehicle_name' => 'Ambulance One',
            'vehicle_type' => 'ambulance',
            'status' => 'available',
        ]);

        $incident = Incident::create([
            'incident_number' => 'INC-100',
            'reporter_name' => 'Jane Doe',
            'contact_number' => '09120000000',
            'incident_type' => 'Medical',
            'location' => 'Test Street',
            'description' => 'Sample incident',
            'status' => 'pending',
            'driver_id' => $driver->id,
            'ambulance_id' => $ambulance->id,
        ]);

        Dispatch::create([
            'incident_id' => $incident->id,
            'driver_id' => $driver->id,
            'vehicle_id' => $ambulance->id,
            'status' => Dispatch::STATUS_COMPLETED,
            'assigned_at' => now()->subMinutes(10),
            'accepted_at' => now()->subMinutes(8),
            'completed_at' => now()->subMinutes(2),
        ]);

        IncidentReport::create([
            'incident_id' => $incident->id,
            'driver_id' => $driver->id,
            'summary' => 'Patient stabilized',
            'actions_taken' => 'Transported to hospital',
            'casualties' => 'None',
            'remarks' => 'Completed',
            'submitted_at' => now(),
            'status' => 'approved',
        ]);

        $response = $this->actingAs($admin)->get(route('admin.reports.driver-performance'));

        $response->assertOk();
        $response->assertViewHas('drivers', function ($drivers) use ($driver): bool {
            $entry = $drivers->firstWhere('id', $driver->id);

            return $entry
                && $entry->total_dispatches === 1
                && $entry->completed_dispatches === 1
                && $entry->incidents_handled === 1
                && $entry->acceptance_rate >= 0;
        });
    }

    public function test_driver_can_submit_incident_report_and_persist_it(): void
    {
        $driverRole = Role::firstOrCreate(['name' => 'driver']);
        $driverUser = User::factory()->create(['status' => 'approved']);
        $driverUser->assignRole($driverRole);

        $driver = Driver::create([
            'user_id' => $driverUser->id,
            'badge_id' => 'DRV-003',
            'contact_number' => '09123456791',
            'license_number' => 'LIC-003',
            'license_expiry' => '2030-01-01',
            'status' => 'available',
        ]);

        $ambulance = Ambulance::create([
            'plate_number' => 'ABC-999',
            'vehicle_name' => 'Ambulance Three',
            'vehicle_type' => 'ambulance',
            'status' => 'available',
        ]);

        $incident = Incident::create([
            'incident_number' => 'INC-102',
            'reporter_name' => 'Alice Doe',
            'contact_number' => '09120000002',
            'incident_type' => 'Medical',
            'location' => 'Sample Road',
            'description' => 'Sample incident',
            'status' => Incident::STATUS_COMPLETED,
            'driver_id' => $driver->id,
            'ambulance_id' => $ambulance->id,
        ]);

        Dispatch::create([
            'incident_id' => $incident->id,
            'driver_id' => $driver->id,
            'vehicle_id' => $ambulance->id,
            'status' => Dispatch::STATUS_COMPLETED,
            'completed_at' => now(),
        ]);

        $response = $this->actingAs($driverUser)->post(route('driver.report.store', $incident), [
            'summary' => 'Patient stabilized',
            'actions_taken' => 'Transported to hospital',
            'casualties' => 'None',
            'remarks' => 'Completed',
        ]);

        $response->assertRedirect(route('driver.dashboard'));
        $response->assertSessionHas('success', 'Incident report submitted successfully.');
        $this->assertDatabaseHas('incident_reports', [
            'incident_id' => $incident->id,
            'driver_id' => $driver->id,
            'summary' => 'Patient stabilized',
            'actions_taken' => 'Transported to hospital',
            'casualties' => 'None',
            'remarks' => 'Completed',
            'status' => 'pending',
        ]);
    }

    public function test_vehicle_utilization_report_includes_utilization_metrics(): void
    {
        $role = Role::firstOrCreate(['name' => 'admin']);
        $admin = User::factory()->create(['status' => 'approved']);
        $admin->assignRole($role);

        $ambulance = Ambulance::create([
            'plate_number' => 'XYZ-789',
            'vehicle_name' => 'Ambulance Two',
            'vehicle_type' => 'ambulance',
            'status' => 'available',
        ]);

        $driverUser = User::factory()->create(['status' => 'approved']);
        $driver = Driver::create([
            'user_id' => $driverUser->id,
            'badge_id' => 'DRV-002',
            'contact_number' => '09123456790',
            'license_number' => 'LIC-002',
            'license_expiry' => '2030-01-01',
            'status' => 'available',
        ]);

        $incident = Incident::create([
            'incident_number' => 'INC-101',
            'reporter_name' => 'John Doe',
            'contact_number' => '09120000001',
            'incident_type' => 'Traffic',
            'location' => 'Sample Avenue',
            'description' => 'Sample incident',
            'status' => 'pending',
            'driver_id' => $driver->id,
            'ambulance_id' => $ambulance->id,
        ]);

        Dispatch::create([
            'incident_id' => $incident->id,
            'driver_id' => $driver->id,
            'vehicle_id' => $ambulance->id,
            'status' => Dispatch::STATUS_COMPLETED,
            'assigned_at' => now()->subMinutes(15),
            'accepted_at' => now()->subMinutes(10),
            'completed_at' => now()->subMinutes(2),
        ]);

        $response = $this->actingAs($admin)->get(route('admin.reports.vehicle-utilization'));

        $response->assertOk();
        $response->assertViewHas('vehicles', function ($vehicles) use ($ambulance): bool {
            $entry = $vehicles->firstWhere('id', $ambulance->id);

            return $entry
                && $entry->usage_count === 1
                && $entry->total_dispatches === 1
                && $entry->maintenance_count === 0
                && $entry->availability_rate >= 0;
        });
    }

    public function test_admin_incident_reports_support_search_status_filters_and_pagination(): void
    {
        $admin = $this->createAdmin();
        $driverUser = User::factory()->create(['status' => 'approved']);
        $driver = $this->createDriver($driverUser, 'DRV-RPT-001');

        for ($index = 1; $index <= 17; $index++) {
            $incident = $this->createIncident($driver, 'INC-RPT-' . $index);
            IncidentReport::create([
                'incident_id' => $incident->id,
                'driver_id' => $driver->id,
                'summary' => $index === 17 ? 'Target report summary' : 'Routine report ' . $index,
                'actions_taken' => 'Actions for report ' . $index,
                'status' => 'pending',
                'submitted_at' => now()->subMinutes($index),
            ]);
        }

        $searchResponse = $this->actingAs($admin)->get(route('admin.reports.index', [
            'search' => 'Target report',
            'status' => 'pending',
        ]));

        $searchResponse->assertOk()
            ->assertSee('Target report summary')
            ->assertSee('INC-RPT-17')
            ->assertViewHas('reports', fn($reports) => $reports->total() === 1);

        $firstPage = $this->get(route('admin.reports.index', ['status' => 'pending']));
        $firstPage->assertOk()
            ->assertSee('Showing 1–15 of 17 reports')
            ->assertViewHas('reports', fn($reports) => $reports->count() === 15);

        $secondPage = $this->get(route('admin.reports.index', ['status' => 'pending', 'page' => 2]));
        $secondPage->assertOk()
            ->assertViewHas('reports', fn($reports) => $reports->count() === 2 && $reports->currentPage() === 2);
    }

    public function test_admin_can_approve_a_pending_incident_report_without_changing_incident_report_workflow(): void
    {
        $admin = $this->createAdmin();
        $driverUser = User::factory()->create(['status' => 'approved']);
        $driver = $this->createDriver($driverUser, 'DRV-RPT-APPROVE');
        $ambulance = Ambulance::create([
            'plate_number' => 'RPT-001',
            'vehicle_name' => 'Report Test Ambulance',
            'vehicle_type' => 'ambulance',
            'status' => 'on_duty',
        ]);
        $incident = $this->createIncident($driver, 'INC-RPT-APPROVE', $ambulance->id);
        $incident->update(['status' => Incident::STATUS_COMPLETED]);

        $report = IncidentReport::create([
            'incident_id' => $incident->id,
            'driver_id' => $driver->id,
            'summary' => 'Report ready for review',
            'actions_taken' => 'Patient transported',
            'submitted_at' => now(),
        ]);

        $this->assertSame('pending', $report->fresh()->status);

        $response = $this->actingAs($admin)
            ->from(route('admin.reports.index'))
            ->post(route('admin.reports.approve', $report));

        $response->assertRedirect(route('admin.reports.index'))
            ->assertSessionHas('success', 'Report approved.');

        $this->assertDatabaseHas('incident_reports', [
            'id' => $report->id,
            'status' => 'approved',
        ]);
        $this->assertDatabaseHas('incidents', [
            'id' => $incident->id,
            'status' => Incident::STATUS_CLOSED,
        ]);
        $this->assertDatabaseHas('ambulances', [
            'id' => $ambulance->id,
            'status' => 'available',
        ]);
        $this->assertDatabaseHas('notifications', [
            'user_id' => $driverUser->id,
            'type' => 'report',
        ]);
    }

    private function createAdmin(): User
    {
        $role = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        $admin = User::factory()->create(['status' => 'approved']);
        $admin->assignRole($role);

        return $admin;
    }

    private function createDriver(User $user, string $badgeId): Driver
    {
        return Driver::create([
            'user_id' => $user->id,
            'badge_id' => $badgeId,
            'contact_number' => '09123456789',
            'license_number' => 'LIC-' . $badgeId,
            'license_expiry' => '2030-01-01',
            'status' => 'assigned',
        ]);
    }

    private function createIncident(Driver $driver, string $incidentNumber, ?int $ambulanceId = null): Incident
    {
        return Incident::create([
            'incident_number' => $incidentNumber,
            'reporter_name' => 'Report Test',
            'contact_number' => '09120000000',
            'incident_type' => 'Medical',
            'location' => 'Test location',
            'description' => 'Incident for report feature tests',
            'status' => Incident::STATUS_COMPLETED,
            'driver_id' => $driver->id,
            'ambulance_id' => $ambulanceId,
        ]);
    }
}
