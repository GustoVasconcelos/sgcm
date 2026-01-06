<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

class ActionLog extends Model
{
    protected $fillable = ['user_id', 'module', 'action', 'details', 'ip_address'];

    protected $casts = [
        'details' => 'array', // Converte JSON para Array automaticamente
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Método estático para registrar logs de qualquer lugar
    public static function register($module, $action, $details = [])
    {
        if (Auth::check()) {
            self::create([
                'user_id' => Auth::id(),
                'module' => $module,
                'action' => $action,
                'details' => $details,
                'ip_address' => Request::ip()
            ]);
        }
    }
}