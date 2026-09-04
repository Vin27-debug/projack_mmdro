<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PanicAlert;

class PanicAlertController extends Controller
{
    public function resolve(PanicAlert $panicAlert)
    {
        $panicAlert->update([
            'resolved' => true,
        ]);

        return back()->with(
            'success',
            'Panic alert resolved.'
        );
    }

    public function index()
    {
        $alerts = \App\Models\PanicAlert::with(
            'driver.user'
        )
            ->latest()
            ->get();

        return view(
            'admin.panic-alerts',
            compact('alerts')
        );
    }
}
