<?php

namespace App\Http\Controllers\Driver;

use App\Http\Controllers\Controller;
use App\Models\Driver;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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

        $role = Role::firstOrCreate([
            'name' => 'driver',
            'guard_name' => 'web',
        ]);

        DB::transaction(function () use ($request, $role): void {
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => bcrypt($request->password),
                'status' => 'approved',
            ]);

            $user->assignRole($role);

            $badgeId = 'AMB-REG-' . $user->id;
            $suffix = 1;
            while (Driver::where('badge_id', $badgeId)->exists()) {
                $badgeId = 'AMB-REG-' . $user->id . '-' . $suffix++;
            }

            $user->driver()->create([
                'badge_id' => $badgeId,
                'contact_number' => $request->contact_number,
            ]);
        });

        return redirect()
            ->route('login')
            ->with('status', 'Driver account created successfully. You can now log in.');
    }
}