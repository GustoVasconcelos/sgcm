<?php

namespace App\Http\Controllers;

use App\Models\ActionLog;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class LogController extends Controller
{
    public function index(Request $request)
    {
        // Inicia a query ordenando do mais recente para o antigo
        $query = ActionLog::with(['user' => function($query) {
            $query->withTrashed();
        }])->latest();

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

        // Pega do banco ou usa 20 como padrão
        $perPage = \App\Models\Setting::get('log_pagination', 20);
        $logs = $query->paginate($perPage)->withQueryString();
        $users = User::orderBy('name')->get();
        
        // Pega os módulos únicos que existem no banco para preencher o select
        $modules = ActionLog::select('module')->distinct()->pluck('module');

        return view('admin.logs', compact('logs', 'users', 'modules'));
    }

    public function exportPdf(Request $request)
    {
        // Mesma query do index(), mas sem paginação
        $query = ActionLog::with(['user' => function($query) {
            $query->withTrashed();
        }])->latest();

        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }
        if ($request->filled('module')) {
            $query->where('module', $request->module);
        }
        if ($request->filled('date')) {
            $query->whereDate('created_at', $request->date);
        }

        $logs = $query->get();

        // Resolve o nome do usuário do filtro para exibição no PDF
        $filterUser = $request->filled('user_id')
            ? optional(User::withTrashed()->find($request->user_id))->name
            : null;

        $filters = [
            'user'   => $filterUser,
            'module' => $request->module,
            'date'   => $request->date,
        ];

        ActionLog::register('Logs', 'Exportar PDF', [
            'filtros'   => array_filter($filters),
            'total'     => $logs->count(),
            'exportado_por' => auth()->user()->name,
        ]);

        $pdf = Pdf::loadView('admin.logs-pdf', compact('logs', 'filters'));
        $pdf->setPaper('a4', 'landscape');

        $fileName = 'logs_' . date('Y-m-d_H-i') . '.pdf';

        return $pdf->stream($fileName);
    }
    
    public function store(Request $request)
    {
        // Validação básica para garantir dados mínimos
        $request->validate([
            'module' => 'required|string',
            'action' => 'required|string',
            'details' => 'nullable|array',
        ]);

        ActionLog::register(
            $request->module, 
            $request->action, 
            $request->details ?? []
        );

        return response()->json(['status' => 'ok'], 201);
    }
}