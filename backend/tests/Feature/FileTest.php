<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Client;
use Spatie\Permission\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;

class FileTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_create_a_file()
    {
        $admin = User::factory()->create();
        $role = Role::firstOrCreate(['name' => 'admin']);
        $admin->assignRole($role);

        $client = Client::create([
            'business_name' => 'Test Client',
            'email' => 'client@test.com'
        ]);

        $response = $this->actingAs($admin)->postJson('/api/files', [
            'client_id' => $client->id,
            'file_type' => 'Income Tax',
            'assessment_year' => '2024-2025',
            'status' => 'received',
        ]);

        $response->assertStatus(201)
            ->assertJson([
                'file_type' => 'Income Tax',
                'assessment_year' => '2024-2025',
            ]);

        $this->assertDatabaseHas('files', [
            'client_id' => $client->id,
            'file_type' => 'Income Tax',
        ]);
    }

    public function test_admin_can_assign_file_to_employee()
    {
        $admin = User::factory()->create();
        $role = Role::firstOrCreate(['name' => 'admin']);
        $admin->assignRole($role);

        $employee = User::factory()->create();
        $employeeRole = Role::firstOrCreate(['name' => 'employee']);
        $employee->assignRole($employeeRole);

        $client = Client::create([
            'business_name' => 'Test Client',
            'email' => 'client@test.com'
        ]);

        $file = \App\Models\File::create([
            'client_id' => $client->id,
            'file_type' => 'GST',
            'assessment_year' => '2024-2025',
            'status' => 'received',
        ]);

        $response = $this->actingAs($admin)->putJson("/api/files/{$file->id}", [
            'assigned_to' => $employee->id,
            'status' => 'assigned',
        ]);

        $response->assertStatus(200);
        
        $this->assertDatabaseHas('files', [
            'id' => $file->id,
            'assigned_to' => $employee->id,
            'status' => 'assigned',
        ]);
    }
}
