<?php

namespace App\Services;

use App\Repositories\Contracts\WorkLogRepositoryInterface;
use App\Models\WorkLog;
use Illuminate\Database\Eloquent\Collection;

class WorkLogService
{
    protected $workLogRepository;

    public function __construct(WorkLogRepositoryInterface $workLogRepository)
    {
        $this->workLogRepository = $workLogRepository;
    }

    public function createWorkLog(array $data): WorkLog
    {
        return $this->workLogRepository->create($data);
    }

    public function updateWorkLog(int $id, array $data): bool
    {
        return $this->workLogRepository->update($id, $data);
    }

    public function deleteWorkLog(int $id): bool
    {
        return $this->workLogRepository->delete($id);
    }

    public function getWorkLogsByFile(int $fileId): Collection
    {
        return $this->workLogRepository->getByFile($fileId);
    }

    public function getWorkLogsByEmployee(int $userId): Collection
    {
        return $this->workLogRepository->getByEmployee($userId);
    }

    public function getAllWorkLogs(?\App\Models\User $user = null): Collection
    {
        if (!$user) {
            $user = auth()->user();
        }
        return $this->workLogRepository->getAll($user);
    }
}
