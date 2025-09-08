<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class HomeController extends Controller
{
    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        $user = Auth::user();

        if ($user->role->name === 'Root' || $user->role->name === 'Administrador') {
            return view('home');
        }

        if ($user->role->name === 'Persona') {
            return redirect()->route('persona.dashboard');
        }

        return view('welcome');
    }
}
