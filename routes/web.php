<?php

use App\Http\Controllers\UserController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ScheduleController;
use App\Http\Controllers\ProgramController;
use Illuminate\Support\Facades\Route;

// --- Rotas Públicas ---
Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.submit');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

Route::get('/', function () {
    return redirect()->route('login');
});

// --- Rotas de Usuários Logados (Comum a todos) ---
Route::middleware('auth')->group(function () {
    
    // Dashboard do Usuário Comum
    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');

    // Rotas de Perfil
    Route::get('/profile', [App\Http\Controllers\ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile', [App\Http\Controllers\ProfileController::class, 'update'])->name('profile.update');

    // Rotas das Escalas
    Route::resource('scales', \App\Http\Controllers\ScaleController::class);
    Route::get('scales/{scale}/pdf', [\App\Http\Controllers\ScaleController::class, 'pdf'])->name('scales.pdf');
    Route::post('scales/{scale}/regenerate-day', [\App\Http\Controllers\ScaleController::class, 'regenerateDay'])->name('scales.day.regenerate');

    // Rotas de Relatório RH Escalas
    Route::get('reports/rh', [\App\Http\Controllers\ScaleController::class, 'rhForm'])->name('reports.rh');
    Route::post('reports/rh', [\App\Http\Controllers\ScaleController::class, 'rhGenerate'])->name('reports.rh.generate');

    // Rota das ferias
    Route::resource('vacations', \App\Http\Controllers\VacationController::class);

    // Rotas de Grade
    Route::get('/schedules', [ScheduleController::class, 'index'])->name('schedules.index');
    Route::post('/schedules', [ScheduleController::class, 'store'])->name('schedules.store');
    Route::delete('/schedules/{schedule}', [ScheduleController::class, 'destroy'])->name('schedules.destroy');
    
    // Ações Especiais
    Route::post('/schedules/clone', [ScheduleController::class, 'clone'])->name('schedules.clone');
    Route::post('/schedules/{id}/toggle/{type}', [ScheduleController::class, 'toggleStatus'])->name('schedules.toggle');
    
    // CRUD de Programas (Catálogo)
    Route::resource('programs', ProgramController::class);

    // Rota da afinacao
    Route::get('/afinacao', function () {
        return view('tools.afinacao');
    })->name('tools.afinacao');

});

// --- Rotas EXCLUSIVAS de Administrador ---
// Tudo aqui dentro precisa de login E ser admin
Route::middleware(['auth', 'admin'])->prefix('admin')->group(function () {
    
    // Dashboard do Admin
    Route::get('/dashboard', function () {
        return view('admin.dashboard');
    })->name('admin.dashboard');

    // CRUD de Usuários (agora só admin acessa)
    Route::resource('users', UserController::class);
});