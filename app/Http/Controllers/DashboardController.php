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
        $todayShift = null;
        $nextWorkShift = null;
        
        if (Auth::user()->is_operator) {
            // 1. Busca o que tem para HOJE (Pode ser Folga ou Trabalho)
            $todayShift = ScaleShift::where('user_id', Auth::id())
                ->whereDate('date', Carbon::today())
                ->first();

            // 2. Busca o próximo turno de TRABALHO REAL (Ignora Folgas)
            // Se hoje for trabalho, esta query vai retornar hoje mesmo (o que é correto)
            // Se hoje for folga, ela vai pular hoje e pegar o próximo
            $nextWorkShift = ScaleShift::where('user_id', Auth::id())
                ->whereDate('date', '>=', Carbon::today()) // A partir de hoje
                ->where('name', '!=', 'FOLGA') // Ignora registros de Folga
                ->orderBy('date', 'asc')
                ->orderBy('order', 'asc')
                ->first();
        }

        return view('dashboard', compact('todayShift', 'nextWorkShift'));
    }
}