<?php

namespace App\Services;

use Exception;
use Illuminate\Support\Facades\File;
use Carbon\Carbon;

class LicenseManager
{
    protected $licenseData = null;
    protected $isValid = false;
    protected $error = null;

    public function __construct()
    {
        $this->boot();
    }

    /**
     * Load and verify the license file.
     */
    protected function boot()
    {
        $path = config('license.license_path');
        $publicKey = config('license.public_key');

        if (!File::exists($path)) {
            $this->error = "License file missing.";
            return;
        }

        try {
            $content = File::get($path);
            $data = json_decode($content, true);

            if (!$data || !isset($data['signature'])) {
                $this->error = "Invalid license format.";
                return;
            }

            $signature = base64_decode($data['signature']);
            unset($data['signature']);
            
            // Sort keys to ensure consistency with the generator script
            ksort($data);
            
            // Re-encode data to ensure signature is verified against the exact payload
            $payload = json_encode($data, JSON_UNESCAPED_SLASHES);

            // Verify the signature
            $verification = openssl_verify(
                $payload,
                $signature,
                $publicKey,
                OPENSSL_ALGO_SHA256
            );

            if ($verification !== 1) {
                $this->error = "License signature verification failed.";
                return;
            }

            // Check expiration
            $expires = Carbon::parse($data['expires']);
            if ($expires->isPast()) {
                $this->error = "License expired on " . $expires->toDateString();
                return;
            }

            $this->licenseData = $data;
            $this->isValid = true;
        } catch (Exception $e) {
            $this->error = "License processing error: " . $e->getMessage();
        }
    }

    /**
     * Check if the application has a valid license.
     */
    public function isValid(): bool
    {
        return $this->isValid;
    }

    /**
     * Check if a specific feature is allowed by the license.
     */
    public function allows(string $feature): bool
    {
        if (!$this->isValid) {
            return false;
        }

        $features = $this->licenseData['features'] ?? [];
        return in_array($feature, $features) || in_array('*', $features);
    }

    /**
     * Get the license owner name.
     */
    public function issuedTo(): ?string
    {
        return $this->licenseData['issued_to'] ?? null;
    }

    /**
     * Get the expiration date.
     */
    public function expiresAt(): ?string
    {
        return $this->licenseData['expires'] ?? null;
    }

    /**
     * Get the license ID.
     */
    public function getLicenseId(): ?string
    {
        return $this->licenseData['license_id'] ?? null;
    }

    /**
     * Get the allowed features.
     */
    public function getFeatures(): array
    {
        return $this->licenseData['features'] ?? [];
    }

    /**
     * Get the current license error if any.
     */
    public function getError(): ?string
    {
        return $this->error;
    }
}
