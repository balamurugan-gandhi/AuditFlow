<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Client;
use App\Models\Invoice;
use Spatie\Permission\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use App\Notifications\InvoiceGenerated;
use App\Notifications\PaymentReceived;

class NotificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_invoice_generation_sends_notification()
    {
        Notification::fake();

        $role = Role::firstOrCreate(['name' => 'admin']);
        $admin = User::factory()->create();
        $admin->assignRole($role);

        $client = Client::create([
            'business_name' => 'Test Client',
            'email' => 'client@test.com'
        ]);

        $this->actingAs($admin)->postJson('/api/invoices', [
            'client_id' => $client->id,
            'invoice_number' => 'INV-001',
            'invoice_date' => '2024-01-01',
            'due_date' => '2024-01-15',
            'total_amount' => 1000.00,
        ]);

        Notification::assertSentTo(
            [$admin], InvoiceGenerated::class
        );
    }

    public function test_payment_recording_sends_notification()
    {
        Notification::fake();

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
            'status' => 'unpaid',
        ]);

        $this->actingAs($admin)->postJson("/api/invoices/{$invoice->id}/payments", [
            'amount' => 500.00,
            'payment_date' => '2024-01-05',
            'payment_method' => 'bank_transfer',
        ]);

        Notification::assertSentTo(
            [$admin], PaymentReceived::class
        );
    }

    public function test_can_fetch_notifications()
    {
        $role = Role::firstOrCreate(['name' => 'admin']);
        $admin = User::factory()->create();
        $admin->assignRole($role);

        // Manually create a notification in DB
        $admin->notifications()->create([
            'id' => \Illuminate\Support\Str::uuid(),
            'type' => 'App\Notifications\InvoiceGenerated',
            'data' => ['message' => 'Test Notification'],
            'read_at' => null,
        ]);

        $response = $this->actingAs($admin)->getJson('/api/notifications');

        $response->assertStatus(200)
            ->assertJsonStructure([
                '*' => ['id', 'data', 'read_at']
            ]);
    }
}
