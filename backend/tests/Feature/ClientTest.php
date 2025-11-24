<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use Spatie\Permission\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ClientTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_create_a_client()
    {
        $admin = User::factory()->create();
        $role = Role::firstOrCreate(['name' => 'admin']);
        $admin->assignRole($role);

        $response = $this->actingAs($admin)->postJson('/api/clients', [
            'business_name' => 'Test Business',
            'email' => 'test@business.com',
            'filing_cycle' => 'yearly',
        ]);

        $response->assertStatus(201)
            ->assertJson([
                'business_name' => 'Test Business',
                'email' => 'test@business.com',
            ]);

        $this->assertDatabaseHas('clients', [
            'email' => 'test@business.com',
        ]);
    }

    public function test_admin_can_list_clients()
    {
        $admin = User::factory()->create();
        $role = Role::firstOrCreate(['name' => 'admin']);
        $admin->assignRole($role);

        \App\Models\Client::create([
            'business_name' => 'Client 1',
            'email' => 'c1@test.com'
        ]);

        $response = $this->actingAs($admin)->getJson('/api/clients');

        $response->assertStatus(200)
            ->assertJsonStructure([
                '*' => ['id', 'business_name', 'email']
            ]);
    }
}
