<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Course;
use App\Models\Resolution;
use Illuminate\Support\Facades\Storage;
use App\Models\Area;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

class CourseController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $user = Auth::user();
        $query = Course::query();

        if ($user->role) {
            if ($user->role->name === 'Administrador' && $user->area_id) {
                $query->where('area_id', $user->area_id);
            }
        }
        $courses = $query->with(['resolution', 'area'])->latest()->paginate(10);
        return view('courses.index', compact('courses'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $resolutions = Resolution::all();
        $areas = Area::all();

        return view('courses.create', compact('resolutions', 'areas'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {

        $data = $request->validate([
            'area_id' => 'required|exists:areas,id',
            'nro_curso' => 'required|string|max:255',
            'nombre' => 'required|string|max:255',
            'periodo' => 'required|string|max:255',
            'horas' => 'required|integer|min:1',
            'tipo_horas' => 'required|in:Reloj,Cátedra',
            'resolution_id' => 'required|exists:resolutions,id',
            'objetivo' => 'nullable|string',
            'contenido' => 'nullable|string',
            'maxima_nota' => 'required|integer|min:1',
            'capacitador_nombre' => 'nullable|string',
            'coordinador_nombre' => 'nullable|string',
            'signature1' => 'nullable|image|mimes:png|max:2048',
            'signature2' => 'nullable|image|mimes:png|max:2048',
        ]);

        if ($request->hasFile('signature1')) {
            $data['signature1_path'] = $request->file('signature1')->store('signatures', 'public');
        }
        if ($request->hasFile('signature2')) {
            $data['signature2_path'] = $request->file('signature2')->store('signatures', 'public');
        }

        Course::create($data);

        return redirect()->route('courses.index')->with('success', 'Curso creado exitosamente.');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Course $course)
    {
        $resolutions = Resolution::all();
        $areas = Area::all();

        return view('courses.edit', compact('course', 'resolutions', 'areas'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Course $course)
    {
        $data = $request->validate([
            'area_id' => 'required|exists:areas,id',
            'nro_curso' => 'required|string|max:255',
            'nombre' => 'required|string|max:255',
            'periodo' => 'required|string|max:255',
            'horas' => 'required|integer|min:1',
            'tipo_horas' => 'required|in:Reloj,Cátedra',
            'resolution_id' => 'required|exists:resolutions,id',
            'objetivo' => 'nullable|string',
            'contenido' => 'nullable|string',
            'maxima_nota' => 'required|integer|min:1',
            'capacitador_nombre' => 'nullable|string',
            'coordinador_nombre' => 'nullable|string',
            'signature1' => 'nullable|image|mimes:png|max:2048',
            'signature2' => 'nullable|image|mimes:png|max:2048',
        ]);

        if ($request->hasFile('signature1')) {
            if ($course->signature1_path) {
                Storage::disk('public')->delete($course->signature1_path);
            }
            $data['signature1_path'] = $request->file('signature1')->store('signatures', 'public');
        }
        if ($request->hasFile('signature2')) {
            if ($course->signature2_path) {
                Storage::disk('public')->delete($course->signature2_path);
            }
            $data['signature2_path'] = $request->file('signature2')->store('signatures', 'public');
        }

        $course->update($data);

        return redirect()->route('courses.index')->with('success', 'Curso actualizado exitosamente.');
    }
    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Course $course)
    {
        if ($course->signature1_path) {
            Storage::disk('public')->delete($course->signature1_path);
        }
        if ($course->signature2_path) {
            Storage::disk('public')->delete($course->signature2_path);
        }

        $course->delete();

        return redirect()->route('courses.index')
            ->with('success', 'Curso eliminado exitosamente.');
    }
}
