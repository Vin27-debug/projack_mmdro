<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Ambulance;
use App\Models\Driver;
use App\Models\Dispatch;
use App\Models\User;
use App\Models\VehicleDriverAssignment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
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

        $ambulance = Ambulance::notArchived()->whereIn('status', ['available', 'on_duty'])
            ->orderBy('id')
            ->first();

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
        $archived = request()->boolean('archived');
        $drivers = Driver::query()
            ->when($archived, fn($query) => $query->archived(), fn($query) => $query->notArchived())
            ->with([
            'user',
            'activeVehicleAssignment.ambulance'
        ])
            ->orderBy('id', 'desc')
            ->get();

        return view(
            'superadmin.drivers.index',
            compact('drivers', 'archived')
        );
    }

    public function updateDriverStatus(Request $request, Driver $driver)
    {
        abort_if($driver->archived_at, 404);
        abort_unless($driver->user?->status === 'approved', 403, 'Approve the driver account before changing its operational status.');

        $validated = $request->validate([
            'status' => ['required', 'in:active,suspended'],
        ]);

        $driver->update([
            'management_status' => $validated['status'],
        ]);

        if (
            $validated['status'] === 'active'
            && $driver->status === Driver::STATUS_OFFLINE
            && !Dispatch::active()->where('driver_id', $driver->id)->exists()
        ) {
            $driver->update(['status' => Driver::STATUS_AVAILABLE]);
        }

        return back()->with('success', 'Driver status updated.');
    }

    public function archiveDriver(Driver $driver)
    {
        abort_if($driver->archived_at, 404);

        if (Dispatch::active()->where('driver_id', $driver->id)->exists()) {
            return back()->with('error', 'A driver with an active dispatch cannot be archived.');
        }

        DB::transaction(function () use ($driver): void {
            $driver->update([
                'archived_at' => now(),
                'archived_by' => Auth::id(),
                'management_status' => Driver::MANAGEMENT_STATUS_SUSPENDED,
            ]);
        });

        return redirect()->route('superadmin.drivers')->with('success', 'Driver archived. Historical records remain available.');
    }

    public function restoreDriver(Driver $driver)
    {
        abort_if(!$driver->archived_at, 404);

        $driver->update([
            'archived_at' => null,
            'archived_by' => null,
        ]);

        return redirect()->route('superadmin.drivers')->with('success', 'Driver restored. Set the driver status to Active when ready for dispatch.');
    }
    public function assignForm(Driver $driver)
    {
        abort_if($driver->archived_at, 404);
        abort_unless(
            Driver::activeAccount()->whereKey($driver->id)->exists(),
            403,
            'Only active drivers can be assigned a vehicle.'
        );
        $vehicles = Ambulance::notArchived()->whereIn('status', [
            Ambulance::STATUS_AVAILABLE,
            Ambulance::STATUS_ON_DUTY,
        ])
            ->orderBy('vehicle_type')
            ->orderBy('vehicle_name')
            ->get();

        $currentAssignment = $driver->activeVehicleAssignment()
            ->with('ambulance')
            ->first();

        return view(
            'superadmin.drivers.assign',
            compact(
                'driver',
                'vehicles',
                'currentAssignment'
            )
        );
    }
    public function assignVehicle(Request $request, Driver $driver)
    {
        abort_if($driver->archived_at, 404);
        abort_unless(
            Driver::activeAccount()->whereKey($driver->id)->exists(),
            403,
            'Only active drivers can be assigned a vehicle.'
        );

        $request->validate([
            'ambulance_id' => 'required|exists:ambulances,id',
        ]);

        $ambulance = Ambulance::notArchived()->findOrFail(
            $request->ambulance_id
        );

        VehicleDriverAssignment::assignDriverToAmbulance(
            $driver,
            $ambulance
        );

        return redirect()
            ->route('superadmin.drivers')
            ->with(
                'success',
                'Vehicle assigned to driver successfully.'
            );
    }
}
