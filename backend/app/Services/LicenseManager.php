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

            // Verify Machine ID if locked
            $licenseMachineHash = $data['machine_id'] ?? null;
            if ($licenseMachineHash) {
                $currentMachineHash = $this->getMachineHash();
                if ($licenseMachineHash !== $currentMachineHash) {
                    $this->error = "License is not valid for this machine.";
                    return;
                }
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
     * Get the machine ID (Raw from mounted secret file).
     */
    public function getMachineId(): string
    {
        // Path to the mounted host machine ID (more secure than .env)
        // In Docker, this should be mapped from the host
        $path = base_path('secrets/machine_id.txt');
        
        if (File::exists($path)) {
            return trim(File::get($path));
        }

        // Fallback for native Windows development (non-docker)
        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            try {
                $command = 'powershell.exe -Command "(Get-ItemProperty -Path HKLM:\SOFTWARE\Microsoft\Cryptography).MachineGuid"';
                $guid = shell_exec($command);
                if ($guid) return trim($guid);
            } catch (Exception $e) {}
        }
        
        return 'unknown';
    }

    /**
     * Get a secure hash of the machine ID.
     * This is what is actually stored in the license file.
     */
    public function getMachineHash(?string $rawId = null): string
    {
        $id = $rawId ?: $this->getMachineId();
        if ($id === 'unknown') return 'unknown';

        // Use APP_KEY as a pepper for the hash to make it unique to this app instance
        $salt = config('app.key');
        return hash_hmac('sha256', $id, $salt);
    }

    /**
     * Get the license machine hash requirement.
     */
    public function getLicenseMachineId(): ?string
    {
        return $this->licenseData['machine_id'] ?? null;
    }

    /**
     * Get the current license error if any.
     */
    public function getError(): ?string
    {
        return $this->error;
    }
}
