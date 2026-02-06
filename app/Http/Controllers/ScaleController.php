<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Models\ActionLog;
use App\Services\ScaleService;
use App\Services\ScaleReportService;
use App\Services\ScaleAutoGenerator;

class ScaleController extends Controller
{
    protected $service;
    protected $reportService;
    protected $autoGenerator;

    // Injeção de Dependência: O Laravel resolve tudo sozinho aqui
    public function __construct(
        ScaleService $service, 
        ScaleReportService $reportService,
        ScaleAutoGenerator $autoGenerator
    ) {
        $this->service = $service;
        $this->reportService = $reportService;
        $this->autoGenerator = $autoGenerator;
    }

    // 1. Tela Inicial
    public function index(Request $request)
    {
        if ($request->has(['start_date', 'end_date'])) {
            return redirect()->route('scales.manage', $request->only(['start_date', 'end_date']));
        }
        return view('scales.index');
    }

    // 2. O Calendário (Visualização/Edição)
    public function manage(Request $request)
    {
        $start = $request->start_date ? Carbon::parse($request->start_date) : Carbon::today();
        $end = $request->end_date ? Carbon::parse($request->end_date) : Carbon::today()->addDays(6);

        if ($start > $end) return back()->with('error', 'Data inicial maior que final.');
        if ($end->diffInDays($start) > 40) return back()->with('error', 'Máximo 40 dias.');

        // O Service cuida de buscar e montar os dados
        $data = $this->service->getScaleData($start, $end);

        // Busca ID do "NÃO HÁ" para a view (se necessário na view blade)
        $idNaoHa = $data['users']->firstWhere('name', 'NÃO HÁ')?->id;

        return view('scales.edit', [
            'days' => $data['days'],
            'users' => $data['users'],
            'start' => $start,
            'end' => $end,
            'idNaoHa' => $idNaoHa
        ]);
    }

    // 3. Salvar Alterações
    public function store(Request $request)
    {
        // O Service processa e retorna o que mudou
        $changedDays = $this->service->updateShifts(
            $request->input('slots', []), 
            $request->input('names', [])
        );

        if (count($changedDays) > 0) {
            ActionLog::register('Escalas', 'Salvar Alterações', [
                'dias_alterados' => implode(', ', $changedDays)
            ]);
        }

        return redirect()->route('scales.manage', $request->only(['start_date', 'end_date']))
                         ->with('success', 'Escala atualizada!');
    }

    // 4. Geração Automática (O Matemático)
    public function autoGenerate(Request $request)
    {
        try {
            // Delega tudo para o gerador
            $result = $this->autoGenerator->execute($request->start_date, $request->end_date);
            
            ActionLog::register('Escalas', 'Geração Automática', $result['log_data']);

            return back()->with('success', $result['message']);

        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    // 5. Regenerar Layout do Dia (8h/6h)
    public function regenerateDay(Request $request)
    {
        $this->service->resetDay($request->date, $request->mode);
        return back()->with('success', 'Layout atualizado!');
    }

    // 6. Imprimir PDF
    public function print(Request $request)
    {
        $start = Carbon::parse($request->start_date);
        $end = Carbon::parse($request->end_date);

        ActionLog::register('Escalas', 'Baixar PDF', [
            'periodo' => $start->format('d/m') . ' a ' . $end->format('d/m')
        ]);

        return $this->reportService->generatePdf($start, $end)
                    ->stream('ESCALA_' . $start->format('d-m') . '_a_' . $end->format('d-m') . '.pdf');
    }

    // 7. Enviar Email
    public function sendEmail(Request $request)
    {
        $result = $this->reportService->sendEmailByPeriod(
            $request->recipients, 
            Carbon::parse($request->start_date), 
            Carbon::parse($request->end_date), 
            auth()->user()->name
        );

        if ($result['failed_count'] > 0) {
            return back()->with('warning', 'Envio parcial. Falhas: ' . count($result['failed_names']));
        }

        return back()->with('success', 'E-mails enviados com sucesso!');
    }
}