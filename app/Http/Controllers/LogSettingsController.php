<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Setting;
use App\Models\ActionLog;
use Carbon\Carbon;
use Illuminate\Support\Facades\Response;

class LogSettingsController extends Controller
{
    public function index()
    {
        $retentionDays = Setting::get('log_retention_days', 365);
        $pagination = Setting::get('log_pagination', 20);
        
        // Contagem para o painel de estatísticas
        $totalLogs = ActionLog::count();
        $oldestLog = ActionLog::oldest()->first();
        $oldestDate = $oldestLog ? $oldestLog->created_at->format('d/m/Y') : '-';

        return view('admin.settings.logs', compact('retentionDays', 'pagination', 'totalLogs', 'oldestDate'));
    }

    public function update(Request $request)
    {
        $request->validate([
            'log_retention_days' => 'required|integer|min:1',
            'log_pagination' => 'required|integer|min:5|max:100',
        ]);

        Setting::set('log_retention_days', $request->log_retention_days);
        Setting::set('log_pagination', $request->log_pagination);

        return back()->with('success', 'Configurações salvas com sucesso!');
    }

    // Limpeza Automática (Baseada na configuração)
    public function cleanOld()
    {
        $days = Setting::get('log_retention_days', 365);
        $dateLimit = Carbon::now()->subDays($days);

        $deletedCount = ActionLog::where('created_at', '<', $dateLimit)->delete();

        // Registra que alguém fez a limpeza (Meta-Log!)
        ActionLog::register('Configurações', 'Limpeza Automática de Logs', [
            'dias_retencao' => $days,
            'registros_apagados' => $deletedCount
        ]);

        return back()->with('success', "$deletedCount registros antigos foram removidos.");
    }

    // Zona de Perigo: Limpar TUDO
    public function clearAll(Request $request)
    {
        // Verifica confirmação extra (senha ou texto) se quiser ser muito seguro.
        // Aqui vamos confiar no Confirm do JS e Auth Admin.
        
        ActionLog::truncate(); // Zera a tabela id volta a 1 (ou use delete() para manter id)

        // Recria o log avisando quem apagou tudo (já que a tabela foi zerada, esse será o ID 1)
        ActionLog::register('Configurações', 'Limpeza TOTAL de Logs', [
            'responsavel' => auth()->user()->name
        ]);

        return back()->with('success', 'Todos os logs foram apagados.');
    }

    // Exportação CSV
    public function export()
    {
        $fileName = 'logs_sistema_' . date('Y-m-d_H-i') . '.csv';
        
        $callback = function() {
            $file = fopen('php://output', 'w');
            
            // BOM para Excel reconhecer acentos
            fputs($file, "\xEF\xBB\xBF"); 

            // Cabeçalho
            fputcsv($file, ['ID', 'Data/Hora', 'Usuário', 'Módulo', 'Ação', 'Detalhes', 'IP'], ';');

            // Chunk para não estourar memória se tiver milhoes de logs
            ActionLog::with('user')->orderBy('id', 'desc')->chunk(1000, function($logs) use ($file) {
                foreach ($logs as $log) {
                    fputcsv($file, [
                        $log->id,
                        $log->created_at->format('d/m/Y H:i:s'),
                        $log->user->name ?? 'Usuário Excluído',
                        $log->module,
                        $log->action,
                        json_encode($log->details, JSON_UNESCAPED_UNICODE),
                        $log->ip_address
                    ], ';');
                }
            });

            fclose($file);
        };

        return Response::stream($callback, 200, [
            "Content-type" => "text/csv",
            "Content-Disposition" => "attachment; filename=$fileName",
            "Pragma" => "no-cache",
            "Cache-Control" => "must-revalidate, post-check=0, pre-check=0",
            "Expires" => "0"
        ]);
    }
}