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

// --- ROTAS AUTENTICADAS (Base) ---
// Qualquer usuário logado acessa (Dashboard, Perfil, Logs de JS)
Route::middleware(['auth'])->group(function () {
    
    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Perfil
    Route::get('/profile', [App\Http\Controllers\ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile', [App\Http\Controllers\ProfileController::class, 'update'])->name('profile.update');

    // API de Logs (Frontend)
    Route::post('/log/register', function (\Illuminate\Http\Request $request) {
        \App\Models\ActionLog::register($request->module, $request->action, $request->details ?? []);
        return response()->json(['status' => 'ok']);
    });

    // =========================================================================
    // MÓDULO: ESCALAS (Permissão: ver_escalas)
    // =========================================================================
    Route::middleware(['can:ver_escalas'])->group(function () {
        Route::get('scales', [\App\Http\Controllers\ScaleController::class, 'index'])->name('scales.index');
        Route::get('scales/manage', [\App\Http\Controllers\ScaleController::class, 'manage'])->name('scales.manage');
        Route::post('scales/store', [\App\Http\Controllers\ScaleController::class, 'store'])->name('scales.store');
        Route::get('scales/print', [\App\Http\Controllers\ScaleController::class, 'print'])->name('scales.print');
        Route::post('scales/auto', [\App\Http\Controllers\ScaleController::class, 'autoGenerate'])->name('scales.auto');
        Route::post('scales/email', [\App\Http\Controllers\ScaleController::class, 'sendEmail'])->name('scales.email');
        Route::post('scales/regenerate', [\App\Http\Controllers\ScaleController::class, 'regenerateDay'])->name('scales.day.regenerate');
    });

    // =========================================================================
    // MÓDULO: FÉRIAS (Permissão: ver_ferias)
    // =========================================================================
    Route::middleware(['can:ver_ferias'])->group(function () {
        Route::resource('vacations', \App\Http\Controllers\VacationController::class);
    });

    // =========================================================================
    // MÓDULO: PROGRAMAS/GRADE (Permissão: ver_pgm_fds)
    // =========================================================================
    Route::middleware(['can:ver_pgm_fds'])->group(function () {
        Route::get('/schedules', [ScheduleController::class, 'index'])->name('schedules.index');
        Route::post('/schedules', [ScheduleController::class, 'store'])->name('schedules.store');
        Route::delete('/schedules/{schedule}', [ScheduleController::class, 'destroy'])->name('schedules.destroy');
        Route::put('/schedules/{schedule}', [ScheduleController::class, 'update'])->name('schedules.update');
        Route::post('/schedules/clone', [ScheduleController::class, 'clone'])->name('schedules.clone');
        Route::post('/schedules/{id}/toggle/{type}', [ScheduleController::class, 'toggleStatus'])->name('schedules.toggle');
        
        // Catálogo de Programas
        Route::resource('programs', ProgramController::class);
    });

    // =========================================================================
    // MÓDULO: AFINAÇÃO (Permissão: usar_afinacao)
    // =========================================================================
    Route::middleware(['can:usar_afinacao'])->group(function () {
        Route::get('/afinacao', function () {
            return view('tools.afinacao');
        })->name('tools.afinacao');
    });

    // =========================================================================
    // MÓDULO: TIMERS / ESTÚDIO
    // =========================================================================
    Route::prefix('timers')->group(function () {
        
        // VISUALIZAÇÃO (Viewer, Operador, Admin)
        Route::middleware(['can:ver_regressiva'])->group(function () {
            Route::get('/viewer', [StudioTimerController::class, 'viewer'])->name('timers.viewer');
            Route::get('/status', [StudioTimerController::class, 'status']); // API de Leitura
        });

        // OPERAÇÃO (Apenas Operador e Admin)
        Route::middleware(['can:operar_regressiva'])->group(function () {
            Route::get('/operator', [StudioTimerController::class, 'operator'])->name('timers.operator');
            Route::post('/update-regressive', [StudioTimerController::class, 'updateRegressive']);
            Route::post('/update-stopwatch', [StudioTimerController::class, 'updateStopwatch']);
            Route::post('/update-bk', [StudioTimerController::class, 'updateBk']);
        });
    });

});

// =========================================================================
// MÓDULO: ADMINISTRADOR (Role: Admin)
// =========================================================================
Route::middleware(['auth', 'role:Admin'])->prefix('admin')->group(function () {
    
    Route::get('/dashboard', [\App\Http\Controllers\AdminController::class, 'index'])->name('admin.dashboard');

    Route::resource('users', UserController::class);

    Route::resource('roles', \App\Http\Controllers\RoleController::class);

    Route::resource('permissions', \App\Http\Controllers\PermissionController::class);

    Route::get('/logs', [\App\Http\Controllers\LogController::class, 'index'])->name('logs.index');

    Route::prefix('settings')->name('logs.settings.')->group(function () {
        Route::get('/', [LogSettingsController::class, 'index'])->name('index');
        Route::post('/update', [LogSettingsController::class, 'update'])->name('update');
        Route::post('/clean', [LogSettingsController::class, 'cleanOld'])->name('clean');
        Route::post('/clear-all', [LogSettingsController::class, 'clearAll'])->name('clear_all');
        Route::get('/export', [LogSettingsController::class, 'export'])->name('export');
    });
});