<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Program extends Model
{
    protected $fillable = ['name', 'default_duration', 'color'];

    public function schedules() {
        return $this->hasMany(Schedule::class);
    }
}