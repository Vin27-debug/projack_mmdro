<?php

namespace App\Http\Controllers\Driver;

use App\Http\Controllers\Controller;
use App\Models\Dispatch;
use Illuminate\Support\Facades\Auth;

class DriverHistoryController extends Controller
{
    public function index()
    {
        $driver = Auth::user()?->driver;

        if (!$driver) {
            abort(403, 'Driver profile not found.');
        }

        $dispatches = Dispatch::with(['incident', 'vehicle'])
            ->where('driver_id', $driver->id)
            ->latest('assigned_at')
            ->get();

        return view('driver.history', compact('dispatches'));
    }
}
