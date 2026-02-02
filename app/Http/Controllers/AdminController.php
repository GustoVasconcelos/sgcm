<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Spatie\Permission\Models\Role;
use App\Models\Program; // Supondo que você tenha esse Model
use App\Models\ActionLog;
use Illuminate\Support\Facades\DB;

class AdminController extends Controller
{
    public function index()
    {
        // 1. Métricas Rápidas (Topo)
        $stats = [
            'users' => User::count(),
            'roles' => Role::count(),
            'programs' => Program::count(), // Se não tiver o model Program, remova ou troque
            'logs_today' => ActionLog::whereDate('created_at', today())->count(),
        ];

        // 2. Últimas 5 Atividades do Sistema (Rodapé)
        // Trazemos o usuário junto ('user') pra não pesar o banco com N+1 queries
        $recentLogs = ActionLog::with('user')
                        ->latest()
                        ->take(5)
                        ->get();

        return view('admin.dashboard', compact('stats', 'recentLogs'));
    }
}