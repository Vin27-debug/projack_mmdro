<?php

namespace App\Http\Controllers\Driver;

use App\Http\Controllers\Controller;
use App\Models\HijackAlert;
use App\Models\Notification;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class HijackController extends Controller
{
    public function trigger(Request $request)
    {
        $driver = Auth::user()?->driver;

        if (!$driver) {
            abort(403);
        }

        HijackAlert::create([
            'driver_id' => $driver->id,
            'latitude' => $request->input('latitude'),
            'longitude' => $request->input('longitude'),
            'status' => 'active',
            'triggered_at' => now(),
        ]);

        AuditLog::create([
            'user_id' => auth()->id(),
            'action' => 'Hijack Alert',
            'module' => 'Emergency',
            'description' => 'Vehicle hijack alert triggered',
            'ip_address' => request()->ip(),
        ]);

        Notification::create([
            'title' => 'Vehicle Hijack Alert',
            'message' => 'Possible vehicle hijacking detected.',
            'type' => 'hijack',
        ]);

        return response()->json([
            'success' => true
        ]);
    }
}
