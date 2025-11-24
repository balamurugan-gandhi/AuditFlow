<?php

namespace App\Repositories;

use App\Models\WorkLog;
use App\Repositories\Contracts\WorkLogRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class WorkLogRepository implements WorkLogRepositoryInterface
{
    public function create(array $data): WorkLog
    {
        return WorkLog::create($data);
    }

    public function update(int $id, array $data): bool
    {
        $log = WorkLog::find($id);
        if (!$log) {
            return false;
        }
        return $log->update($data);
    }

    public function delete(int $id): bool
    {
        $log = WorkLog::find($id);
        if (!$log) {
            return false;
        }
        return $log->delete();
    }

    public function getByFile(int $fileId): Collection
    {
        return WorkLog::with('user')->where('file_id', $fileId)->orderBy('date', 'desc')->get();
    }

    public function getByEmployee(int $userId): Collection
    {
        return WorkLog::with('file.client')->where('user_id', $userId)->orderBy('date', 'desc')->get();
    }

    public function getAll(\App\Models\User $user): Collection
    {
        $query = WorkLog::with(['file.client', 'user']);

        // Apply RBAC
        if (!$user->hasRole('admin') && !$user->hasRole('manager')) {
            $query->whereHas('file', function ($q) use ($user) {
                $q->whereHas('client', function ($subQ) use ($user) {
                    $subQ->whereHas('users', function ($userQ) use ($user) {
                        $userQ->where('user_id', $user->id);
                    });
                });
            });
        }

        return $query->orderBy('date', 'desc')->get();
    }

    public function allForUser(\App\Models\User $user): Collection
    {
        return $this->getAll($user);
    }
}
