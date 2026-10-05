<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Driver;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class DriverController extends Controller
{
    public function create()
    {
        return view('superadmin.drivers.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
            'contact_number' => 'nullable|string|max:30',
        ]);

        // Create user account and leave it pending until a super-admin approves it.
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'status' => 'pending',
            'created_by' => auth()->id(),
        ]);

        // Make sure driver role exists
        $role = Role::firstOrCreate([
            'name' => 'driver',
            'guard_name' => 'web',
        ]);

        $user->syncRoles([$role]);

        // Create driver profile
        $user->driver()->create([
            'badge_id' => $this->generateBadgeId(),
            'contact_number' => $request->contact_number,
            'status' => 'available',
        ]);

        return redirect()
            ->route('superadmin.drivers')
            ->with('success', 'Driver account created successfully and is pending approval.');
    }

    protected function generateBadgeId(): string
    {
        $nextId = Driver::whereNotNull('badge_id')
            ->where('badge_id', '!=', 'PENDING')
            ->count() + 1;

        return 'AMB-' . str_pad($nextId, 3, '0', STR_PAD_LEFT);
    }
}
