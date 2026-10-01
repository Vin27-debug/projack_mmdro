<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\ViewErrorBag;
use Tests\TestCase;

class DriverLayoutTest extends TestCase
{
    public function test_profile_page_uses_driver_layout(): void
    {
        $user = new User([
            'name' => 'Test Driver',
            'email' => 'driver@example.com',
        ]);

        $view = view('profile.edit', [
            'user' => $user,
            'errors' => new ViewErrorBag(),
        ])->render();

        $this->assertStringContainsString('Dashboard', $view);
        $this->assertStringContainsString('My Assignment', $view);
        $this->assertStringContainsString('Navigation', $view);
        $this->assertStringContainsString('Reports', $view);
        $this->assertStringContainsString('Profile', $view);
    }

    public function test_driver_dashboard_renders_without_blade_errors(): void
    {
        $driver = new \stdClass();
        $driver->user = new \stdClass();
        $driver->user->name = 'Test Driver';
        $driver->status = 'available';

        $view = view('driver.dashboard', [
            'driver' => $driver,
            'currentDispatch' => null,
            'incidents' => new Collection(),
        ])->render();

        $this->assertStringContainsString('Driver Operations Center', $view);
    }

    public function test_driver_navigation_does_not_use_hardcoded_fallback_coordinates_for_missing_mission(): void
    {
        $dispatch = new \stdClass();
        $dispatch->incident = new \stdClass();
        $dispatch->incident->incident_code = 'INC-005';
        $dispatch->incident->address = null;
        $dispatch->incident->latitude = null;
        $dispatch->incident->longitude = null;

        $view = view('driver.navigation', [
            'dispatch' => $dispatch,
        ])->render();

        $this->assertStringNotContainsString('15.5000', $view);
        $this->assertStringNotContainsString('120.8500', $view);
        $this->assertStringNotContainsString('alvarez st., Poblacion East, Rizal, Nueva Ecija', $view);
        $this->assertStringContainsString('No valid incident coordinates are available', $view);
    }
}
