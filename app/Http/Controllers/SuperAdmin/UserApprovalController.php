<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Ambulance;
use App\Models\Driver;
use App\Models\Dispatch;
use App\Models\VehicleDriverAssignment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class UserApprovalController extends Controller
{
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
