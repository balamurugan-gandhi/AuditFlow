<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class SettingsController extends Controller
{
    public function index(): JsonResponse
    {
        $settings = Setting::all()->pluck('value', 'key');
        return response()->json($settings);
    }

    public function update(Request $request): JsonResponse
    {
        $data = [];

        // Handle WhatsApp settings from nested 'settings' object
        if ($request->has('settings')) {
            $settings = $request->input('settings');
            foreach ($settings as $key => $value) {
                Setting::updateOrCreate(['key' => $key], ['value' => $value]);
                $data[$key] = $value;
            }
        }

        // Handle company information fields
        $companyFields = [
            'company_name',
            'company_contact_name',
            'company_email',
            'company_phone',
            'company_whatsapp',
            'company_address',
            'gst_number',
            'pan_number',
            'tan_number',
            'license_number'
        ];

        foreach ($companyFields as $field) {
            if ($request->has($field)) {
                Setting::updateOrCreate(['key' => $field], ['value' => $request->input($field)]);
                $data[$field] = $request->input($field);
            }
        }

        // Handle logo upload
        if ($request->hasFile('company_logo')) {
            $file = $request->file('company_logo');
            $filename = time() . '_' . $file->getClientOriginalName();

            $path = $file->storeAs('logos', $filename, 'public');

            Setting::updateOrCreate(['key' => 'company_logo'], ['value' => $path]);
            $data['company_logo'] = $path;
        }

        return response()->json(['message' => 'Settings saved successfully', 'data' => $data]);
    }
}
