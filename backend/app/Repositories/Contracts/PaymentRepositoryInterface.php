<?php

namespace App\Repositories\Contracts;

use App\Models\Payment;
use Illuminate\Database\Eloquent\Collection;

interface PaymentRepositoryInterface
{
    public function create(array $data): Payment;
    public function getByInvoice(int $invoiceId): Collection;
}
