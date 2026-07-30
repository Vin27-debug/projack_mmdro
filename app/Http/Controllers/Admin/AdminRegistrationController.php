<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;


class AdminRegistrationController extends Controller
{
    public function create()
    {
        return view('admin.register');
    }

    public function store(Request $request)
    {

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:8|confirmed',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => bcrypt($request->password),
            'status' => 'pending',
        ]);

        $user->assignRole('admin');

        return redirect()
            ->back()
            ->with('success', 'Admin registration submitted for approval.');
    }
}
