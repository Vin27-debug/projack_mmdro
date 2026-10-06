<?php

namespace Tests\Feature;

use App\Models\Ambulance;
use App\Models\Incident;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class DataPersistenceTest extends TestCase
{
    use RefreshDatabase;

    public function test_super_admin_created_ambulance_is_persisted_and_visible_after_a_new_request(): void
    {
        Role::firstOrCreate(['name' => 'super-admin', 'guard_name' => 'web']);
        $superAdmin = User::factory()->create(['status' => 'approved']);
        $superAdmin->assignRole('super-admin');

        $this->actingAs($superAdmin)
            ->post(route('superadmin.ambulances.store'), [
                'plate_number' => 'PERSIST-001',
                'vehicle_name' => 'Persistence Test Ambulance',
                'vehicle_type' => 'ambulance',
            ])
            ->assertRedirect(route('superadmin.ambulances.index'))
            ->assertSessionHas('success');

        $ambulance = Ambulance::where('plate_number', 'PERSIST-001')->firstOrFail();
        $this->assertSame(Ambulance::STATUS_AVAILABLE, $ambulance->status);

        $this->get(route('superadmin.ambulances.index'))
            ->assertOk()
            ->assertSee('PERSIST-001')
            ->assertSee('Persistence Test Ambulance');
    }

    public function test_driver_cannot_create_ambulance_or_delete_operational_records(): void
    {
        Role::firstOrCreate(['name' => 'driver', 'guard_name' => 'web']);
        $driver = User::factory()->create(['status' => 'approved']);
        $driver->assignRole('driver');
        $incident = Incident::create([
            'incident_number' => 'INC-PERSIST-LOCKED',
            'reporter_name' => 'Protected Incident',
            'incident_type' => 'Medical Emergency',
            'location' => 'Protected Street',
            'description' => 'Unauthorized modification must not succeed',
            'status' => Incident::STATUS_PENDING,
        ]);

        $this->actingAs($driver)
            ->post(route('superadmin.ambulances.store'), [
                'plate_number' => 'UNAUTHORIZED-001',
                'vehicle_name' => 'Must Not Be Created',
                'vehicle_type' => 'ambulance',
            ])
            ->assertForbidden();

        $this->assertDatabaseMissing('ambulances', [
            'plate_number' => 'UNAUTHORIZED-001',
        ]);

        $this->post(route('admin.incidents.archive', $incident))
            ->assertForbidden();

        $this->assertDatabaseHas('incidents', [
            'id' => $incident->id,
            'archived_at' => null,
        ]);
    }
}
