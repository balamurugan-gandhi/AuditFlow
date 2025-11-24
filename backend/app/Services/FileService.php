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
        return $this->fileRepository->create($data);
    }

    public function updateFile(int $id, array $data): bool
    {
        return $this->fileRepository->update($id, $data);
    }

    public function deleteFile(int $id): bool
    {
        return $this->fileRepository->delete($id);
    }

    public function getFilesByEmployee(int $userId): Collection
    {
        return $this->fileRepository->getByEmployee($userId);
    }

    public function getDashboardStats(?string $assessmentYear, \App\Models\User $user): array
    {
        return $this->fileRepository->getStats($assessmentYear, $user);
    }
}
