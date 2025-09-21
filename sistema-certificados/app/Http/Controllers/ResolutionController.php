<?php

namespace App\Http\Controllers;

use App\Models\Resolution;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\Area;


class ResolutionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $user = Auth::user();
        $query = Resolution::query();

        if ($user->role?->name === 'Administrador' && $user->area_id) {
            $query->where('area_id', $user->area_id);
        }

        $resolutions = $query->latest()->paginate(10);
        return view('resolutions.index', compact('resolutions'));
    }

    public function create()
    {
        $areas = Area::all();
        return view('resolutions.create', compact('areas'));
    }

    public function store(Request $request)
    {
        // Validar los datos recibidos
        $data = $request->validate([
            'numero' => [
                'required',
                'string',
                'max:255',
                Rule::unique('resolutions')->where(function ($query) use ($request) {
                    return $query->where('anio', $request->anio)
                        ->whereNull('deleted_at');
                }),
            ],
            'anio' => 'required|integer|digits:4|min:1901|max:2155',
            'area_id' => 'required|exists:areas,id',
            'pdf_file' => 'required|file|mimes:pdf',
        ]);
        $filePath = $request->file('pdf_file')->store('resolutions', 'public');
        $area = Area::findOrFail($request->area_id);
        Resolution::create([
            'numero' => $request->numero,
            'anio' => $request->anio,
            'area_id' => $area->id,
            'area' => $area->nombre,
            'pdf_path' => $filePath,
        ]);
        return redirect()->route('resolutions.index')
            ->with('success', 'Resolución creada exitosamente.');
    }


    public function edit(Resolution $resolution)
    {
        $areas = Area::all();
        return view('resolutions.edit', compact('resolution', 'areas'));
    }

    public function update(Request $request, Resolution $resolution)
    {
        $data = $request->validate([
            'numero' => [
                'required',
                'string',
                'max:255',
                Rule::unique('resolutions')->where(function ($query) use ($request) {
                    return $query->where('anio', $request->anio)
                        ->whereNull('deleted_at');
                })->ignore($resolution->id),
            ],
            'anio' => 'required|integer|digits:4|min:1901|max:2155',
            'area_id' => 'required|exists:areas,id',
            'pdf_file' => 'nullable|file|mimes:pdf',
        ]);

        $data = $request->only(['numero', 'anio', 'area']);
        if ($request->hasFile('pdf_file')) {
            Storage::disk('public')->delete($resolution->pdf_path);
            $data['pdf_path'] = $request->file('pdf_file')->store('resolutions', 'public');
        }
        $resolution->update($data);
        return redirect()->route('resolutions.index')
            ->with('success', 'Resolución actualizada exitosamente.');
    }

    public function destroy(Resolution $resolution)
    {

        if ($resolution->pdf_path) {
            Storage::disk('public')->delete($resolution->pdf_path);
        }

        $resolution->delete();

        return redirect()->route('resolutions.index')
            ->with('success', 'Resolución eliminada exitosamente.');
    }
}
