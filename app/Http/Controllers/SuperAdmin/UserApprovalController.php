<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Ambulance;
use App\Models\Driver;
use App\Models\User;
use App\Models\VehicleDriverAssignment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Spatie\Permission\Models\Role;

class UserApprovalController extends Controller
{
    public function index()
    {
        $pendingUsers = User::where('status', 'pending')
            ->with('driver')
            ->orderBy('created_at', 'desc')
            ->get();

        return view('superadmin.users.pending', compact('pendingUsers'));
    }

    public function approve(User $user)
    {
        $user->update([
            'status' => 'approved',
            'approved_by' => Auth::id(),
            'approved_at' => now(),
        ]);

        $role = Role::firstOrCreate([
            'name' => 'driver',
            'guard_name' => 'web',
        ]);

        $driver = $user->driver()->first();

        if (!$driver) {
            $driver = $user->driver()->create([
                'badge_id' => $this->generateBadgeId(),
                'contact_number' => null,
                'license_number' => null,
                'license_expiry' => null,
                'status' => 'available',
            ]);
        }

        $driver->update([
            'badge_id' => blank($driver->badge_id) || $driver->badge_id === 'PENDING'
                ? $this->generateBadgeId()
                : $driver->badge_id,
            'status' => 'available',
        ]);

        $this->ensureVehicleAssignment($driver);

        $user->syncRoles([$role->name]);

        return back()->with('success', 'User approved successfully.');
    }

    public function reject(User $user)
    {
        $user->update([
            'status' => 'rejected',
            'approved_by' => Auth::id(),
            'approved_at' => now(),
        ]);

        if ($user->driver()->exists()) {
            $user->driver()->update([
                'status' => 'offline',
            ]);
        }

        $user->syncRoles([]);

        return back()->with('success', 'User rejected successfully.');
    }

    protected function ensureVehicleAssignment(Driver $driver): void
    {
        if ($driver->activeVehicleAssignment()->exists()) {
            return;
        }

        $ambulance = Ambulance::whereIn('status', ['available', 'on_duty'])
            ->orderBy('id')
            ->first();

        if (!$ambulance) {
            $ambulance = Ambulance::query()->orderBy('id')->first();
        }

        if ($ambulance) {
            VehicleDriverAssignment::assignDriverToAmbulance($driver, $ambulance);
        }
    }

    protected function generateBadgeId(): string
    {
        $nextId = Driver::whereNotNull('badge_id')
            ->where('badge_id', '!=', 'PENDING')
            ->count() + 1;

        return 'AMB-' . str_pad($nextId, 3, '0', STR_PAD_LEFT);
    }

    public function drivers()
    {
        $drivers = Driver::with('user')
            ->orderBy('id', 'desc')
            ->get();

        return view(
            'superadmin.drivers.index',
            compact('drivers')
        );
    }
}
