<?php

namespace App\Http\Controllers\Driver;

use App\Http\Controllers\Controller;
use App\Models\Dispatch;
use Illuminate\Support\Facades\Auth;

class MyAssignmentController extends Controller
{
    public function index()
    {
        $driver = Auth::user()?->driver;

        if (!$driver) {
            abort(403, 'Driver record not found');
        }

        $dispatch = Dispatch::with([
            'incident',
            'vehicle'
        ])
            ->where('driver_id', $driver->id)
            ->whereIn('status', [
                Dispatch::STATUS_PENDING,
                Dispatch::STATUS_ASSIGNED,
                Dispatch::STATUS_ACCEPTED,
                Dispatch::STATUS_EN_ROUTE,
                Dispatch::STATUS_ARRIVED,
            ])
            ->latest('assigned_at')
            ->first();

        $incident = $dispatch?->incident;

        return view(
            'driver.assignment.index',
            compact('dispatch', 'incident')
        );
    }
}
