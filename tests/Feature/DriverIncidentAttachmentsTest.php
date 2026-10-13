<?php

namespace Tests\Feature;

use App\Models\Driver;
use App\Models\Incident;
use App\Models\IncidentAttachment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class DriverIncidentAttachmentsTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_create_incident_form_does_not_offer_attachments(): void
    {
        $admin = $this->createUserWithRole('admin');

        $this->actingAs($admin)
            ->get(route('admin.incidents.create'))
            ->assertOk()
            ->assertDontSee('Photos / Documents')
            ->assertDontSee('name="attachments[]"', false)
            ->assertSee('Save Incident');
    }

    public function test_driver_can_submit_valid_attachments_with_an_authorized_incident_report(): void
    {
        Storage::fake('local');
        [$user, $driver] = $this->createDriver();
        $admin = $this->createUserWithRole('admin');
        $incident = $this->createCompletedIncident($driver);
        $files = [];
        for ($index = 1; $index <= 10; $index++) {
            $files[] = $this->fakePng("scene-{$index}.png");
        }

        $response = $this->actingAs($user)->post(route('driver.report.store', $incident), [
            'summary' => 'Patient stabilized',
            'actions_taken' => 'Transported to hospital',
            'casualties' => 'None',
            'remarks' => 'Evidence attached',
            'attachments' => $files,
        ]);

        $response->assertRedirect(route('driver.dashboard'))
            ->assertSessionHas('success', 'Incident report and 10 attachments submitted successfully.');

        $attachments = IncidentAttachment::query()->get();
        $this->assertCount(10, $attachments);
        $attachment = $attachments->firstWhere('original_name', 'scene-1.png');
        $this->assertSame($incident->id, $attachment->incident_id);
        $this->assertSame($user->id, $attachment->uploaded_by);
        foreach ($attachments as $storedAttachment) {
            Storage::disk('local')->assertExists($storedAttachment->path);
        }

        $this->actingAs($admin)
            ->get(route('admin.incidents.attachments.download', [$incident, $attachment]))
            ->assertDownload('scene-1.png');

        $this->assertDatabaseHas('incident_reports', [
            'incident_id' => $incident->id,
            'driver_id' => $driver->id,
            'summary' => 'Patient stabilized',
        ]);
    }

    public function test_driver_report_screen_displays_attachment_validation_errors(): void
    {
        [$user, $driver] = $this->createDriver();
        $incident = $this->createCompletedIncident($driver);

        $this->actingAs($user)
            ->get(route('driver.report.create', $incident))
            ->assertOk()
            ->assertSee('name="attachments[]"', false)
            ->assertSee('10 MB each');
    }

    public function test_driver_attachments_reject_invalid_file_type(): void
    {
        $this->assertAttachmentSubmissionRejected([
            UploadedFile::fake()->create('evidence.txt', 20, 'text/plain'),
        ], 'attachments.0');
    }

    public function test_driver_attachments_reject_files_over_10_mb(): void
    {
        $this->assertAttachmentSubmissionRejected([
            UploadedFile::fake()->create('large.jpg', 10241, 'image/jpeg'),
        ], 'attachments.0');
    }

    public function test_driver_attachments_reject_more_than_ten_files(): void
    {
        $files = [];
        for ($index = 1; $index <= 11; $index++) {
            $files[] = $this->fakePng("evidence-{$index}.png");
        }

        $this->assertAttachmentSubmissionRejected($files, 'attachments');
    }

    public function test_driver_cannot_attach_files_to_an_incident_owned_by_another_driver(): void
    {
        Storage::fake('local');
        [$user] = $this->createDriver();
        [, $otherDriver] = $this->createDriver();
        $incident = $this->createCompletedIncident($otherDriver);

        $this->actingAs($user)
            ->post(route('driver.report.store', $incident), [
                'summary' => 'Unauthorized report',
                'actions_taken' => 'None',
                'casualties' => 'None',
                'attachments' => [$this->fakePng('unauthorized.png')],
            ])
            ->assertForbidden();

        $this->assertDatabaseMissing('incident_reports', ['incident_id' => $incident->id]);
        $this->assertDatabaseMissing('incident_attachments', ['incident_id' => $incident->id]);
        Storage::disk('local')->assertDirectoryEmpty('incident-attachments');
    }

    public function test_attachment_download_requires_authorization_and_the_matching_incident(): void
    {
        Storage::fake('local');
        [$driverUser, $driver] = $this->createDriver();
        $admin = $this->createUserWithRole('admin');
        $incident = $this->createCompletedIncident($driver);
        $otherIncident = Incident::create([
            'incident_number' => 'INC-ATTACH-OTHER-' . $driver->id,
            'reporter_name' => 'Other incident',
            'incident_type' => 'Medical Emergency',
            'location' => 'Other location',
            'status' => Incident::STATUS_COMPLETED,
        ]);
        $path = 'incident-attachments/' . $incident->id . '/evidence.pdf';
        Storage::disk('local')->put($path, 'private evidence');
        $originalName = '<script>alert(1).pdf';
        $attachment = IncidentAttachment::create([
            'incident_id' => $incident->id,
            'uploaded_by' => $driverUser->id,
            'disk' => 'local',
            'path' => $path,
            'original_name' => $originalName,
            'mime_type' => 'application/pdf',
            'size' => 15,
        ]);

        $downloadUrl = route('admin.incidents.attachments.download', [$incident, $attachment]);
        $this->assertSame(storage_path('app/private'), config('filesystems.disks.local.root'));
        $this->get($downloadUrl)->assertRedirect(route('login'));
        $this->get(route('storage.local', ['path' => $path]))->assertForbidden();
        $this->actingAs($driverUser)->get($downloadUrl)->assertForbidden();
        $this->actingAs($admin)
            ->get(route('admin.incidents.attachments.download', [$otherIncident, $attachment]))
            ->assertNotFound();

        $this->get(route('admin.incidents.show', $incident))
            ->assertOk()
            ->assertSee(e($originalName), false)
            ->assertDontSee($originalName, false)
            ->assertSee($downloadUrl, false);

        $this->get($downloadUrl)->assertDownload('_script_alert(1).pdf');
    }

    public function test_attachment_download_rejects_missing_files_and_untrusted_disk_or_path_values(): void
    {
        Storage::fake('local');
        $admin = $this->createUserWithRole('admin');
        [$driverUser, $driver] = $this->createDriver();
        $incident = $this->createCompletedIncident($driver);

        $missingAttachment = IncidentAttachment::create([
            'incident_id' => $incident->id,
            'uploaded_by' => $driverUser->id,
            'disk' => 'local',
            'path' => 'incident-attachments/' . $incident->id . '/missing.pdf',
            'original_name' => 'missing.pdf',
            'mime_type' => 'application/pdf',
            'size' => 0,
        ]);
        $untrustedDiskAttachment = IncidentAttachment::create([
            'incident_id' => $incident->id,
            'uploaded_by' => $driverUser->id,
            'disk' => 'public',
            'path' => '../../.env',
            'original_name' => 'secret.txt',
            'mime_type' => 'text/plain',
            'size' => 0,
        ]);
        $traversalAttachment = IncidentAttachment::create([
            'incident_id' => $incident->id,
            'uploaded_by' => $driverUser->id,
            'disk' => 'local',
            'path' => 'incident-attachments/' . $incident->id . '/../.env',
            'original_name' => 'secret.txt',
            'mime_type' => 'text/plain',
            'size' => 0,
        ]);

        $this->actingAs($admin)
            ->get(route('admin.incidents.attachments.download', [$incident, $missingAttachment]))
            ->assertNotFound();
        $this->get(route('admin.incidents.attachments.download', [$incident, $untrustedDiskAttachment]))
            ->assertNotFound();
        $this->get(route('admin.incidents.attachments.download', [$incident, $traversalAttachment]))
            ->assertNotFound();
    }

    private function assertAttachmentSubmissionRejected(array $files, string $errorKey): void
    {
        [$user, $driver] = $this->createDriver();
        $incident = $this->createCompletedIncident($driver);

        $this->actingAs($user)
            ->from(route('driver.report.create', $incident))
            ->post(route('driver.report.store', $incident), [
                'summary' => 'Patient stabilized',
                'actions_taken' => 'Transported to hospital',
                'casualties' => 'None',
                'attachments' => $files,
            ])
            ->assertRedirect(route('driver.report.create', $incident))
            ->assertSessionHasErrors($errorKey);

        $this->assertDatabaseMissing('incident_reports', ['incident_id' => $incident->id]);
        $this->assertDatabaseMissing('incident_attachments', ['incident_id' => $incident->id]);
    }

    private function fakePng(string $filename): UploadedFile
    {
        $image = imagecreatetruecolor(10, 10);

        ob_start();
        imagepng($image);
        $content = ob_get_clean();
        imagedestroy($image);

        return UploadedFile::fake()->createWithContent($filename, $content);
    }

    private function createDriver(): array
    {
        Role::firstOrCreate(['name' => 'driver', 'guard_name' => 'web']);
        $user = User::factory()->create(['status' => 'approved']);
        $user->assignRole('driver');
        $driver = Driver::create([
            'user_id' => $user->id,
            'badge_id' => 'ATTACH-' . $user->id,
            'contact_number' => '09123456789',
            'status' => Driver::STATUS_AVAILABLE,
        ]);

        return [$user, $driver];
    }

    private function createCompletedIncident(Driver $driver): Incident
    {
        return Incident::create([
            'incident_number' => 'INC-ATTACH-' . $driver->id,
            'reporter_name' => 'Attachment workflow test',
            'incident_type' => 'Medical Emergency',
            'location' => 'Test location',
            'status' => Incident::STATUS_COMPLETED,
            'driver_id' => $driver->id,
        ]);
    }

    private function createUserWithRole(string $role): User
    {
        Role::firstOrCreate(['name' => $role, 'guard_name' => 'web']);
        $user = User::factory()->create(['status' => 'approved']);
        $user->assignRole($role);

        return $user;
    }
}
