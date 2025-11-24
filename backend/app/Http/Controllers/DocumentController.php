<?php

namespace App\Http\Controllers;

use App\Services\DocumentService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class DocumentController extends Controller
{
    protected $documentService;

    public function __construct(DocumentService $documentService)
    {
        $this->documentService = $documentService;
    }

    public function index(int $fileId): JsonResponse
    {
        $documents = $this->documentService->getDocumentsByFile($fileId);
        return response()->json($documents);
    }

    public function store(Request $request, int $fileId): JsonResponse
    {
        $request->validate([
            'file' => 'required|file|max:10240', // 10MB max
            'type' => 'required|string|max:255',
        ]);

        $document = $this->documentService->uploadDocument(
            $fileId,
            $request->user()->id,
            $request->file('file'),
            $request->input('type')
        );

        return response()->json($document, 201);
    }

    public function download(int $id)
    {
        $document = \App\Models\Document::findOrFail($id);
        
        // Use Laravel's Storage facade
        if (!Storage::exists($document->file_path)) {
            Log::error('File not found for download', [
                'document_id' => $id,
                'file_path' => $document->file_path,
                'storage_path' => storage_path('app/' . $document->file_path)
            ]);
            return response()->json([
                'message' => 'File not found',
                'path' => $document->file_path,
                'expected_location' => storage_path('app/' . $document->file_path)
            ], 404);
        }
        
        return Storage::download($document->file_path, $document->original_name);
    }

    public function destroy(int $id): JsonResponse
    {
        $deleted = $this->documentService->deleteDocument($id);
        if (!$deleted) {
            return response()->json(['message' => 'Document not found'], 404);
        }
        return response()->json(['message' => 'Document deleted successfully']);
    }
}
