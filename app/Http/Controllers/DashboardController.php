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
        $displayShift = null; // O turno principal que será exibido (Card Grande)
        $returnShift = null;  // O turno de retorno (Só preenchido se o principal for Folga)
        
        if (Auth::user()->is_operator) {
            $now = Carbon::now();
            $today = Carbon::today();

            // 1. Pega o turno de HOJE
            $todayShift = ScaleShift::where('user_id', Auth::id())
                ->whereDate('date', $today)
                ->first();

            $showNext = false;

            // 2. Análise Temporal: Devemos mostrar o próximo?
            if ($todayShift) {
                // Se hoje é FOLGA, mostramos hoje mesmo ("Hoje você está de folga")
                if ($todayShift->name === 'FOLGA') {
                    $displayShift = $todayShift;
                } 
                else {
                    // Se hoje é TRABALHO, verificamos a hora
                    // Ex: extrai "14" de "14:00 - 22:00"
                    $parts = explode(':', $todayShift->name);
                    $startHour = isset($parts[0]) ? intval($parts[0]) : 0;

                    // 1. ($startHour > 0): Se o turno for 00h, essa parte falha, o IF falha, e ele cai no 'else' (Mostra HOJE).
                    // 2. ($now->hour >= $startHour): Para turnos normais (ex: 14h), funciona como antes (15h > 14h -> mostra próximo).
                    if ($startHour > 0 && $now->hour >= $startHour) {
                        $showNext = true;
                    } else {
                        // Se ainda não começou (ex: são 08h e turno é 14h), mostra HOJE
                        $displayShift = $todayShift;
                    }
                }
            } else {
                // Se não tem nada hoje, mostra o próximo
                $showNext = true;
            }

            // 3. Busca o Próximo Turno (se definido pela lógica acima)
            if ($showNext) {
                $displayShift = ScaleShift::where('user_id', Auth::id())
                    ->whereDate('date', '>', $today) // Estritamente maior que hoje
                    ->orderBy('date', 'asc')
                    ->orderBy('order', 'asc')
                    ->first();
            }

            // 4. Lógica da Folga (Se o card principal for Folga, busca quando volta)
            if ($displayShift && $displayShift->name === 'FOLGA') {
                $returnShift = ScaleShift::where('user_id', Auth::id())
                    ->whereDate('date', '>', $displayShift->date) // Depois da folga
                    ->where('name', '!=', 'FOLGA') // Que seja trabalho
                    ->orderBy('date', 'asc')
                    ->orderBy('order', 'asc')
                    ->first();
            }
        }

        return view('dashboard', compact('displayShift', 'returnShift'));
    }
}