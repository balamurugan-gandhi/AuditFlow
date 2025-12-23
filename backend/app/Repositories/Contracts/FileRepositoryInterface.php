<?php

namespace App\Repositories\Contracts;

use App\Models\File;
use Illuminate\Database\Eloquent\Collection;

interface FileRepositoryInterface
{
    public function all(): Collection;
    public function allForUser(\App\Models\User $user): Collection;
    public function find(int $id): ?File;
    public function create(array $data): File;
    public function update(int $id, array $data): bool;
    public function delete(int $id): bool;
    public function getByEmployee(int $userId): Collection;
    public function getByClient(int $clientId, ?\App\Models\User $user = null): Collection;
    public function getStats(?string $assessmentYear, \App\Models\User $user, ?int $employeeId = null, ?string $timePeriod = null): array;
}
