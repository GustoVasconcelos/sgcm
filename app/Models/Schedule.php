<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Schedule extends Model
{
    protected $fillable = [
        'program_id', 'date', 'start_time', 'duration',
        'custom_info', 'status_mago', 'status_verification', 'notes'
    ];

    protected $casts = [
        'date' => 'date',
        'status_mago' => 'boolean',
        'status_verification' => 'boolean',
    ];

    public function program() {
        return $this->belongsTo(Program::class);
    }
    
    // Calcula hora final para mostrar na tela
    public function getEndTimeAttribute() {
        return Carbon::parse($this->start_time)->addMinutes($this->duration)->format('H:i');
    }
}