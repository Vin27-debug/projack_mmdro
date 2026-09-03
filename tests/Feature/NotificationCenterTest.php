<?php

namespace Tests\Feature;

use App\Models\Incident;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class NotificationCenterTest extends TestCase
{
    use RefreshDatabase;

    public function test_incident_creation_creates_a_notification_and_mark_all_read_updates_it(): void
    {
        Role::create(['name' => 'admin', 'guard_name' => 'web']);

        $admin = User::factory()->create([
            'status' => 'approved',
        ]);
        $admin->assignRole('admin');

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
        $this->assertSame(0, $notification->is_read);

        $markAllReadResponse = $this->post(route('admin.notifications.read-all'));

        $markAllReadResponse->assertRedirect();
        $this->assertTrue((bool) $notification->fresh()->is_read);
    }
}
