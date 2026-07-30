<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\GpsLocation;
use App\Models\Incident;

class GpsMonitoringController extends Controller
{
    public function index()
    {
        $incident = \App\Models\Incident::with([
            'driver.user',
            'ambulance'
        ])->latest()->first();

        return view(
            'admin.gps-monitoring',
            compact('incident')
        );
    }

    public function locations()
    {
        $locations = GpsLocation::with(
            'driver.user'
        )
            ->latest()
            ->paginate(50);

        return response()->json(
            GpsLocation::with('driver.user')
                ->latest()
                ->take(100)
                ->get()
        );
    }

    public function history()
    {
        $locations = \App\Models\GpsLocation::with(
            'driver.user'
        )
            ->latest()
            ->paginate(100);

        return view(
            'admin.gps-history',
            compact('locations')
        );
    }
}
