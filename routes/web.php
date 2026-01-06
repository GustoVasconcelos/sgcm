<?php

use App\Http\Controllers\UserController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
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
    Route::get('/dashboard', [DashboardController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

    // Rotas de Perfil
    Route::get('/profile', [App\Http\Controllers\ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile', [App\Http\Controllers\ProfileController::class, 'update'])->name('profile.update');

    // Novas Rotas de Escala Contínua
    Route::get('scales', [\App\Http\Controllers\ScaleController::class, 'index'])->name('scales.index');
    Route::get('scales/manage', [\App\Http\Controllers\ScaleController::class, 'manage'])->name('scales.manage');
    Route::post('scales/store', [\App\Http\Controllers\ScaleController::class, 'store'])->name('scales.store');
    Route::get('scales/print', [\App\Http\Controllers\ScaleController::class, 'print'])->name('scales.print');
    
    // Rota de Regenerar Dia (agora não precisa do ID da escala na URL)
    Route::post('scales/regenerate', [\App\Http\Controllers\ScaleController::class, 'regenerateDay'])->name('scales.day.regenerate');

    // Rotas de Relatório RH Escalas
    Route::get('reports/rh', [\App\Http\Controllers\ScaleController::class, 'rhForm'])->name('reports.rh');
    Route::post('reports/rh', [\App\Http\Controllers\ScaleController::class, 'rhGenerate'])->name('reports.rh.generate');

    // Rota das ferias
    Route::resource('vacations', \App\Http\Controllers\VacationController::class);

    // Rotas de Grade
    Route::get('/schedules', [ScheduleController::class, 'index'])->name('schedules.index');
    Route::post('/schedules', [ScheduleController::class, 'store'])->name('schedules.store');
    Route::delete('/schedules/{schedule}', [ScheduleController::class, 'destroy'])->name('schedules.destroy');
    
    // Ações Especiais da Grade de Programacao
    Route::post('/schedules/clone', [ScheduleController::class, 'clone'])->name('schedules.clone');
    Route::post('/schedules/{id}/toggle/{type}', [ScheduleController::class, 'toggleStatus'])->name('schedules.toggle');
    
    // CRUD de Programas (Catálogo)
    Route::resource('programs', ProgramController::class);

    // Rota da afinacao
    Route::get('/afinacao', function () {
        return view('tools.afinacao');
    })->name('tools.afinacao');

    Route::post('/log/register', function (\Illuminate\Http\Request $request) {
        \App\Models\ActionLog::register($request->module, $request->action, $request->details ?? []);
        return response()->json(['status' => 'ok']);
    })->middleware('auth');

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

    // Coloque dentro do grupo middleware auth e admin (se quiser restringir)
    Route::get('/logs', [\App\Http\Controllers\LogController::class, 'index'])->name('logs.index');
});