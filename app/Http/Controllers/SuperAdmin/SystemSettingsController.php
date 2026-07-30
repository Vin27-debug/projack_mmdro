<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class SystemSettingsController extends Controller
{
    public function index()
    {
        return view('superadmin.settings');
    }

    public function update(Request $request)
    {
        return back()->with(
            'success',
            'Settings updated successfully.'
        );
    }
}
