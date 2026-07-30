<?php

namespace App\Http\Controllers\Driver;

use App\Http\Controllers\Controller;
use App\Models\Dispatch;
use Illuminate\Support\Facades\Auth;

class NavigationController extends Controller
{
    public function show(?Dispatch $dispatch = null)
    {
        $driver = Auth::user()?->driver;

        if (!$dispatch && $driver) {
            $dispatch = Dispatch::with(['incident', 'vehicle'])
                ->where('driver_id', $driver->id)
                ->inProgress()
                ->latest()
                ->first();
        }

        if (!$dispatch || !$dispatch->incident) {
            return view('driver.navigation', ['dispatch' => null]);
        }

        return view('driver.navigation', compact('dispatch'));
    }
}
