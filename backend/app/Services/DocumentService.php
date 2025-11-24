<?php

namespace App\Services;

use App\Repositories\Contracts\DocumentRepositoryInterface;
use App\Models\Document;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class DocumentService
{
    protected $documentRepository;

    public function __construct(DocumentRepositoryInterface $documentRepository)
    {
        $this->documentRepository = $documentRepository;
    }

    public function uploadDocument(int $fileId, int $userId, UploadedFile $file, string $type): Document
    {
        $path = $file->store('documents/' . $fileId);

        return $this->documentRepository->create([
            'file_id' => $fileId,
            'uploaded_by' => $userId,
            'file_path' => $path,
            'original_name' => $file->getClientOriginalName(),
            'mime_type' => $file->getClientMimeType(),
            'size' => $file->getSize(),
            'type' => $type,
        ]);
    }

    public function deleteDocument(int $id): bool
    {
        $document = $this->documentRepository->find($id);
        if (!$document) {
            return false;
        }

        Storage::delete($document->file_path);
        return $this->documentRepository->delete($id);
    }

    public function getDocumentsByFile(int $fileId): Collection
    {
        return $this->documentRepository->getByFile($fileId);
    }
}
