<?php

namespace Tests\Feature;

use App\Models\Driver;
use App\Models\GpsLocation;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\PersonalAccessToken;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class MobileGpsApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_active_driver_can_get_a_scoped_token_and_submit_a_timestamped_gps_fix(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-10-15 12:00:00'));
        [$driverUser, $driver] = $this->createDriver();

        $login = $this->postJson('/api/mobile/login', [
            'driver_id' => $driver->badge_id,
            'password' => 'driver-password',
        ])->assertOk()
            ->assertJsonPath('token_type', 'Bearer')
            ->assertJsonPath('driver.id', $driver->id);

        $token = $login->json('access_token');
        $recordedAt = now()->subSeconds(20);

        $this->withToken($token)
            ->postJson('/api/mobile/driver/gps/update', [
                'latitude' => 15.5,
                'longitude' => 120.9,
                'accuracy' => 8.5,
                'recorded_at' => $recordedAt->toISOString(),
            ])
            ->assertOk()
            ->assertJsonPath('driver_id', $driver->id)
            ->assertJsonPath('latitude', 15.5)
            ->assertJsonPath('longitude', 120.9);

        $location = GpsLocation::query()->sole();
        $this->assertSame($driver->id, $location->driver_id);
        $this->assertTrue($location->recorded_at->equalTo($recordedAt));

        $admin = User::factory()->create(['status' => 'approved']);
        $admin->assignRole(Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']));
        $mapDrivers = $this->actingAs($admin)
            ->getJson(route('admin.dashboard.live-command-map'))
            ->assertOk()
            ->json('drivers');

        $mapDriver = collect($mapDrivers)->firstWhere('id', $driver->id);
        $this->assertSame('fresh', $mapDriver['gps_status']);
        $this->assertSame(15.5, $mapDriver['latitude']);
        $this->assertSame(120.9, $mapDriver['longitude']);
    }

    public function test_mobile_gps_endpoint_rejects_missing_and_out_of_range_coordinates(): void
    {
        [$driverUser, $driver] = $this->createDriver();
        $token = $this->loginAs($driverUser, $driver);

        $this->withToken($token)
            ->postJson('/api/mobile/driver/gps/update', [
                'latitude' => 91,
                'longitude' => 120.9,
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['latitude']);

        $this->withToken($token)
            ->postJson('/api/mobile/driver/gps/update', [
                'latitude' => 15.5,
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['longitude']);

        $this->assertSame(0, $driver->gpsLocations()->count());
    }

    public function test_mobile_gps_endpoint_rejects_future_timestamps(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-10-15 12:00:00'));
        [$driverUser, $driver] = $this->createDriver();
        $token = $this->loginAs($driverUser, $driver);

        $this->withToken($token)
            ->postJson('/api/mobile/driver/gps/update', [
                'latitude' => 15.5,
                'longitude' => 120.9,
                'recorded_at' => now()->addSecond()->toISOString(),
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['recorded_at']);

        $this->assertSame(0, $driver->gpsLocations()->count());
    }

    public function test_mobile_gps_endpoint_requires_authentication_and_an_active_driver_role(): void
    {
        $this->postJson('/api/mobile/driver/gps/update', [
            'latitude' => 15.5,
            'longitude' => 120.9,
        ])->assertUnauthorized();

        [$driverUser, $driver] = $this->createDriver();
        $driverUser->syncRoles([Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web'])]);

        $this->postJson('/api/mobile/login', [
            'driver_id' => $driver->badge_id,
            'password' => 'driver-password',
        ])->assertUnprocessable();
    }

    public function test_mobile_login_rejects_wrong_credentials_and_inactive_driver_accounts(): void
    {
        [$driverUser, $driver] = $this->createDriver();

        $this->postJson('/api/mobile/login', [
            'driver_id' => $driver->badge_id,
            'password' => 'wrong-password',
        ])->assertUnprocessable();

        $driver->update(['management_status' => Driver::MANAGEMENT_STATUS_SUSPENDED]);
        $this->postJson('/api/mobile/login', [
            'driver_id' => $driver->badge_id,
            'password' => 'driver-password',
        ])->assertUnprocessable();
    }

    public function test_mobile_api_rejects_a_driver_suspended_after_token_issuance(): void
    {
        [$driverUser, $driver] = $this->createDriver();
        $token = $this->loginAs($driverUser, $driver);
        $driver->update(['management_status' => Driver::MANAGEMENT_STATUS_SUSPENDED]);

        $this->withToken($token)
            ->postJson('/api/mobile/driver/gps/update', [
                'latitude' => 15.5,
                'longitude' => 120.9,
            ])
            ->assertForbidden();
    }

    public function test_mobile_logout_revokes_the_current_bearer_token(): void
    {
        [$driverUser, $driver] = $this->createDriver();
        $token = $this->loginAs($driverUser, $driver);

        $this->withToken($token)
            ->postJson('/api/mobile/logout')
            ->assertOk();

        $tokenId = (int) explode('|', $token, 2)[0];
        $this->assertDatabaseMissing('personal_access_tokens', ['id' => $tokenId]);

        $this->app['auth']->forgetGuards();
        $this->withToken($token)
            ->postJson('/api/mobile/driver/gps/update', [
                'latitude' => 15.5,
                'longitude' => 120.9,
            ])
            ->assertUnauthorized();
    }

    public function test_mobile_api_rejects_expired_tokens(): void
    {
        config(['sanctum.expiration' => 1]);
        [$driverUser, $driver] = $this->createDriver();
        $token = $this->loginAs($driverUser, $driver);
        $tokenId = (int) explode('|', $token, 2)[0];

        PersonalAccessToken::query()
            ->whereKey($tokenId)
            ->update(['created_at' => now()->subMinutes(2)]);

        $this->withToken($token)
            ->postJson('/api/mobile/driver/gps/update', [
                'latitude' => 15.5,
                'longitude' => 120.9,
            ])
            ->assertUnauthorized();
    }

    private function createDriver(): array
    {
        $driverRole = Role::firstOrCreate(['name' => 'driver', 'guard_name' => 'web']);
        $user = User::factory()->create([
            'status' => 'approved',
            'password' => Hash::make('driver-password'),
        ]);
        $user->assignRole($driverRole);

        $driver = Driver::create([
            'user_id' => $user->id,
            'badge_id' => 'MOBILE-' . $user->id,
            'contact_number' => '09123456789',
            'license_number' => 'MOBILE-LICENSE-' . $user->id,
            'license_expiry' => '2030-01-01',
            'status' => Driver::STATUS_AVAILABLE,
        ]);

        return [$user, $driver];
    }

    private function loginAs(User $user, Driver $driver): string
    {
        $response = $this->postJson('/api/mobile/login', [
            'driver_id' => $driver->badge_id,
            'password' => 'driver-password',
        ])->assertOk();

        return $response->json('access_token');
    }
}
