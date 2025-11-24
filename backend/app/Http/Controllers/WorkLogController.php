<?php

namespace App\Http\Controllers;

use App\Services\WorkLogService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class WorkLogController extends Controller
{
    protected $workLogService;

    public function __construct(WorkLogService $workLogService)
    {
        $this->workLogService = $workLogService;
    }

    public function index(): JsonResponse
    {
        $logs = $this->workLogService->getAllWorkLogs(auth()->user());
        return response()->json(['data' => $logs]);
    }

    public function store(Request $request, int $fileId): JsonResponse
    {
        $validated = $request->validate([
            'date' => 'required|date',
            'hours_worked' => 'required|numeric|min:0.1',
            'description' => 'required|string',
            'status_update' => 'nullable|string',
            'pending_requirements' => 'nullable|string',
        ]);

        $validated['file_id'] = $fileId;
        $validated['user_id'] = $request->user()->id;

        $workLog = $this->workLogService->createWorkLog($validated);
        return response()->json($workLog, 201);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $validated = $request->validate([
            'date' => 'sometimes|date',
            'hours_worked' => 'sometimes|numeric|min:0.1',
            'description' => 'sometimes|string',
            'status_update' => 'nullable|string',
            'pending_requirements' => 'nullable|string',
        ]);

        $updated = $this->workLogService->updateWorkLog($id, $validated);
        if (!$updated) {
            return response()->json(['message' => 'Work log not found or update failed'], 404);
        }
        return response()->json(['message' => 'Work log updated successfully']);
    }

    public function destroy(int $id): JsonResponse
    {
        $deleted = $this->workLogService->deleteWorkLog($id);
        if (!$deleted) {
            return response()->json(['message' => 'Work log not found'], 404);
        }
        return response()->json(['message' => 'Work log deleted successfully']);
    }

    public function getByFile(int $fileId): JsonResponse
    {
        $logs = $this->workLogService->getWorkLogsByFile($fileId);
        return response()->json($logs);
    }
}
