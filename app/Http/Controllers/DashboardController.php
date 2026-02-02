<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\ScaleShift;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user(); // Pega o usuário logado para facilitar
        $displayShift = null; // O turno principal que será exibido (Card Grande)
        $returnShift = null;  // O turno de retorno (Só preenchido se o principal for Folga)
        
        // --- 1. LÓGICA DE ESCALAS (MANTIDA ORIGINAL) ---
        if ($user->is_operator) {
            $now = Carbon::now();
            $today = Carbon::today();

            // Pega o turno de HOJE
            $todayShift = ScaleShift::where('user_id', $user->id)
                ->whereDate('date', $today)
                ->first();

            $showNext = false;

            // Análise Temporal: Devemos mostrar o próximo?
            if ($todayShift) {
                if ($todayShift->name === 'FOLGA') {
                    $displayShift = $todayShift;
                } 
                else {
                    $parts = explode(':', $todayShift->name);
                    $startHour = isset($parts[0]) ? intval($parts[0]) : 0;

                    if ($startHour > 0 && $now->hour >= $startHour) {
                        $showNext = true;
                    } else {
                        $displayShift = $todayShift;
                    }
                }
            } else {
                $showNext = true;
            }

            // Busca o Próximo Turno
            if ($showNext) {
                $displayShift = ScaleShift::where('user_id', $user->id)
                    ->whereDate('date', '>', $today)
                    ->orderBy('date', 'asc')
                    ->orderBy('order', 'asc')
                    ->first();
            }

            // Lógica da Folga
            if ($displayShift && $displayShift->name === 'FOLGA') {
                $returnShift = ScaleShift::where('user_id', $user->id)
                    ->whereDate('date', '>', $displayShift->date)
                    ->where('name', '!=', 'FOLGA')
                    ->orderBy('date', 'asc')
                    ->orderBy('order', 'asc')
                    ->first();
            }
        }

        // --- 2. LÓGICA DOS CARDS (NOVA) ---
        $menuItems = [
            [
                'title'   => 'Afinação',
                'desc'    => 'Afinação do jornal.',
                'icon'    => 'bi-mic',
                'color'   => 'text-warning',
                'route'   => route('tools.afinacao'),
                'visible' => $user->is_operator
            ],
            [
                'title'   => 'Regressiva',
                'desc'    => ($user->profile === 'viewer') ? 'Visualizar regressiva.' : 'Controle de tempo e regressiva.',
                'icon'    => 'bi-stopwatch',
                'color'   => 'text-primary',
                'route'   => ($user->profile === 'viewer') ? route('timers.viewer') : route('timers.operator'),
                'visible' => true // Todos acessam
            ],
            [
                'title'   => 'Escalas',
                'desc'    => 'Visualize ou edite os horários.',
                'icon'    => 'bi-calendar-range',
                'color'   => 'text-info',
                'route'   => route('scales.index'),
                'visible' => $user->is_operator
            ],
            [
                'title'   => 'PGMs FDS',
                'desc'    => 'Controle dos programas locais.',
                'icon'    => 'bi-broadcast',
                'color'   => 'text-success',
                'route'   => route('schedules.index'),
                'visible' => $user->is_operator
            ],
            [
                'title'   => 'Férias',
                'desc'    => 'Cadastro e consulta de férias.',
                'icon'    => 'bi-airplane',
                'color'   => 'text-danger',
                'route'   => route('vacations.index'),
                'visible' => $user->is_operator
            ],
            // -- ÁREA ADMINISTRATIVA --
            [
                'title'    => 'Gerenciar Equipe',
                'desc'     => 'Cadastro e controle de usuários.',
                'icon'     => 'bi-people-fill',
                'color'    => 'text-white',
                'route'    => route('users.index'),
                'badge'    => 'Admin',
                'bg_class' => 'bg-dark border-secondary',
                'visible'  => $user->profile === 'admin'
            ],
            [
                'title'    => 'Visualizar Logs',
                'desc'     => 'Histórico de ações do sistema.',
                'icon'     => 'bi-activity',
                'color'    => 'text-white',
                'route'    => route('logs.index'),
                'badge'    => 'Admin',
                'bg_class' => 'bg-dark border-secondary',
                'visible'  => $user->profile === 'admin'
            ],
            [
                'title'    => 'Configurações',
                'desc'     => 'Definir configurações do sistema.',
                'icon'     => 'bi-gear-fill',
                'color'    => 'text-white',
                'route'    => route('logs.settings.index'),
                'badge'    => 'Admin',
                'bg_class' => 'bg-dark border-secondary',
                'visible'  => $user->profile === 'admin'
            ],
        ];

        // Filtra apenas os cards visíveis para este usuário
        $cards = array_filter($menuItems, fn($item) => $item['visible']);

        // Retorna tudo para a View
        return view('dashboard', compact('displayShift', 'returnShift', 'cards'));
    }
}