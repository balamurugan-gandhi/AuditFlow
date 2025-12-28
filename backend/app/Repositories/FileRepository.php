<?php

namespace App\Repositories;

use App\Models\File;
use App\Repositories\Contracts\FileRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class FileRepository implements FileRepositoryInterface
{
    protected $license;

    public function __construct(\App\Services\LicenseManager $license)
    {
        $this->license = $license;
    }

    protected function enforceLicense()
    {
        if (!$this->license->isValid()) {
            throw new \Exception("Core data access denied: Invalid License.");
        }
    }

    public function all(): Collection
    {
        $this->enforceLicense();
        return File::with(['client', 'assignee'])->get();
    }

    public function allForUser(\App\Models\User $user): Collection
    {
        $this->enforceLicense();
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
        $this->enforceLicense();
        return File::with(['client', 'assignee'])->find($id);
    }

    public function create(array $data): File
    {
        $this->enforceLicense();
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

    public function getByClient(int $clientId, ?\App\Models\User $user = null): Collection
    {
        $query = File::with(['client', 'assignee'])->where('client_id', $clientId);

        // Apply user permissions if provided
        if ($user) {
            if ($user->hasRole('admin') || $user->hasRole('manager')) {
                // Admins and managers see all files for the client
            } elseif ($user->hasRole('employee')) {
                // Employees see only files assigned to them for this client
                $query->where('assigned_to', $user->id);
            } else {
                // Clients see files for their assigned clients (should already be filtered by client_id)
            }
        }

        return $query->get();
    }

    public function getStats(?string $assessmentYear, \App\Models\User $user, ?int $employeeId = null, ?string $timePeriod = null): array
    {
        $this->enforceLicense();
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

        // Filter by Time Period
        if ($timePeriod) {
            $now = now();
            switch ($timePeriod) {
                case '24h':
                    $query->where('created_at', '>=', $now->subHours(24));
                    break;
                case '7d':
                    $query->where('created_at', '>=', $now->subDays(7));
                    break;
                case '30d':
                    $query->where('created_at', '>=', $now->subDays(30));
                    break;
            }
        }

        // Use conditional aggregation for performance
        $stats = $query->toBase()->selectRaw("
            count(*) as total,
            sum(case when status = 'received' then 1 else 0 end) as received_status,
            sum(case when status in ('assigned', 'in-progress') then 1 else 0 end) as in_progress,
            sum(case when status = 'ready-to-file' then 1 else 0 end) as ready_to_file,
            sum(case when status in ('completed', 'filed') then 1 else 0 end) as completed,
            sum(case when payment_id is not null then 1 else 0 end) as payment_received
        ")->first();

        // Get total clients count
        $totalClients = 0;
        if ($user->hasRole('employee')) {
            // For employees, count unique clients from their assigned files
            // We can reuse the query conditions but we need a fresh query builder or clone
            $clientQuery = clone $query;
            $totalClients = $clientQuery->distinct('client_id')->count('client_id');
        } elseif ($employeeId && ($user->hasRole('admin') || $user->hasRole('manager'))) {
             // If filtering by employee, count unique clients from that employee's assigned files
             $clientQuery = clone $query;
             $totalClients = $clientQuery->distinct('client_id')->count('client_id');
        } elseif (!$user->hasRole('admin') && !$user->hasRole('manager')) {
            $totalClients = \App\Models\Client::whereHas('users', function ($q) use ($user) {
                $q->where('user_id', $user->id);
            })->count();
        } else {
            $totalClients = \App\Models\Client::count();
        }

        return [
            'received' => $stats->total ?? 0,
            'total_clients' => $totalClients,
            'total_files' => $stats->total ?? 0,
            'unreceived' => $totalClients - ($stats->total ?? 0),
            'pending' => ($stats->total ?? 0) - ($stats->completed ?? 0),
            'in_progress' => $stats->in_progress ?? 0,
            'ready_to_file' => $stats->ready_to_file ?? 0,
            'completed' => $stats->completed ?? 0,
            'payment_received' => $stats->payment_received ?? 0,
        ];
    }

    public function getRecentFiles(int $limit, \App\Models\User $user): Collection
    {
        $this->enforceLicense();
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
