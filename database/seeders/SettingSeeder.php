<?php

namespace Database\Seeders;

use App\Console\Commands\CrawlDaily;
use App\Models\Setting;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Setting::insert([
            [
                'key'        => CrawlDaily::SETTING_KEY,
                'value'      => '18:00',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
