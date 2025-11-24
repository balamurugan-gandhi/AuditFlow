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
        $user = $request->user();

        $stats = $this->fileService->getDashboardStats($assessmentYear, $user);

        return response()->json($stats);
    }
}
