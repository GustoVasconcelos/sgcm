<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\ScaleShift; // Certifique-se de importar o Model
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        $nextShift = null;
        
        // Só busca turno se NÃO for admin
        if (Auth::user()->profile != 'admin') {
            $nextShift = ScaleShift::where('user_id', Auth::id())
                ->whereDate('date', '>=', Carbon::today())
                ->orderBy('date', 'asc')
                ->orderBy('order', 'asc')
                ->first();
        }

        return view('dashboard', compact('nextShift'));
    }
}