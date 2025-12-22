<?php

namespace App\Services;

use App\Repositories\Contracts\FileRepositoryInterface;
use App\Models\File;
use Illuminate\Database\Eloquent\Collection;

class FileService
{
    protected $fileRepository;

    public function __construct(FileRepositoryInterface $fileRepository)
    {
        $this->fileRepository = $fileRepository;
    }

    public function getAllFiles(?\App\Models\User $user = null): Collection
    {
        if ($user) {
            return $this->fileRepository->allForUser($user);
        }
        return $this->fileRepository->all();
    }

    public function getFileById(int $id): ?File
    {
        return $this->fileRepository->find($id);
    }

    public function createFile(array $data): File
    {
        $file = $this->fileRepository->create($data);

        // Notify client if file is received
        $client = $file->client;
        if ($client) {
            $client->notify(new \App\Notifications\FileReceived($file));
        }

        return $file;
    }

    public function updateFile(int $id, array $data): bool
    {
        $updated = $this->fileRepository->update($id, $data);

        if ($updated && isset($data['status'])) {
            $file = $this->fileRepository->find($id);
            $status = $data['status'];

            // Notify client for specific statuses
            if (in_array($status, ['completed', 'filed'])) {
                $client = $file->client;
                if ($client) {
                    $client->notify(new \App\Notifications\FileStatusUpdated($file, $status));
                }
            }
        }

        return $updated;
    }

    public function deleteFile(int $id): bool
    {
        return $this->fileRepository->delete($id);
    }

    public function getFilesByEmployee(int $userId): Collection
    {
        return $this->fileRepository->getByEmployee($userId);
    }

    public function getDashboardStats(?string $assessmentYear, \App\Models\User $user, ?int $employeeId = null): array
    {
        return $this->fileRepository->getStats($assessmentYear, $user, $employeeId);
    }
}
