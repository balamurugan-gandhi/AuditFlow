<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Plugin;

class PluginSeeder extends Seeder
{
    public function run()
    {
        Plugin::updateOrCreate(
            ['name' => 'whatsapp-meta'],
            [
                'display_name' => 'WhatsApp Meta API',
                'icon' => 'pi pi-whatsapp',
                'icon_color' => '#25D366',
                'enabled' => true,
            ]
        );
    }
}
