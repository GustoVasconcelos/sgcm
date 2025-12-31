<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ScaleShift extends Model
{
    protected $fillable = ['scale_id', 'user_id', 'date', 'name', 'order'];
    protected $casts = ['date' => 'date'];

    public function user() {
        return $this->belongsTo(User::class);
    }
    
    public function scale() {
        return $this->belongsTo(Scale::class);
    }
}