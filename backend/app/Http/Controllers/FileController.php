<?php

namespace App\Http\Controllers;

use App\Services\FileService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class FileController extends Controller
{
    protected $fileService;

    public function __construct(FileService $fileService)
    {
        $this->fileService = $fileService;
    }

    public function index(Request $request): JsonResponse
    {
        $clientId = $request->query('client_id');

        if ($clientId) {
            $files = $this->fileService->getFilesByClient($clientId, auth()->user());
        } else {
            $files = $this->fileService->getAllFiles(auth()->user());
        }

        return response()->json($files);
    }

    public function show(int $id): JsonResponse
    {
        $file = $this->fileService->getFileById($id);
        if (!$file) {
            return response()->json(['message' => 'File not found'], 404);
        }
        return response()->json($file);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'client_id' => 'required|exists:clients,id',
            'file_type' => 'required|string|max:255',
            'assessment_year' => 'required|string|max:20',
            'financial_year' => 'nullable|string|max:20',
            'turnover' => 'required|string|max:20',
            'status' => 'nullable|string|in:received,assigned,in-progress,ready-to-file,filed,completed',
            'assigned_to' => 'nullable|exists:users,id',
            'estimated_completion_date' => 'required|date',
            'payment_request_date' => 'nullable|date',
            'payment_id' => 'nullable|exists:payments,id',
            'notes' => 'nullable|string',
        ]);

        $file = $this->fileService->createFile($validated);
        return response()->json($file, 201);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $validated = $request->validate([
            'client_id' => 'sometimes|exists:clients,id',
            'file_type' => 'sometimes|string|max:255',
            'assessment_year' => 'sometimes|string|max:20',
            'financial_year' => 'nullable|string|max:20',
            'turnover' => 'sometimes|string|max:20',
            'status' => 'nullable|string|in:received,assigned,in-progress,ready-to-file,filed,completed',
            'assigned_to' => 'nullable|exists:users,id',
            'estimated_completion_date' => 'sometimes|required|date',
            'actual_completion_date' => 'nullable|date',
            'payment_request_date' => 'nullable|date',
            'payment_id' => 'nullable|exists:payments,id',
            'notes' => 'nullable|string',
        ]);

        $updated = $this->fileService->updateFile($id, $validated);
        if (!$updated) {
            return response()->json(['message' => 'File not found or update failed'], 404);
        }
        return response()->json(['message' => 'File updated successfully']);
    }

    public function destroy(int $id): JsonResponse
    {
        $deleted = $this->fileService->deleteFile($id);
        if (!$deleted) {
            return response()->json(['message' => 'File not found'], 404);
        }
        return response()->json(['message' => 'File deleted successfully']);
    }
}
