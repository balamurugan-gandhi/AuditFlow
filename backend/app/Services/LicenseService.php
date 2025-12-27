<?php

namespace App\Services;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Crypt;
use Carbon\Carbon;

class LicenseService
{
    protected $licensePath;

    public function __construct()
    {
        // We will store the license in a path that should be mounted as a volume
        $this->licensePath = base_path('license/license.key');
    }

    public function getLicenseInfo()
    {
        if (!File::exists($this->licensePath)) {
            // Check if an initial key is provided in ENV
            $initialKey = config('app.initial_license_key');
            
            if ($initialKey) {
                $this->saveLicenseKey($initialKey);
            } else {
                // Auto-generate a 2-year trial license for new installations if requested
                if (config('app.auto_generate_license', true)) {
                    $key = $this->generateLicenseKey(2);
                    $this->saveLicenseKey($key);
                } else {
                    return [
                        'status' => 'missing',
                        'message' => 'License key is missing.',
                        'expires_at' => null,
                        'is_valid' => false
                    ];
                }
            }
        }

        try {
            $encryptedKey = File::get($this->licensePath);
            $data = Crypt::decrypt($encryptedKey);
            $data = json_decode($data, true);

            $expiresAt = Carbon::parse($data['expires_at']);
            $isValid = $expiresAt->isFuture();

            return [
                'status' => $isValid ? 'active' : 'expired',
                'message' => $isValid ? 'License is active.' : 'License has expired.',
                'expires_at' => $expiresAt->toDateTimeString(),
                'is_valid' => $isValid,
                'remaining_days' => Carbon::now()->diffInDays($expiresAt, false)
            ];
        } catch (\Exception $e) {
            return [
                'status' => 'invalid',
                'message' => 'Invalid license key.',
                'expires_at' => null,
                'is_valid' => false
            ];
        }
    }

    public function saveLicenseKey($key)
    {
        try {
            // Validate the key before saving
            $data = Crypt::decrypt($key);
            json_decode($data, true);

            // Ensure directory exists
            if (!File::isDirectory(dirname($this->licensePath))) {
                File::makeDirectory(dirname($this->licensePath), 0755, true);
            }

            File::put($this->licensePath, $key);
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    public function generateLicenseKey($years = 2)
    {
        $data = [
            'issued_at' => Carbon::now()->toDateTimeString(),
            'expires_at' => Carbon::now()->addYears($years)->toDateTimeString(),
            'identifier' => bin2hex(random_bytes(16))
        ];

        return Crypt::encrypt(json_encode($data));
    }
}
