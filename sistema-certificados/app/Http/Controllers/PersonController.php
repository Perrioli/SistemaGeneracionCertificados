<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Person;
use Illuminate\Validation\Rule;
use App\Imports\PersonsImport;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use App\Models\Area;
use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class PersonController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $user = Auth::user();
        $query = Person::query();

        if ($user->role?->name === 'Administrador' && $user->area_id) {
            $query->where('area_id', $user->area_id);
        }

        $people = $query->latest()->paginate(10);

        return view('persons.index', compact('people'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $user = Auth::user();
        $areas = Area::all();

        // Si el usuario es Administrador pasamos su propia área.
        if ($user->role?->name === 'Administrador') {
            $areas = Area::where('id', $user->area_id)->get();
        }

        return view('persons.create', compact('areas'));
    }


    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'dni' => 'required|string|unique:persons,dni',
            'apellido' => 'required|string|max:255',
            'nombre' => 'required|string|max:255',
            'titulo' => 'required|string|max:255',
            'domicilio' => 'required|string|max:255',
            'telefono' => 'required|string|max:255',
            'email' => 'required|email|unique:persons,email|unique:users,email',
            'area_id' => 'required|exists:areas,id',
        ]);

        $person = Person::create($data);

        $personaRole = Role::where('name', 'Persona')->first();

        if ($personaRole) {
            $user = User::create([
                'name'     => $person->nombre . ' ' . $person->apellido,
                'email'    => $person->email,
                'password' => Hash::make($person->dni), // El DNI como contraseña por defecto
                'role_id'  => $personaRole->id,
                'area_id'  => $person->area_id,
            ]);

            $person->user_id = $user->id;
            $person->save();
        }

        return redirect()->route('persons.index')->with('success', 'Persona y cuenta de usuario creadas exitosamente.');
    }


    /**
     * Display the specified resource.
     */
    public function showImportForm()
    {
        return view('persons.import');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Person $person)
    {
        $areas = Area::all();
        return view('persons.edit', compact('person', 'areas'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function updateProfile(Request $request)
    {
        $person = Auth::user()->person;
        $data = $request->validate([
            'dni' => 'required|string|unique:persons,dni,' . $person->id,
            'apellido' => 'required|string|max:255',
            'nombre' => 'required|string|max:255',
            'titulo' => 'required|string|max:255',
            'domicilio' => 'required|string|max:255',
            'telefono' => 'required|string|max:255',
        ]);
        $person->update($data);
        return redirect()->route('profile.edit')->with('success', 'Datos personales actualizados.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Person $person)
    {
        if ($person->certificates()->count() > 0) {
            return redirect()->route('persons.index')
                ->with('error', 'No se puede eliminar esta persona porque tiene certificados asociados.');
        }
        $person->delete();
        return redirect()->route('persons.index')->with('success', 'Persona eliminada exitosamente.');
    }

    public function import(Request $request)
    {
        $request->validate(['excel_file' => 'required|mimes:xlsx,xls']);

        try {
            Excel::import(new PersonsImport, $request->file('excel_file'));

            return redirect()->route('persons.index')
                ->with('success', 'La importación de personas se ha realizado exitosamente.');
        } catch (\Maatwebsite\Excel\Validators\ValidationException $e) {
            return back()->withErrors(['excel_file' => 'Hubo un error en la validación de algunas filas. Asegúrate de que no haya DNI o emails duplicados.']);
        }
    }

    public function update(Request $request, Person $person)
    {
        $data = $request->validate([
            'dni' => 'required|string|unique:persons,dni,' . $person->id,
            'apellido' => 'required|string|max:255',
            'nombre' => 'required|string|max:255',
            'titulo' => 'required|string|max:255',
            'domicilio' => 'required|string|max:255',
            'telefono' => 'required|string|max:255',
            'email' => 'required|email|unique:persons,email,' . $person->id,
            'area_id' => 'required|exists:areas,id',
        ]);

        $person->update($data);

        return redirect()->route('persons.index')->with('success', 'Persona actualizada exitosamente.');
    }
}
