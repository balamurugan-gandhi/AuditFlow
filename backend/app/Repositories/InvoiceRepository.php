<?php

namespace App\Repositories;

use App\Models\Invoice;
use App\Repositories\Contracts\InvoiceRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class InvoiceRepository implements InvoiceRepositoryInterface
{
    public function create(array $data): Invoice
    {
        return Invoice::create($data);
    }

    public function update(int $id, array $data): bool
    {
        $invoice = Invoice::find($id);
        if (!$invoice) {
            return false;
        }
        return $invoice->update($data);
    }

    public function find(int $id): ?Invoice
    {
        return Invoice::with(['client', 'payments'])->find($id);
    }

    public function delete(int $id): bool
    {
        $invoice = Invoice::find($id);
        if (!$invoice) {
            return false;
        }
        return $invoice->delete();
    }

    public function getByClient(int $clientId): Collection
    {
        return Invoice::where('client_id', $clientId)->orderBy('invoice_date', 'desc')->get();
    }

    public function getPending(): Collection
    {
        return Invoice::whereIn('status', ['unpaid', 'partial', 'overdue'])->orderBy('due_date', 'asc')->get();
    }

    public function all(): Collection
    {
        return Invoice::with('client')->orderBy('invoice_date', 'desc')->get();
    }

    public function allForUser(\App\Models\User $user): Collection
    {
        if ($user->hasRole('admin') || $user->hasRole('manager')) {
            return $this->all();
        }

        return Invoice::with('client')
            ->whereHas('client', function ($query) use ($user) {
                $query->whereHas('users', function ($q) use ($user) {
                    $q->where('user_id', $user->id);
                });
            })
            ->orderBy('invoice_date', 'desc')
            ->get();
    }
}
