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
        $user = Auth::user();
        $displayShift = null;
        $returnShift = null;
        
        // --- 1. LÓGICA DE ESCALAS ---
        // Verifica se o usuário tem a permissão de ver escalas (definida no Seeder)
        // ou se ele participa da escala (is_operator no banco ainda existe para fins de lógica de turno)
        if ($user->is_operator && $user->can('ver_escalas')) {
            $now = Carbon::now();
            $today = Carbon::today();

            $todayShift = ScaleShift::where('user_id', $user->id)
                ->whereDate('date', $today)
                ->first();

            $showNext = false;

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

            if ($showNext) {
                $displayShift = ScaleShift::where('user_id', $user->id)
                    ->whereDate('date', '>', $today)
                    ->orderBy('date', 'asc')
                    ->orderBy('order', 'asc')
                    ->first();
            }

            if ($displayShift && $displayShift->name === 'FOLGA') {
                $returnShift = ScaleShift::where('user_id', $user->id)
                    ->whereDate('date', '>', $displayShift->date)
                    ->where('name', '!=', 'FOLGA')
                    ->orderBy('date', 'asc')
                    ->orderBy('order', 'asc')
                    ->first();
            }
        }

        // --- 2. LÓGICA DOS CARDS (COM PERMISSÕES SPATIE) ---
        $menuItems = [
            [
                'title'   => 'Afinação',
                'desc'    => 'Afinação do jornal.',
                'icon'    => 'bi-mic',
                'color'   => 'text-warning',
                'route'   => route('tools.afinacao'),
                'visible' => $user->can('usar_afinacao') // Verifica permissão
            ],
            [
                'title'   => 'Regressiva',
                'desc'    => $user->hasRole('Viewer') ? 'Visualizar tela de estúdio.' : 'Controle de tempo e regressiva.',
                'icon'    => 'bi-stopwatch',
                'color'   => 'text-primary',
                // Se for Viewer vai pro viewer, se tiver permissão de operar vai pro operator
                'route'   => $user->hasRole('Viewer') ? route('timers.viewer') : route('timers.operator'),
                'visible' => $user->can('ver_regressiva') // Todos os grupos têm essa permissão
            ],
            [
                'title'   => 'Escalas',
                'desc'    => 'Visualize ou edite os horários.',
                'icon'    => 'bi-calendar-range',
                'color'   => 'text-info',
                'route'   => route('scales.index'),
                'visible' => $user->can('ver_escalas')
            ],
            [
                'title'   => 'PGMs FDS',
                'desc'    => 'Controle dos programas locais.',
                'icon'    => 'bi-broadcast',
                'color'   => 'text-success',
                'route'   => route('schedules.index'),
                'visible' => $user->can('ver_pgm_fds')
            ],
            [
                'title'   => 'Férias',
                'desc'    => 'Cadastro e consulta de férias.',
                'icon'    => 'bi-airplane',
                'color'   => 'text-danger',
                'route'   => route('vacations.index'),
                'visible' => $user->can('ver_ferias')
            ],
            // -- ÁREA ADMINISTRATIVA --
            [
                'title'    => 'Gerenciar Usuários',
                'desc'     => 'Cadastro e controle de usuários.',
                'icon'     => 'bi-person-fill',
                'color'    => 'text-white',
                'route'    => route('users.index'),
                'badge'    => 'Admin',
                'bg_class' => 'bg-dark border-secondary',
                'visible'  => $user->hasRole('Admin')
            ],
            [
                'title'    => 'Gerenciar Grupos',
                'desc'     => 'Cadastro e controle de grupos.',
                'icon'     => 'bi-people-fill',
                'color'    => 'text-white',
                'route'    => route('roles.index'),
                'badge'    => 'Admin',
                'bg_class' => 'bg-dark border-secondary',
                'visible'  => $user->hasRole('Admin')
            ],
            [
                'title'    => 'Visualizar Logs',
                'desc'     => 'Histórico de ações do sistema.',
                'icon'     => 'bi-activity',
                'color'    => 'text-white',
                'route'    => route('logs.index'),
                'badge'    => 'Admin',
                'bg_class' => 'bg-dark border-secondary',
                'visible'  => $user->hasRole('Admin')
            ],
        ];

        // Filtra apenas os cards visíveis
        $cards = array_filter($menuItems, fn($item) => $item['visible']);

        return view('dashboard', compact('displayShift', 'returnShift', 'cards'));
    }
}