<?php

namespace App\Repositories\Contracts;

use App\Models\WorkLog;
use Illuminate\Database\Eloquent\Collection;

interface WorkLogRepositoryInterface
{
    public function create(array $data): WorkLog;
    public function update(int $id, array $data): bool;
    public function delete(int $id): bool;
    public function getByFile(int $fileId): Collection;
    public function getByEmployee(int $userId): Collection;
    public function getAll(\App\Models\User $user): Collection;
    public function allForUser(\App\Models\User $user): Collection;
}
