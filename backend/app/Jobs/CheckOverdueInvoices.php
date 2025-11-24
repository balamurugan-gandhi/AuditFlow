<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class CheckOverdueInvoices implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $overdueInvoices = \App\Models\Invoice::where('due_date', '<', now()->toDateString())
            ->whereIn('status', ['unpaid', 'partial'])
            ->get();

        foreach ($overdueInvoices as $invoice) {
            $invoice->update(['status' => 'overdue']);
            
            // Optional: Notify admin or client
            // $admin = \App\Models\User::role('admin')->first();
            // if ($admin) {
            //     $admin->notify(new \App\Notifications\InvoiceOverdue($invoice));
            // }
        }
    }
}
