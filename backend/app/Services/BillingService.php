<?php

namespace App\Services;

use App\Repositories\Contracts\InvoiceRepositoryInterface;
use App\Repositories\Contracts\PaymentRepositoryInterface;
use App\Models\Invoice;
use App\Models\Payment;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class BillingService
{
    protected $invoiceRepository;
    protected $paymentRepository;

    public function __construct(
        InvoiceRepositoryInterface $invoiceRepository,
        PaymentRepositoryInterface $paymentRepository
    ) {
        $this->invoiceRepository = $invoiceRepository;
        $this->paymentRepository = $paymentRepository;
    }

    public function createInvoice(array $data): Invoice
    {
        $invoice = $this->invoiceRepository->create($data);
        $invoice->refresh();

        // Notify client
        $client = $invoice->client;
        if ($client) {
            $client->notify(new \App\Notifications\InvoiceGenerated($invoice));
        }

        // Notify admin (for demo purposes)
        $admin = \App\Models\User::role('admin')->first();
        if ($admin) {
            // $admin->notify(new \App\Notifications\InvoiceGenerated($invoice));
        }

        return $invoice;
    }

    public function getInvoice(int $id): ?Invoice
    {
        return $this->invoiceRepository->find($id);
    }

    public function getInvoicesByClient(int $clientId): Collection
    {
        return $this->invoiceRepository->getByClient($clientId);
    }

    public function getInvoicesForUser(\App\Models\User $user): Collection
    {
        if ($user->hasRole('admin') || $user->hasRole('manager')) {
            return $this->invoiceRepository->all();
        }
        return $this->invoiceRepository->allForUser($user);
    }

    public function recordPayment(int $invoiceId, array $data): Payment
    {
        return DB::transaction(function () use ($invoiceId, $data) {
            $data['invoice_id'] = $invoiceId;
            $payment = $this->paymentRepository->create($data);

            // Update invoice status
            $invoice = $this->invoiceRepository->find($invoiceId);
            $totalPaid = $invoice->payments->sum('amount');
            $totalDue = $invoice->total_amount; // total_amount already includes tax

            if ($totalPaid >= $totalDue) {
                $this->invoiceRepository->update($invoiceId, ['status' => 'paid']);
            } elseif ($totalPaid > 0) {
                $this->invoiceRepository->update($invoiceId, ['status' => 'partial']);
            }

            // Notify client
            $client = $invoice->client;
            if ($client) {
                $client->notify(new \App\Notifications\PaymentReceived($payment, $invoice));
            }

            // Notify admin
            $admin = \App\Models\User::role('admin')->first();
            if ($admin) {
                //$admin->notify(new \App\Notifications\PaymentReceived($payment, $invoice));
            }

            return $payment;
        });
    }
}
