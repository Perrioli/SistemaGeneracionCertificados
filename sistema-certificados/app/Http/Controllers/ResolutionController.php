<?php

namespace App\Http\Controllers;

use App\Models\Resolution;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;


class ResolutionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $resolutions = Resolution::latest()->paginate(10);
        return view('resolutions.index', compact('resolutions'));
    }

    public function create()
    {
        return view('resolutions.create');
    }


    public function store(Request $request)
    {
        $request->validate([
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
            'area' => 'required|string|max:255',
            'pdf_file' => 'required|file|mimes:pdf',
        ]);


        $filePath = $request->file('pdf_file')->store('resolutions', 'public');
        Resolution::create([
            'numero' => $request->numero,
            'anio' => $request->anio,
            'area' => $request->area,
            'pdf_path' => $filePath,
        ]);
        return redirect()->route('resolutions.index')
            ->with('success', 'Resolución creada exitosamente.');
    }


    public function edit(Resolution $resolution)
    {
        return view('resolutions.edit', compact('resolution'));
    }

    public function update(Request $request, Resolution $resolution)
    {
        $request->validate([
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
            'area' => 'required|string|max:255',
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
