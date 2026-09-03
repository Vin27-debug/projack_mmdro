<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class AdminController extends Controller
{
    public function index(Request $request)
    {
        $admins = User::role('admin')
            ->with(['approvedBy', 'createdBy'])
            ->when($request->filled('search'), function ($query) use ($request): void {
                $search = trim($request->input('search'));
                $query->where(function ($query) use ($search): void {
                    $query->where('name', 'like', "%{$search}%")
                        ->orWhere('employee_id', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                });
            })
            ->when($request->filled('status'), fn($query) => $query->where('status', $request->input('status')))
            ->latest()
            ->get();

        return view('superadmin.admin.index', compact('admins'));
    }

    public function create()
    {
        return view('superadmin.admin.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'employee_id' => 'required|string|max:50|unique:users,employee_id',
            'position' => 'required|string|max:255',
            'department' => 'required|string|max:255',
            'office' => 'required|string|max:255',
            'contact_number' => 'required|string|max:20',
            'email' => 'required|email|max:255|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $admin = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),

            'employee_id' => $validated['employee_id'],
            'position' => $validated['position'],
            'department' => $validated['department'],
            'office' => $validated['office'],
            'contact_number' => $validated['contact_number'],

            'status' => 'pending',

            'created_by' => auth()->id(),
        ]);

        $admin->assignRole('admin');

        return redirect()
            ->route('admins.index')
            ->with('success', 'Administrator account created and is pending approval.');
    }

    public function show(User $user)
    {
        $this->ensureAdminAccount($user);
        $user->load(['approvedBy', 'createdBy']);

        return view('superadmin.admin.show', ['admin' => $user]);
    }

    public function edit(User $user)
    {
        $this->ensureAdminAccount($user);

        return view('superadmin.admin.edit', ['admin' => $user]);
    }

    public function update(Request $request, User $user)
    {
        $this->ensureAdminAccount($user);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'employee_id' => ['required', 'string', 'max:50', Rule::unique('users', 'employee_id')->ignore($user->id)],
            'position' => 'required|string|max:255',
            'department' => 'required|string|max:255',
            'office' => 'required|string|max:255',
            'contact_number' => 'required|string|max:20',
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
        ]);

        $user->update($validated);

        return redirect()->route('admins.show', $user)->with('success', 'Administrator account updated successfully.');
    }

    public function approve(User $user)
    {
        $this->ensureAdminAccount($user);
        $user->update(['status' => 'approved', 'approved_by' => auth()->id(), 'approved_at' => now()]);

        return back()->with('success', 'Administrator account approved.');
    }

    public function reject(User $user)
    {
        $this->ensureAdminAccount($user);
        $user->update(['status' => 'rejected', 'approved_by' => auth()->id(), 'approved_at' => now()]);

        return back()->with('success', 'Administrator account rejected.');
    }

    public function suspend(User $user)
    {
        $this->ensureAdminAccount($user);
        $user->update(['status' => 'suspended']);

        return back()->with('success', 'Administrator account suspended.');
    }

    protected function ensureAdminAccount(User $user): void
    {
        abort_unless($user->hasRole('admin'), 404);
    }
}
