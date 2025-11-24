<?php

namespace App\Repositories;

use App\Models\Document;
use App\Repositories\Contracts\DocumentRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class DocumentRepository implements DocumentRepositoryInterface
{
    public function create(array $data): Document
    {
        return Document::create($data);
    }

    public function delete(int $id): bool
    {
        $document = Document::find($id);
        if (!$document) {
            return false;
        }
        return $document->delete();
    }

    public function find(int $id): ?Document
    {
        return Document::find($id);
    }

    public function getByFile(int $fileId): Collection
    {
        return Document::where('file_id', $fileId)->orderBy('created_at', 'desc')->get();
    }
}
