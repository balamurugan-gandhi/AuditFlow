<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class PluginsTableSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('plugins')->insert([
            'id' => 1,
            'name' => 'whatsapp-meta',
            'display_name' => 'WhatsApp Meta API',
            'icon' => 'pi pi-whatsapp',
            'icon_color' => '#25D366',
            'enabled' => 1,
            'created_at' => Carbon::parse('2025-12-24 19:00:58'),
            'updated_at' => Carbon::parse('2025-12-25 16:55:47'),
        ]);
    }
}
