<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Scale extends Model
{
    protected $fillable = ['start_date', 'end_date', 'type', 'is_published'];
    
    // Converte datas automaticamente para objeto Carbon
    protected $casts = ['start_date' => 'date', 'end_date' => 'date'];

    public function shifts() {
        return $this->hasMany(ScaleShift::class)->orderBy('date')->orderBy('order');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}