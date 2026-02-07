<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Program extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'default_duration', 'color'];

    public function schedules() {
        return $this->hasMany(Schedule::class);
    }
}