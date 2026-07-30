<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Incident;

class IncidentHistoryController extends Controller
{
    public function index()
    {
        $history = Incident::with([
            'driver.user',
            'ambulance'
        ])
            ->where('status', 'completed')
            ->latest()
            ->get();

        return view(
            'admin.incidents.history',
            compact('history')
        );
    }
}
