<?php

namespace App\Http\Controllers\Driver;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class DriverSettingsController extends Controller
{
    public function index()
    {
        $driver = Auth::user()?->driver;

        abort_if(!$driver, 403, 'Driver profile not found.');

        return view('driver.settings', compact('driver'));
    }
}
