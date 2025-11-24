<?php

namespace App\Repositories;

use App\Models\File;
use App\Repositories\Contracts\FileRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class FileRepository implements FileRepositoryInterface
{
    public function all(): Collection
    {
        return File::with(['client', 'assignee'])->get();
    }

    public function allForUser(\App\Models\User $user): Collection
    {
        if ($user->hasRole('admin') || $user->hasRole('manager')) {
            return $this->all();
        }

        // Employees see only files assigned to them
        if ($user->hasRole('employee')) {
            return File::with(['client', 'assignee'])
                ->where('assigned_to', $user->id)
                ->get();
        }

        // Clients see files for their assigned clients
        return File::with(['client', 'assignee'])
            ->whereHas('client', function ($query) use ($user) {
                $query->whereHas('users', function ($q) use ($user) {
                    $q->where('user_id', $user->id);
                });
            })
            ->get();
    }

    public function find(int $id): ?File
    {
        return File::with(['client', 'assignee'])->find($id);
    }
    
    public function create(array $data): File
    {
        return File::create($data);
    }

    public function update(int $id, array $data): bool
    {
        $file = File::find($id);
        if (!$file) {
            return false;
        }
        return $file->update($data);
    }

    public function delete(int $id): bool
    {
        $file = File::find($id);
        if (!$file) {
            return false;
        }
        return $file->delete();
    }

    public function getByEmployee(int $userId): Collection
    {
        return File::with(['client'])->where('assigned_to', $userId)->get();
    }

    public function getStats(?string $assessmentYear, \App\Models\User $user, ?int $employeeId = null): array
    {
        $query = File::query();

        // Apply RBAC
        if ($user->hasRole('admin') || $user->hasRole('manager')) {
            // Admins and managers see all files
            if ($employeeId) {
                $query->where('assigned_to', $employeeId);
            }
        } elseif ($user->hasRole('employee')) {
            // Employees see only files assigned to them
            $query->where('assigned_to', $user->id);
        } else {
            // Clients see files for their assigned clients
            $query->whereHas('client', function ($q) use ($user) {
                $q->whereHas('users', function ($subQ) use ($user) {
                    $subQ->where('user_id', $user->id);
                });
            });
        }

        // Filter by Assessment Year
        if ($assessmentYear) {
            $query->where('assessment_year', $assessmentYear);
        }

        $files = $query->get();
        
        // Total files submitted
        $totalFiles = $files->count();
        
        // Get total clients count for percentage calculation
        if ($user->hasRole('employee')) {
            // For employees, count unique clients from their assigned files
            $totalClients = $files->pluck('client_id')->unique()->count();
        } elseif ($employeeId && ($user->hasRole('admin') || $user->hasRole('manager'))) {
             // If filtering by employee, count unique clients from that employee's assigned files
             $totalClients = $files->pluck('client_id')->unique()->count();
        } elseif (!$user->hasRole('admin') && !$user->hasRole('manager')) {
            $totalClients = \App\Models\Client::whereHas('users', function ($q) use ($user) {
                $q->where('user_id', $user->id);
            })->count();
        } else {
            $totalClients = \App\Models\Client::count();
        }

        return [
            'received' => $totalFiles,  // Total files received
            'total_clients' => $totalClients,  // Total clients for reference
            'total_files' => $totalFiles,  // Same as received
            'pending' => $files->whereIn('status', ['received', 'pending-info'])->count(),  // Unassigned + Pending Info
            'in_progress' => $files->whereIn('status', ['assigned', 'in-progress'])->count(),  // Assigned + In Progress
            'ready_to_file' => $files->where('status', 'ready-to-file')->count(),
            'completed' => $files->whereIn('status', ['completed', 'filed'])->count(),  // Completed + Filed
            'payment_received' => $files->whereNotNull('payment_id')->count(),
        ];
    }

    public function getRecentFiles(int $limit, \App\Models\User $user): Collection
    {
        $query = File::with(['client', 'assignee']);

        // Apply same RBAC as getStats
        if ($user->hasRole('admin') || $user->hasRole('manager')) {
            // Admins and managers see all files
        } elseif ($user->hasRole('employee')) {
            // Employees see only files assigned to them
            $query->where('assigned_to', $user->id);
        } else {
            // Clients see files for their assigned clients
            $query->whereHas('client', function ($q) use ($user) {
                $q->whereHas('users', function ($subQ) use ($user) {
                    $subQ->where('user_id', $user->id);
                });
            });
        }

        return $query->orderBy('created_at', 'desc')->limit($limit)->get();
    }
}
