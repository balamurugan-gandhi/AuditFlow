<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\LicenseService;

class LicenseController extends Controller
{
    protected $licenseService;

    public function __construct(LicenseService $licenseService)
    {
        $this->licenseService = $licenseService;
    }

    public function status()
    {
        return response()->json($this->licenseService->getLicenseInfo());
    }

    public function update(Request $request)
    {
        $request->validate([
            'license_key' => 'required|string'
        ]);

        $success = $this->licenseService->saveLicenseKey($request->license_key);

        if ($success) {
            return response()->json([
                'message' => 'License updated successfully.',
                'license' => $this->licenseService->getLicenseInfo()
            ]);
        }

        return response()->json([
            'message' => 'Invalid license key.'
        ], 422);
    }

    public function generate(Request $request)
    {
        // This should probably be protected or removed in production, 
        // but for now, we'll allow it for setup purposes.
        $years = $request->get('years', 2);
        $key = $this->licenseService->generateLicenseKey($years);
        
        return response()->json([
            'license_key' => $key,
            'message' => 'Generated a ' . $years . ' year license key.'
        ]);
    }
}
