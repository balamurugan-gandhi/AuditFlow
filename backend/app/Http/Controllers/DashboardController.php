<?php

namespace App\Http\Controllers;

use App\Services\FileService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class DashboardController extends Controller
{
    protected $fileService;
    protected $license;

    public function __construct(FileService $fileService, \App\Services\LicenseManager $license)
    {
        $this->fileService = $fileService;
        $this->license = $license;
    }

    public function stats(Request $request): JsonResponse
    {
        $assessmentYear = $request->input('assessment_year');
        $employeeId = $request->input('employee_id');
        $timePeriod = $request->input('time_period');
        $user = $request->user();

        $stats = $this->fileService->getDashboardStats($assessmentYear, $user, $employeeId, $timePeriod);
        
        // Include license info in basic responses to allow UI feedback
        $stats['license'] = [
            'is_valid' => $this->license->isValid(),
            'issued_to' => $this->license->issuedTo(),
            'expires_at' => $this->license->expiresAt(),
            'error' => $this->license->getError(),
        ];

        return response()->json($stats);
    }
}
