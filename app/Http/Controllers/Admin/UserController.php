<?php

namespace App\Http\Controllers\Admin;

use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    public function index()
    {
        $users = User::with('roles')
            ->latest()
            ->paginate(10);

        return view(
            'admin.users.index',
            compact('users')
        );
    }

    public function create()
    {
        $roles = Role::all();

        return view(
            'admin.users.create',
            compact('roles')
        );
    }

    public function store(Request $request)
    {
        $request->validate([

            'name' => 'required',

            'email' => 'required|email|unique:users',

            'password' => 'required|min:6',

            'role' => 'required',

        ]);

        $user = User::create([

            'name' => $request->name,

            'email' => $request->email,

            'password' => Hash::make(
                $request->password
            ),

        ]);

        $user->assignRole($request->role);

        return redirect()
            ->route('users.index')
            ->with(
                'success',
                'User berhasil dibuat'
            );
    }

    public function edit(User $user)
    {
        $roles = Role::all();

        return view(
            'admin.users.edit',
            compact(
                'user',
                'roles'
            )
        );
    }

    public function update(Request $request, User $user)
    {
        $request->validate([

            'name' => 'required',

            'email' => 'required|email',

            'role' => 'required',

        ]);

        $data = [

            'name' => $request->name,

            'email' => $request->email,

        ];

        if ($request->password) {

            $data['password'] = Hash::make(
                $request->password
            );
        }

        $user->update($data);

        $user->syncRoles([
            $request->role
        ]);

        return redirect()
            ->route('users.index')
            ->with(
                'success',
                'User berhasil diupdate'
            );
    }

    public function destroy(User $user)
    {
        $user->delete();

        return back()->with(
            'success',
            'User berhasil dihapus'
        );
    }
}
