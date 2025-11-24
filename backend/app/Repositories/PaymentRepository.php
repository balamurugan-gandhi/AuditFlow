<?php

namespace App\Repositories;

use App\Models\Payment;
use App\Repositories\Contracts\PaymentRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class PaymentRepository implements PaymentRepositoryInterface
{
    public function create(array $data): Payment
    {
        return Payment::create($data);
    }

    public function getByInvoice(int $invoiceId): Collection
    {
        return Payment::where('invoice_id', $invoiceId)->orderBy('payment_date', 'desc')->get();
    }
}
