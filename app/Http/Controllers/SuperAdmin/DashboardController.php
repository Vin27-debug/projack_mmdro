<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Ambulance;
use App\Models\Driver;
use App\Models\Incident;
use App\Models\PanicAlert;

class DashboardController extends Controller
{
    public function index()
    {
        $stats = [
            'total_incidents' => Incident::count(),
            'pending_incidents' => Incident::where('status', 'pending')->count(),
            'dispatched_incidents' => Incident::where('status', 'dispatched')->count(),
            'completed_incidents' => Incident::where('status', 'completed')->count(),
            'total_drivers' => Driver::count(),
            'available_ambulances' => Ambulance::where('status', 'available')->count(),
        ];

        $recentIncidents = Incident::latest()->take(5)->get();

        $activePanicAlerts = PanicAlert::with('driver.user')
            ->where('status', 'active')
            ->latest()
            ->get();

        return view(
            'superadmin.dashboard',
            compact(
                'stats',
                'recentIncidents',
                'activePanicAlerts'
            )
        );
    }
}
