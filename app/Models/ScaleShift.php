<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ScaleShift extends Model
{
    use HasFactory;
    
    protected $fillable = ['scale_id', 'user_id', 'date', 'name', 'order'];
    protected $casts = ['date' => 'date'];

    public function user() {
        return $this->belongsTo(User::class);
    }
}