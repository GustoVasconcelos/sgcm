<?php

namespace App\Services;

use App\Models\User;
use App\Models\ActionLog;
use App\Mail\ScaleShipped;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Collection;

class ScaleReportService
{
    protected $scaleService;

    // Injeção de Dependência: O Laravel entrega o ScaleService pronto aqui
    public function __construct(ScaleService $scaleService)
    {
        $this->scaleService = $scaleService;
    }

    /**
     * Gera o objeto PDF (DomPDF) pronto para download ou anexo.
     */
    public function generatePdf(Carbon $start, Carbon $end)
    {
        // 1. Pede os dados já tratados para o serviço principal
        $data = $this->scaleService->getScaleData($start, $end);

        // 2. Prepara variáveis para a View
        $days = $data['days'];
        $users = $data['users'];
        $startDate = $start;
        $endDate = $end;
        $reportTitle = 'ESCALA DE TRABALHO'; 

        // 3. Renderiza
        $pdf = Pdf::loadView('scales.pdf', compact('days', 'users', 'startDate', 'endDate', 'reportTitle'));
        $pdf->setPaper('a4', 'portrait');

        return $pdf;
    }

    /**
     * Processa o envio de e-mails para os destinatários selecionados.
     */
    public function sendEmailByPeriod(array $recipientIds, Carbon $start, Carbon $end, string $senderName): array
    {
        $periodoTxt = $start->format('d/m/Y') . ' a ' . $end->format('d/m/Y');

        // 1. Gera o PDF em memória (Reusa a função acima!)
        $pdf = $this->generatePdf($start, $end);
        $pdfContent = $pdf->output();

        // 2. Busca destinatários
        $recipients = User::whereIn('id', $recipientIds)->get();
        
        $sentNames = [];
        $failedNames = [];

        // 3. Loop de Envio
        foreach ($recipients as $user) {
            try {
                Mail::to($user->email)
                    ->send(new ScaleShipped($pdfContent, $periodoTxt, $senderName));
                
                $sentNames[] = $user->name;
            } catch (\Exception $e) {
                // Guarda o erro
                $failedNames[] = "{$user->name} ({$e->getMessage()})";
            }
        }

        // 4. Registra Logs aqui mesmo (Tira a responsabilidade do Controller)
        if (count($sentNames) > 0) {
            ActionLog::register('Escalas', 'Enviar por Email', [
                'periodo' => $periodoTxt,
                'enviado_por' => $senderName,
                'destinatarios' => implode(', ', $sentNames),
                'total_sucesso' => count($sentNames)
            ]);
        }

        if (count($failedNames) > 0) {
            ActionLog::register('Escalas', 'Erro no Envio de Email', [
                'periodo' => $periodoTxt,
                'tentativa_de' => $senderName,
                'falhas_detalhadas' => $failedNames
            ]);
        }

        return [
            'success_count' => count($sentNames),
            'failed_count' => count($failedNames),
            'failed_names' => $failedNames,
            'sent_names' => $sentNames
        ];
    }
}