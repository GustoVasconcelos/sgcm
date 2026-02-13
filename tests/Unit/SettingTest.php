<?php

namespace Tests\Unit;

use App\Models\Setting;
use Illuminate\Support\Facades\Cache;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SettingTest extends TestCase
{
    use RefreshDatabase;

    public function test_get_returns_stored_value(): void
    {
        Setting::create(['key' => 'foo', 'value' => 'bar']);

        $this->assertEquals('bar', Setting::get('foo'));
    }

    public function test_get_returns_default_if_not_exists(): void
    {
        $this->assertEquals('default', Setting::get('non_existent', 'default'));
    }

    public function test_get_stores_value_in_cache(): void
    {
        Setting::create(['key' => 'cache_test', 'value' => 'cached']);

        // Primeira chamada (vai para o banco e cacheia)
        Setting::get('cache_test');

        $this->assertTrue(Cache::has('setting_cache_test'));
        $this->assertEquals('cached', Cache::get('setting_cache_test'));
    }

    public function test_set_updates_value_and_clears_cache(): void
    {
        // 1. Cria e cacheia
        Setting::set('foo', 'initial');
        $this->assertEquals('initial', Setting::get('foo'));
        $this->assertTrue(Cache::has('setting_foo'));

        // 2. Atualiza via set
        Setting::set('foo', 'updated');

        // Como usamos "saved" event que limpa a chave,
        // a chave deve ter sido invalidada OU (se o set chamasse o get logo depois, estaria atualizada).
        // Na nossa impl, o evento limpa. O código de teste verifica se limpou.
        $this->assertFalse(Cache::has('setting_foo'));

        // 3. Lê de novo (recacheia com novo valor)
        $this->assertEquals('updated', Setting::get('foo'));
        $this->assertTrue(Cache::has('setting_foo'));
    }

    public function test_direct_model_update_clears_cache(): void
    {
        Setting::set('bar', 'val1');
        Setting::get('bar'); // Cacheia
        $this->assertTrue(Cache::has('setting_bar'));

        // Atualiza direto no eloquent
        $setting = Setting::where('key', 'bar')->first();
        $setting->update(['value' => 'val2']);

        // Deve ter limpado
        $this->assertFalse(Cache::has('setting_bar'));

        $this->assertEquals('val2', Setting::get('bar'));
    }
}
