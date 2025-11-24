<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Client;
use App\Models\Invoice;
use Spatie\Permission\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;

class BillingTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_create_invoice()
    {
        $role = Role::firstOrCreate(['name' => 'admin']);
        $admin = User::factory()->create();
        $admin->assignRole($role);

        $client = Client::create([
            'business_name' => 'Test Client',
            'email' => 'client@test.com'
        ]);
        
        
        $response = $this->actingAs($admin)->postJson('/api/invoices', [
            'client_id' => $client->id,
            'invoice_number' => 'INV-001',
            'invoice_date' => '2024-01-01',
            'due_date' => '2024-01-15',
            'total_amount' => 1000.00,
            'tax_amount' => 180.00,
        ]);

        $response->assertStatus(201)
            ->assertJson([
                'invoice_number' => 'INV-001',
                'total_amount' => 1000.00,
                'status' => 'unpaid',
            ]);

        $this->assertDatabaseHas('invoices', [
            'invoice_number' => 'INV-001',
            'client_id' => $client->id,
        ]);
    }

    public function test_can_record_payment_and_update_status()
    {
        $role = Role::firstOrCreate(['name' => 'admin']);
        $admin = User::factory()->create();
        $admin->assignRole($role);

        $client = Client::create([
            'business_name' => 'Test Client',
            'email' => 'client@test.com'
        ]);

        $invoice = Invoice::create([
            'client_id' => $client->id,
            'invoice_number' => 'INV-002',
            'invoice_date' => '2024-01-01',
            'due_date' => '2024-01-15',
            'total_amount' => 1000.00,
            'tax_amount' => 0,
            'status' => 'unpaid',
        ]);

        $response = $this->actingAs($admin)->postJson("/api/invoices/{$invoice->id}/payments", [
            'amount' => 500.00,
            'payment_date' => '2024-01-05',
            'payment_method' => 'bank_transfer',
        ]);

        $response->assertStatus(201);

        $this->assertDatabaseHas('payments', [
            'invoice_id' => $invoice->id,
            'amount' => 500.00,
        ]);

        $this->assertDatabaseHas('invoices', [
            'id' => $invoice->id,
            'status' => 'partial',
        ]);

        // Pay remaining
        $this->actingAs($admin)->postJson("/api/invoices/{$invoice->id}/payments", [
            'amount' => 500.00,
            'payment_date' => '2024-01-06',
            'payment_method' => 'cash',
        ]);

        $this->assertDatabaseHas('invoices', [
            'id' => $invoice->id,
            'status' => 'paid',
        ]);
    }
}
