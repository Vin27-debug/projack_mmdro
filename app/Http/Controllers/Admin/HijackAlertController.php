<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\HijackAlert;

class HijackAlertController extends Controller
{
    public function index()
    {
        $alerts = HijackAlert::with(
            'driver.user'
        )
            ->latest()
            ->get();

        return view(
            'admin.hijack-alerts',
            compact('alerts')
        );
    }
}
