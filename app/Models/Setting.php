<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use Illuminate\Support\Facades\Cache;

class Setting extends Model
{
    protected $fillable = ['key', 'value'];

    protected static function booted()
    {
        // Limpa o cache sempre que um setting for salvo ou deletado
        static::saved(function ($setting) {
            Cache::forget('setting_' . $setting->key);
        });

        static::deleted(function ($setting) {
            Cache::forget('setting_' . $setting->key);
        });
    }

    // Método estático para buscar valor rápido: Setting::get('chave', 'padrao')
    public static function get($key, $default = null)
    {
        return Cache::rememberForever('setting_' . $key, function () use ($key, $default) {
            $setting = self::where('key', $key)->first();
            return $setting ? $setting->value : $default;
        });
    }

    // Método estático para salvar rápido: Setting::set('chave', 'valor')
    public static function set($key, $value)
    {
        // O updateOrCreate disparará o evento 'saved', que limpará o cache antigo
        return self::updateOrCreate(
            ['key' => $key],
            ['value' => $value]
        );
    }
}