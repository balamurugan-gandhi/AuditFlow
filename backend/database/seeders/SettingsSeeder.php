<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SettingsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $settings = [
            [
                'key' => 'company_name',
                'value' => 'Your Company Name'
            ],
            [
                'key' => 'company_email',
                'value' => 'info@yourcompany.com'
            ],
            [
                'key' => 'company_phone',
                'value' => '+91-1234567890'
            ],
            [
                'key' => 'company_address',
                'value' => '123 Business Street, City, State, PIN - 123456'
            ],
            [
                'key' => 'company_contact_name',
                'value' => 'John Doe'
            ],
            [
                'key' => 'company_whatsapp',
                'value' => '+91-9876543210'
            ]
        ];

        foreach ($settings as $setting) {
            Setting::updateOrCreate(
                ['key' => $setting['key']],
                ['value' => $setting['value']]
            );
        }
    }
}
