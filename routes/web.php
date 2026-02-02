<?php

use App\Http\Controllers\UserController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ScheduleController;
use App\Http\Controllers\ProgramController;
use App\Http\Controllers\LogSettingsController; 
use App\Http\Controllers\StudioTimerController;
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

    // Rotas de Escala
    Route::get('scales', [\App\Http\Controllers\ScaleController::class, 'index'])->name('scales.index');
    Route::get('scales/manage', [\App\Http\Controllers\ScaleController::class, 'manage'])->name('scales.manage');
    Route::post('scales/store', [\App\Http\Controllers\ScaleController::class, 'store'])->name('scales.store');
    Route::get('scales/print', [\App\Http\Controllers\ScaleController::class, 'print'])->name('scales.print');
    
    // Rota de Gerar Escala Rotativa
    Route::post('scales/auto', [\App\Http\Controllers\ScaleController::class, 'autoGenerate'])->name('scales.auto');

    // Rota de Enviar Escala por Email
    Route::post('scales/email', [\App\Http\Controllers\ScaleController::class, 'sendEmail'])->name('scales.email');
    
    // Rota de Regenerar Dia
    Route::post('scales/regenerate', [\App\Http\Controllers\ScaleController::class, 'regenerateDay'])->name('scales.day.regenerate');

    // Rota das ferias
    Route::resource('vacations', \App\Http\Controllers\VacationController::class);

    // Rotas de Grade
    Route::get('/schedules', [ScheduleController::class, 'index'])->name('schedules.index');
    Route::post('/schedules', [ScheduleController::class, 'store'])->name('schedules.store');
    Route::delete('/schedules/{schedule}', [ScheduleController::class, 'destroy'])->name('schedules.destroy');
    Route::put('/schedules/{schedule}', [ScheduleController::class, 'update'])->name('schedules.update');
    
    // Ações Especiais da Grade de Programacao
    Route::post('/schedules/clone', [ScheduleController::class, 'clone'])->name('schedules.clone');
    Route::post('/schedules/{id}/toggle/{type}', [ScheduleController::class, 'toggleStatus'])->name('schedules.toggle');
    
    // CRUD de Programas (Catálogo)
    Route::resource('programs', ProgramController::class);

    // Rota da afinacao
    Route::get('/afinacao', function () {
        return view('tools.afinacao');
    })->name('tools.afinacao');

    // Rotas para os Timers
    Route::prefix('timers')->group(function () {
        Route::get('/operator', [StudioTimerController::class, 'operator'])->name('timers.operator');
        Route::get('/viewer', [StudioTimerController::class, 'viewer'])->name('timers.viewer');
        Route::get('/status', [StudioTimerController::class, 'status']); // API de sync
        Route::post('/update-regressive', [StudioTimerController::class, 'updateRegressive']);
        Route::post('/update-stopwatch', [StudioTimerController::class, 'updateStopwatch']);
        Route::post('/update-bk', [StudioTimerController::class, 'updateBk']);
    });

    // Rota API para logs do Javascript
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

    // CRUD de Usuários
    Route::resource('users', UserController::class);

    // Logs de Atividade
    Route::get('/logs', [\App\Http\Controllers\LogController::class, 'index'])->name('logs.index');

    // Configuracoes
    Route::prefix('settings')->name('logs.settings.')->group(function () {
        Route::get('/', [LogSettingsController::class, 'index'])->name('index');
        Route::post('/update', [LogSettingsController::class, 'update'])->name('update');
        Route::post('/clean', [LogSettingsController::class, 'cleanOld'])->name('clean');
        Route::post('/clear-all', [LogSettingsController::class, 'clearAll'])->name('clear_all');
        Route::get('/export', [LogSettingsController::class, 'export'])->name('export');
    });
});