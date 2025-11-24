<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Invoice;
use App\Models\Client;
use App\Models\Payment;

use Illuminate\Support\Facades\DB;

class InvoiceSeeder extends Seeder
{
    public function run(): void
    {
        // Truncate tables to avoid duplicates
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        Invoice::truncate();
        Payment::truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $clients = Client::all();
        
        if ($clients->isEmpty()) {
            $this->command->warn('No clients found. Please run ClientSeeder first.');
            return;
        }

        $statuses = ['draft', 'sent', 'paid', 'overdue', 'cancelled'];
        $currentYear = date('Y');
        $invoiceNumber = 1000;

        // Create 35 invoices
        for ($i = 0; $i < 35; $i++) {
            $client = $clients->random();
            $status = $statuses[array_rand($statuses)];
            
            // Random date in the last 6 months
            $invoiceDate = date('Y-m-d', strtotime("-" . rand(0, 180) . " days"));
            $dueDate = date('Y-m-d', strtotime($invoiceDate . " + 30 days"));
            
            // Random amounts
            $totalAmount = rand(5000, 50000);
            $taxAmount = round($totalAmount * 0.18, 2); // 18% GST
            
            $invoice = Invoice::create([
                'client_id' => $client->id,
                'invoice_number' => 'INV-' . $currentYear . '-' . str_pad($invoiceNumber++, 4, '0', STR_PAD_LEFT),
                'invoice_date' => $invoiceDate,
                'due_date' => $dueDate,
                'total_amount' => $totalAmount,
                'tax_amount' => $taxAmount,
                'status' => $status,
                'notes' => $this->generateNotes($status),
            ]);

            // Create payment for paid invoices
            if ($status === 'paid') {
                $payment = Payment::create([
                    'invoice_id' => $invoice->id,
                    'amount' => $totalAmount + $taxAmount,
                    'payment_date' => date('Y-m-d', strtotime($dueDate . " - " . rand(1, 15) . " days")),
                    'payment_method' => ['bank_transfer', 'cash', 'cheque', 'upi'][array_rand(['bank_transfer', 'cash', 'cheque', 'upi'])],
                    'transaction_reference' => 'PAY-' . strtoupper(substr(md5(rand()), 0, 8)),
                    'notes' => 'Payment received for ' . $invoice->invoice_number,
                ]);

                // Link to a random file for this client
                $file = \App\Models\File::where('client_id', $client->id)->inRandomOrder()->first();
                if ($file) {
                    $file->update(['payment_id' => $payment->id]);
                }
            }
            
            // Create partial payment for some sent invoices
            if ($status === 'sent' && rand(0, 100) < 30) {
                $partialAmount = round(($totalAmount + $taxAmount) * (rand(30, 70) / 100), 2);
                $payment = Payment::create([
                    'invoice_id' => $invoice->id,
                    'amount' => $partialAmount,
                    'payment_date' => date('Y-m-d', strtotime($invoiceDate . " + " . rand(5, 25) . " days")),
                    'payment_method' => ['bank_transfer', 'cash', 'upi'][array_rand(['bank_transfer', 'cash', 'upi'])],
                    'transaction_reference' => 'PAY-' . strtoupper(substr(md5(rand()), 0, 8)),
                    'notes' => 'Partial payment for ' . $invoice->invoice_number,
                ]);

                // Link to a random file for this client if not already linked
                $file = \App\Models\File::where('client_id', $client->id)->whereNull('payment_id')->inRandomOrder()->first();
                if ($file) {
                    $file->update(['payment_id' => $payment->id]);
                }
            }
        }

        $this->command->info('Created 35 invoices with payments');
    }

    private function generateNotes(string $status): string
    {
        $notes = [
            'draft' => 'Invoice being prepared',
            'sent' => 'Invoice sent to client via email',
            'paid' => 'Payment received and verified',
            'overdue' => 'Payment overdue - follow up required',
            'cancelled' => 'Invoice cancelled - service not provided',
        ];

        return $notes[$status] ?? '';
    }
}
