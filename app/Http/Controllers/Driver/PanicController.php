<?php

namespace App\Http\Controllers\Driver;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use App\Models\PanicAlert;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PanicController extends Controller
{
    public function trigger(Request $request)
    {
        $driver = Auth::user()?->driver;

        if (!$driver) {
            abort(403);
        }

        PanicAlert::create([
            'driver_id' => $driver->id,
            'latitude' => $request->input('latitude'),
            'longitude' => $request->input('longitude'),
            'status' => 'active',
            'triggered_at' => now(),
        ]);

        Notification::create([
            'title' => 'Panic Alert',
            'message' => 'A driver triggered the panic button.',
            'type' => 'panic'
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Panic alert sent.'
        ]);
    }
}
