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

class PersonController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $people = Person::latest()->paginate(10);
        return view('persons.index', compact('people'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $areas = Area::all();
        return view('persons.create', compact('areas'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'dni' => ['required', 'string', Rule::unique('persons')->whereNull('deleted_at')],
            'apellido' => 'required|string|max:255',
            'nombre' => 'required|string|max:255',
            'titulo' => 'required|string|max:255',
            'domicilio' => 'required|string|max:255',
            'telefono' => 'required|string|max:255',
            'email' => ['required', 'email', Rule::unique('persons')->whereNull('deleted_at')],
            'area_id' => 'required|exists:areas,id',
        ]);

        Person::create($data);

        return redirect()->route('persons.index')
            ->with('success', 'Persona creada exitosamente.');
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

        return redirect()->route('persons.index')
            ->with('success', 'Persona eliminada exitosamente.');
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

        return redirect()->route('persons.index')
            ->with('success', 'Persona actualizada exitosamente.');
    }
}
