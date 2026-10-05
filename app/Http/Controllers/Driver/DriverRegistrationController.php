<?php

namespace App\Http\Controllers\Driver;

use App\Http\Controllers\Controller;
use App\Models\Driver;
use App\Models\User;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;

class DriverRegistrationController extends Controller
{
    public function create()
    {
        return view('driver.register');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:8|confirmed',
            'contact_number' => 'required',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => bcrypt($request->password),
            'status' => 'approved',
        ]);

        $role = Role::firstOrCreate([
            'name' => 'driver',
            'guard_name' => 'web',
        ]);

        $user->assignRole($role);

        Driver::create([
            'user_id' => $user->id,
            'badge_id' => 'PENDING',
            'contact_number' => $request->contact_number,
        ]);

        return redirect()
            ->back()
            ->with('success', 'Driver account created successfully. You can now log in.');
    }
}