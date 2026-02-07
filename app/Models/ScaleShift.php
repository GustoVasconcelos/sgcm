<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ScaleShift extends Model
{
    use HasFactory, SoftDeletes;
    
    protected $fillable = ['scale_id', 'user_id', 'date', 'name', 'order'];
    protected $casts = ['date' => 'date'];

    public function user() {
        return $this->belongsTo(User::class);
    }
}