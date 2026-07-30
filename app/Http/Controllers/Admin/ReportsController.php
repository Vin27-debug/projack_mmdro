<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\IncidentReport;

class ReportsController extends Controller
{
    public function index()
    {
        $reports = IncidentReport::with([
            'incident',
            'driver.user'
        ])
            ->latest()
            ->get();

        return view(
            'admin.reports-center',
            compact('reports')
        );
    }
}
