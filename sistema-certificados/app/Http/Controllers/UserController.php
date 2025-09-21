<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use App\Models\Area;

class UserController extends Controller
{
    public function index()
    {
        $users = \App\Models\User::with('role')->latest()->paginate(10);

        return view('users.index', compact('users'));
    }

    public function create()
    {
        $roles = Role::all();
        $areas = Area::all();
        return view('users.create', compact('roles', 'areas'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'confirmed', \Illuminate\Validation\Rules\Password::defaults()],
            'role_id' => ['required', 'exists:roles,id'],
            'area_id' => ['nullable', 'exists:areas,id'],
        ]);


        $user = \App\Models\User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => \Illuminate\Support\Facades\Hash::make($request->password),
            'role_id' => $request->role_id,
            'area_id' => $request->area_id,
        ]);

        $role = \App\Models\Role::find($request->role_id);
        if ($role && $role->name === 'Persona') {
            \App\Models\Person::create([
                'user_id' => $user->id,
                'area_id' => $user->area_id,
                'dni' => 'PENDIENTE-' . $user->id,
                'apellido' => $user->name,
                'nombre' => '(completar)',
                'email' => $user->email,
                'titulo'   => 'N/A',
                'domicilio' => 'N/A',
                'telefono'  => 'N/A',
            ]);
        }

        return redirect()->route('users.index')->with('success', 'Usuario creado exitosamente.');
    }

    public function edit(User $user)
    {
        $roles = Role::all();
        $areas = Area::all();
        return view('users.edit', compact('user', 'roles', 'areas'));
    }

    public function update(Request $request, \App\Models\User $user)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email,' . $user->id],
            'role_id' => ['required', 'exists:roles,id'],
            'area_id' => ['nullable', 'exists:areas,id'],
            'password' => ['nullable', 'confirmed', \Illuminate\Validation\Rules\Password::defaults()],
        ]);

        $data = $request->only('name', 'email', 'role_id', 'area_id');
        if ($request->filled('password')) {
            $data['password'] = \Illuminate\Support\Facades\Hash::make($request->password);
        }

        $role = Role::find($request->role_id);
        if (!in_array($role->name, ['Administrador', 'Persona'])) {
            $data['area_id'] = null;
        }

        $user->update($data);

        return redirect()->route('users.index')->with('success', 'Usuario actualizado exitosamente.');
    }

    public function destroy(User $user)
    {
        $user->delete();
        return redirect()->route('users.index')->with('success', 'Usuario eliminado exitosamente.');
    }
}
