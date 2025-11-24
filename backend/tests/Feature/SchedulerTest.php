<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Client;
use App\Models\Invoice;
use Spatie\Permission\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Jobs\CheckOverdueInvoices;

class SchedulerTest extends TestCase
{
    use RefreshDatabase;

    public function test_check_overdue_invoices_job_updates_status()
    {
        $client = Client::create([
            'business_name' => 'Test Client',
            'email' => 'client@test.com'
        ]);

        $invoice = Invoice::create([
            'client_id' => $client->id,
            'invoice_number' => 'INV-OLD',
            'invoice_date' => now()->subDays(30),
            'due_date' => now()->subDays(1), // Overdue
            'total_amount' => 1000.00,
            'status' => 'unpaid',
        ]);

        $notOverdueInvoice = Invoice::create([
            'client_id' => $client->id,
            'invoice_number' => 'INV-NEW',
            'invoice_date' => now(),
            'due_date' => now()->addDays(15),
            'total_amount' => 1000.00,
            'status' => 'unpaid',
        ]);

        $job = new CheckOverdueInvoices();
        $job->handle();

        $this->assertDatabaseHas('invoices', [
            'id' => $invoice->id,
            'status' => 'overdue',
        ]);

        $this->assertDatabaseHas('invoices', [
            'id' => $notOverdueInvoice->id,
            'status' => 'unpaid',
        ]);
    }
}
