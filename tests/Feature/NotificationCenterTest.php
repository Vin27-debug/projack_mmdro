<?php

namespace Tests\Feature;

use App\Models\Ambulance;
use App\Models\Dispatch;
use App\Models\Driver;
use App\Models\Incident;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class NotificationCenterTest extends TestCase
{
    use RefreshDatabase;

    public function test_incident_creation_creates_a_notification_and_mark_all_read_updates_it(): void
    {
        $this->withoutMiddleware(PreventRequestForgery::class);
        $admin = $this->createAdmin();

        $this->actingAs($admin);

        $response = $this->post(route('admin.incidents.store'), [
            'reporter_name' => 'Test Reporter',
            'contact_number' => '09170001111',
            'incident_type' => 'Medical',
            'location' => 'Test Location',
            'description' => 'Test description',
            'priority' => 'Medium',
            'latitude' => '14.1234567',
            'longitude' => '121.1234567',
        ]);

        $response->assertRedirect(route('admin.incidents.show', $incident = Incident::latest()->firstOrFail()));

        $notification = Notification::where('type', 'incident')->latest()->first();

        $this->assertNotNull($incident);
        $this->assertNotNull($notification);
        $this->assertSame('New Incident Reported', $notification->title);
        $this->assertStringContainsString($incident->incident_number, $notification->message);
        $this->assertFalse($notification->is_read);

        $markAllReadResponse = $this->post(route('admin.notifications.read-all'));

        $markAllReadResponse->assertRedirect();
        $this->assertTrue((bool) $notification->fresh()->is_read);
    }

    public function test_loading_notification_list_does_not_mark_notifications_read(): void
    {
        $admin = $this->createAdmin();
        $notification = Notification::create([
            'title' => 'Unread Alert',
            'message' => 'Still unread',
            'type' => 'incident',
            'is_read' => false,
        ]);

        $response = $this->actingAs($admin)->get(route('admin.notifications.index'));

        $response->assertOk()->assertSee('Unread Alert')->assertSee('Still unread');
        $this->assertFalse((bool) $notification->fresh()->is_read);
    }

    public function test_driver_report_submission_creates_incident_report_submitted_notification_and_links_it_to_the_incident(): void
    {
        $driverRole = Role::firstOrCreate(['name' => 'driver']);
        $driverUser = User::factory()->create(['status' => 'approved']);
        $driverUser->assignRole($driverRole);

        $driver = Driver::create([
            'user_id' => $driverUser->id,
            'badge_id' => 'DRV-404',
            'contact_number' => '09123456789',
            'license_number' => 'LIC-404',
            'license_expiry' => '2030-01-01',
            'status' => 'available',
        ]);

        $ambulance = Ambulance::create([
            'plate_number' => 'ABC-404',
            'vehicle_name' => 'Ambulance Four',
            'vehicle_type' => 'ambulance',
            'status' => 'available',
        ]);

        $incident = Incident::create([
            'incident_number' => 'INC-404',
            'reporter_name' => 'Test Reporter',
            'contact_number' => '09170001111',
            'incident_type' => 'Medical Emergency',
            'location' => 'Test Location',
            'description' => 'Test description',
            'priority' => 'Medium',
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

        $this->actingAs($driverUser)->post(route('driver.report.store', $incident), [
            'summary' => 'Patient stabilized',
            'actions_taken' => 'Transported to hospital',
            'casualties' => 'None',
            'remarks' => 'Completed',
        ])->assertRedirect(route('driver.dashboard'));

        $notification = Notification::where('type', 'report')->latest()->first();

        $this->assertNotNull($notification);
        $this->assertSame('Incident Report Submitted', $notification->title);
        $this->assertSame($incident->id, $notification->related_id);
        $this->assertSame(Incident::class, $notification->related_type);
        $this->assertStringNotContainsString('New Incident Report', $notification->title);

        $this->actingAs($this->createAdmin())
            ->get(route('admin.notifications.show', $notification))
            ->assertOk()
            ->assertSee('View Incident');
    }

    public function test_opening_one_notification_marks_only_that_notification_read_and_preserves_records(): void
    {
        $this->withoutMiddleware(PreventRequestForgery::class);
        $admin = $this->createAdmin();
        $opened = Notification::create([
            'title' => 'Opened Alert',
            'message' => 'Open this one',
            'type' => 'incident',
            'is_read' => false,
        ]);
        $other = Notification::create([
            'title' => 'Other Alert',
            'message' => 'Leave this unread',
            'type' => 'panic',
            'is_read' => false,
        ]);

        $response = $this->actingAs($admin)->post(route('admin.notifications.open', $opened));

        $response->assertRedirect(route('admin.notifications.show', $opened));
        $this->assertTrue((bool) $opened->fresh()->is_read);
        $this->assertFalse((bool) $other->fresh()->is_read);
        $this->assertDatabaseHas('notifications', ['id' => $opened->id]);
        $this->assertDatabaseHas('notifications', ['id' => $other->id]);
    }

    public function test_notification_detail_view_displays_the_selected_message_and_status(): void
    {
        $admin = $this->createAdmin();
        $notification = Notification::create([
            'title' => 'Vehicle Selected for Dispatch',
            'message' => 'Driver selected vehicle for incident INC-003.',
            'type' => 'dispatch',
            'is_read' => false,
        ]);

        $this->actingAs($admin)
            ->get(route('admin.notifications.show', $notification))
            ->assertOk()
            ->assertSee('Notification Details')
            ->assertSee('Vehicle Selected for Dispatch')
            ->assertSee('Driver selected vehicle for incident INC-003.')
            ->assertSee('Read');

        $this->assertTrue((bool) $notification->fresh()->is_read);
    }

    public function test_mark_read_marks_only_the_selected_notification(): void
    {
        $this->withoutMiddleware(PreventRequestForgery::class);
        $admin = $this->createAdmin();
        $selected = Notification::create([
            'title' => 'Selected Alert',
            'message' => 'Read this one',
            'type' => 'report',
            'is_read' => false,
        ]);
        $other = Notification::create([
            'title' => 'Unread Alert',
            'message' => 'Keep unread',
            'type' => 'maintenance',
            'is_read' => false,
        ]);

        $this->actingAs($admin)->post(route('admin.notifications.read', $selected))->assertRedirect();

        $this->assertTrue((bool) $selected->fresh()->is_read);
        $this->assertFalse((bool) $other->fresh()->is_read);
    }

    public function test_read_all_marks_only_unread_notifications_and_keeps_every_record(): void
    {
        $this->withoutMiddleware(PreventRequestForgery::class);
        $admin = $this->createAdmin();
        $unread = Notification::create([
            'title' => 'Unread Alert',
            'message' => 'Read all this',
            'type' => 'incident',
            'is_read' => false,
        ]);
        $alreadyRead = Notification::create([
            'title' => 'Read Alert',
            'message' => 'Already read',
            'type' => 'report',
            'is_read' => true,
        ]);
        $alreadyReadUpdatedAt = $alreadyRead->updated_at;

        $this->actingAs($admin)->post(route('admin.notifications.read-all'))->assertRedirect();

        $this->assertTrue((bool) $unread->fresh()->is_read);
        $this->assertTrue((bool) $alreadyRead->fresh()->is_read);
        $this->assertTrue($alreadyReadUpdatedAt->equalTo($alreadyRead->fresh()->updated_at));
        $this->assertDatabaseHas('notifications', ['id' => $unread->id]);
        $this->assertDatabaseHas('notifications', ['id' => $alreadyRead->id]);
    }

    public function test_private_notification_cannot_be_opened_or_marked_by_another_admin(): void
    {
        $this->withoutMiddleware(PreventRequestForgery::class);
        $admin = $this->createAdmin();
        $owner = $this->createAdmin();
        $notification = Notification::create([
            'user_id' => $owner->id,
            'title' => 'Private Alert',
            'message' => 'Private notification',
            'type' => 'incident',
            'is_read' => false,
        ]);

        $this->actingAs($admin)->post(route('admin.notifications.open', $notification))->assertForbidden();
        $this->actingAs($admin)->post(route('admin.notifications.read', $notification))->assertForbidden();

        $this->assertFalse((bool) $notification->fresh()->is_read);
    }

    public function test_shared_global_notification_can_be_opened_by_an_admin(): void
    {
        $this->withoutMiddleware(PreventRequestForgery::class);
        $admin = $this->createAdmin();
        $notification = Notification::create([
            'user_id' => null,
            'title' => 'Shared Alert',
            'message' => 'Global notification',
            'type' => 'hijack',
            'is_read' => false,
        ]);

        $this->actingAs($admin)
            ->post(route('admin.notifications.open', $notification))
            ->assertRedirect(route('admin.notifications.show', $notification));

        $this->assertTrue((bool) $notification->fresh()->is_read);
    }

    public function test_read_notifications_remain_visible_in_the_notification_list(): void
    {
        $admin = $this->createAdmin();
        $notification = Notification::create([
            'title' => 'Read Alert',
            'message' => 'This remains visible',
            'type' => 'report',
            'is_read' => true,
        ]);

        $response = $this->actingAs($admin)->get(route('admin.notifications.index'));

        $response->assertOk()
            ->assertSee('Read Alert')
            ->assertSee('This remains visible')
            ->assertSee('Read');
        $this->assertDatabaseHas('notifications', ['id' => $notification->id]);
    }

    public function test_opening_a_read_notification_keeps_it_read_without_changing_it(): void
    {
        $this->withoutMiddleware(PreventRequestForgery::class);
        $admin = $this->createAdmin();
        $notification = Notification::create([
            'title' => 'Already Read Alert',
            'message' => 'Open without changing state',
            'type' => 'report',
            'is_read' => true,
        ]);
        $updatedAt = $notification->updated_at;

        $response = $this->actingAs($admin)
            ->post(route('admin.notifications.open', $notification));

        $response->assertRedirect(route('admin.notifications.show', $notification));
        $notification = $notification->fresh();
        $this->assertTrue((bool) $notification->is_read);
        $this->assertTrue($updatedAt->equalTo($notification->updated_at));
    }

    public function test_admin_can_update_an_emergency_timestamp_and_log_the_correction(): void
    {
        $this->withoutMiddleware(PreventRequestForgery::class);
        $admin = $this->createAdmin();
        $incident = Incident::create([
            'incident_number' => 'INC-100',
            'reporter_name' => 'Test Reporter',
            'contact_number' => '09170001111',
            'incident_type' => 'Medical Emergency',
            'location' => 'Test Location',
            'description' => 'Test description',
            'priority' => 'Medium',
            'status' => Incident::STATUS_DISPATCHED,
            'call_received_at' => now()->subHours(2),
            'response_at' => now()->subHour(),
            'at_scene_at' => now()->subMinutes(45),
            'at_patient_at' => null,
            'depart_scene_at' => null,
            'at_hospital_at' => null,
        ]);

        $oldAtScene = $incident->at_scene_at->format('Y-m-d H:i:s');
        $newAtScene = now()->setMinute(47)->setSecond(0)->format('Y-m-d H:i:s');

        $this->actingAs($admin)
            ->post(route('admin.incidents.timestamp.update', ['incident' => $incident, 'field' => 'at_scene_at']), [
                'timestamp' => $newAtScene,
            ])
            ->assertRedirect(route('admin.incidents.show', $incident));

        $this->assertNotNull($incident->fresh()->at_scene_at);
        $this->assertSame($newAtScene, $incident->fresh()->at_scene_at->format('Y-m-d H:i:s'));
        $this->assertDatabaseHas('audit_logs', ['module' => 'Emergency Time Record']);
    }

    private function createAdmin(): User
    {
        Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);

        $admin = User::factory()->create([
            'status' => 'approved',
        ]);
        $admin->assignRole('admin');

        return $admin;
    }
}
