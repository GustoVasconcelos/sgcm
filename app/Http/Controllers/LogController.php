<?php

namespace App\Http\Controllers;

use App\Models\ActionLog;
use App\Models\User;
use Illuminate\Http\Request;

class LogController extends Controller
{
    public function index(Request $request)
    {
        // Inicia a query ordenando do mais recente para o antigo
        $query = ActionLog::with('user')->latest();

        // 1. Filtro por Usuário
        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        // 2. Filtro por Módulo
        if ($request->filled('module')) {
            $query->where('module', $request->module);
        }

        // 3. Filtro por Data
        if ($request->filled('date')) {
            $query->whereDate('created_at', $request->date);
        }

        $logs = $query->paginate(20)->withQueryString(); // Paginação mantendo os filtros
        $users = User::orderBy('name')->get();
        
        // Pega os módulos únicos que existem no banco para preencher o select
        $modules = ActionLog::select('module')->distinct()->pluck('module');

        return view('admin.logs', compact('logs', 'users', 'modules'));
    }
}