<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\AreaController;
use App\Http\Controllers\ResolutionController;
use App\Http\Controllers\CourseController;
use App\Http\Controllers\PersonController;
use App\Http\Controllers\CertificateController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\PersonaDashboardController;
use App\Http\Controllers\PersonProfileController;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/certificates/verify/{unique_code}', [CertificateController::class, 'verify'])->name('certificates.verify');



//  RUTAS PROTEGIDAS (Requieren Login)

Route::middleware('auth')->group(function () {

    // Dashboard principal
    Route::get('/home', [HomeController::class, 'index'])->name('home');

    // Redirección
    Route::get('/dashboard', function () {
        return redirect()->route('home');
    })->name('dashboard');

    // Perfil de Usuario
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    Route::get('/mi-dashboard', [PersonaDashboardController::class, 'index'])->name('persona.dashboard');


    // RUTAS DE PERSONAS
    Route::get('persons/import', [PersonController::class, 'showImportForm'])->name('persons.import.form');
    Route::post('persons/import', [PersonController::class, 'import'])->name('persons.import');
    Route::resource('persons', PersonController::class);


    Route::get('/mi-perfil', [PersonProfileController::class, 'edit'])->name('persona.profile.edit');
    Route::put('/mi-perfil', [PersonProfileController::class, 'update'])->name('persona.profile.update');




    // Módulos CRUD para Administradores y Root
    Route::resource('resolutions', ResolutionController::class);
    Route::resource('courses', CourseController::class);
    Route::resource('areas', AreaController::class);
    Route::resource('certificates', CertificateController::class)->except(['show']);

    // Rutas para la importación masiva de certificados
    Route::get('certificates/import/template', [CertificateController::class, 'downloadTemplate'])->name('certificates.template.download');
    Route::get('certificates/import', [CertificateController::class, 'showImportForm'])->name('certificates.import.form');
    Route::post('certificates/import', [CertificateController::class, 'import'])->name('certificates.import');
    Route::get('certificates/import/preview', [CertificateController::class, 'showPreview'])->name('certificates.import.preview');
    Route::post('certificates/import/process', [CertificateController::class, 'processImport'])->name('certificates.import.process');
    Route::get('certificates/import/preview-pdf', [CertificateController::class, 'previewPdf'])->name('certificates.import.preview_pdf');
    Route::get('/get-area-by-course/{course}', [CertificateController::class, 'getAreaByCourse'])->name('courses.get_area');

    // RUTAS EXCLUSIVAS PARA EL ROL "ROOT"
    Route::middleware('is.root')->group(function () {
        Route::resource('users', UserController::class);
    });
});


// Rutas de autenticación de Laravel Breeze (Login, etc.)
require __DIR__ . '/auth.php';
