<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StudioTimer extends Model
{
    protected $guarded = [];

    protected $casts = [
        'target_time' => 'datetime',
        'bk_target_time' => 'datetime',
        'stopwatch_started_at' => 'datetime',
    ];
}
