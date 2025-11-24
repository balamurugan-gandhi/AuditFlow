<?php

namespace App\Repositories\Contracts;

use App\Models\Invoice;
use Illuminate\Database\Eloquent\Collection;

interface InvoiceRepositoryInterface
{
    public function create(array $data): Invoice;
    public function update(int $id, array $data): bool;
    public function find(int $id): ?Invoice;
    public function delete(int $id): bool;
    public function getByClient(int $clientId): Collection;
    public function getPending(): Collection;
    public function all(): Collection;
    public function allForUser(\App\Models\User $user): Collection;
}
