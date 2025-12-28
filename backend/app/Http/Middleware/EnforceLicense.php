<?php

namespace App\Http\Middleware;

use App\Services\LicenseManager;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnforceLicense
{
    protected $license;

    public function __construct(LicenseManager $license)
    {
        $this->license = $license;
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // If license is invalid, only allow access to specific safe routes
        if (!$this->license->isValid()) {
            
            // Define list of "safe" routes that are always accessible even with invalid license
            $safeRoutes = [
                'dashboard/stats',  // Needed to show license status on home page
                'settings',         // Needed to see license hash and owner
                'logout',
                'user'
            ];

            $currentRoute = $request->path();
            
            // Check if requested route is NOT in safe list
            $isSafe = false;
            foreach ($safeRoutes as $route) {
                if (str_starts_with(ltrim($currentRoute, 'api/'), $route)) {
                    $isSafe = true;
                    break;
                }
            }

            if (!$isSafe) {
                return response()->json([
                    'message' => 'Application license is invalid or expired. Please contact Klock Technologies.',
                    'license_error' => $this->license->getError(),
                    'is_valid' => false
                ], 402); // Payment Required status code is often used for licensing
            }
        }

        return $next($request);
    }
}
