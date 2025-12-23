<?php

namespace App\Http\Controllers;

use App\Services\FileService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class DashboardController extends Controller
{
    protected $fileService;

    public function __construct(FileService $fileService)
    {
        $this->fileService = $fileService;
    }

    public function stats(Request $request): JsonResponse
    {
        $assessmentYear = $request->input('assessment_year');
        $employeeId = $request->input('employee_id');
        $timePeriod = $request->input('time_period');
        $user = $request->user();

        $stats = $this->fileService->getDashboardStats($assessmentYear, $user, $employeeId, $timePeriod);

        return response()->json($stats);
    }
}
