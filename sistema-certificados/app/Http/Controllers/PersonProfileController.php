<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use App\Models\User;
use App\Models\Person;

class PersonProfileController extends Controller
{

    public function edit()
    {
        $person = Auth::user()->person;
        if (!$person) {

            abort(404, 'Perfil de persona no encontrado.');
        }
        return view('persona.profile', compact('person'));
    }

    public function update(Request $request)
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
        return redirect()->route('persona.profile.edit')->with('success', 'Perfil actualizado exitosamente.');
    }
}
