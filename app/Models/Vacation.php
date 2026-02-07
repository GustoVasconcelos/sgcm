<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Vacation extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id', 'year', 'mode', 'status', // 'year' adicionado
        'period_1_start', 'period_1_end',
        'period_2_start', 'period_2_end',
        'period_3_start', 'period_3_end',
    ];

    // Relacionamento: Uma férias pertence a um usuário
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    
    // Método auxiliar para formatar o nome do modo na tela
    public function getModeLabelAttribute()
    {
        return match($this->mode) {
            '30_dias' => '30 Dias Corridos',
            '15_15' => '2 Períodos de 15 Dias',
            '20_10' => '2 Períodos (20 e 10 Dias)', // <--- Mudou aqui (era 10_10_10)
            '20_venda' => '20 Dias + Abono (Venda)',
            default => $this->mode,
        };
    }
}