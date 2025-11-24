<?php

namespace App\Repositories\Contracts;

use App\Models\Document;
use Illuminate\Database\Eloquent\Collection;

interface DocumentRepositoryInterface
{
    public function create(array $data): Document;
    public function delete(int $id): bool;
    public function find(int $id): ?Document;
    public function getByFile(int $fileId): Collection;
}
