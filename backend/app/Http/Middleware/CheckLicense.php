<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Services\LicenseService;
use Symfony\Component\HttpFoundation\Response;

class CheckLicense
{
    protected $licenseService;

    public function __construct(LicenseService $licenseService)
    {
        $this->licenseService = $licenseService;
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Skip license check for license management routes to allow updating the key
        if ($request->is('api/license*') || $request->is('api/settings*')) {
            return $next($request);
        }

        $license = $this->licenseService->getLicenseInfo();

        if (!$license['is_valid']) {
            return response()->json([
                'error' => 'License Expired or Invalid',
                'message' => $license['message'],
                'license_status' => $license['status']
            ], 402); // 402 Payment Required is suitable for license issues
        }

        return $next($request);
    }
}
