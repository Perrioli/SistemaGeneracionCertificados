<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Area;


class AreaController extends Controller
{
    public function index()
    {
        $areas = Area::latest()->paginate(10);
        return view('areas.index', compact('areas'));
    }

    public function create()
    {
        return view('areas.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nombre' => 'required|string|unique:areas,nombre|max:255',
            'descripcion' => 'nullable|string',
        ]);

        Area::create($data);

        return redirect()->route('areas.index')->with('success', 'Área creada exitosamente.');
    }

    public function edit(Area $area)
    {
        return view('areas.edit', compact('area'));
    }

    public function update(Request $request, Area $area)
{
    $data = $request->validate([
        'nombre' => 'required|string|max:255|unique:areas,nombre,' . $area->id,
        'descripcion' => 'nullable|string',
        'template_front' => 'nullable|string',
        'template_back' => 'nullable|string', 
    ]);

    $area->update($data);

    return redirect()->route('areas.index')->with('success', 'Área actualizada exitosamente.');
}

    public function destroy(Area $area)
    {
        // Verificamos si el área tiene cursos asociados.
        if ($area->courses()->count() > 0) {
            return redirect()->route('areas.index')
                ->with('error', 'No se puede eliminar esta área porque está siendo utilizada por uno o más cursos.');
        }


        $area->delete();

        return redirect()->route('areas.index')
            ->with('success', 'Área eliminada exitosamente.');
    }
}
