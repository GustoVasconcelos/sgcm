<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Setting;

class SettingsSeeder extends Seeder
{
    public function run()
    {
        Setting::set('log_retention_days', '365');
        Setting::set('log_pagination', '20');
    }
}