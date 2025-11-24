<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Client;
use App\Models\File;
use Spatie\Permission\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;

class WorkLogTest extends TestCase
{
    use RefreshDatabase;

    public function test_employee_can_log_work()
    {
        $role = Role::firstOrCreate(['name' => 'employee']);
        $employee = User::factory()->create();
        $employee->assignRole($role);

        $client = Client::create([
            'business_name' => 'Test Client',
            'email' => 'client@test.com'
        ]);

        $file = File::create([
            'client_id' => $client->id,
            'file_type' => 'GST',
            'assessment_year' => '2024-2025',
            'status' => 'assigned',
            'assigned_to' => $employee->id,
        ]);

        $response = $this->actingAs($employee)->postJson("/api/files/{$file->id}/worklogs", [
            'date' => '2024-01-01',
            'hours_worked' => 2.5,
            'description' => 'Reviewing documents',
            'status_update' => 'in-progress',
        ]);

        $response->assertStatus(201)
            ->assertJson([
                'hours_worked' => 2.5,
                'description' => 'Reviewing documents',
            ]);

        $this->assertDatabaseHas('work_logs', [
            'file_id' => $file->id,
            'user_id' => $employee->id,
            'hours_worked' => 2.5,
        ]);
    }

    public function test_can_retrieve_work_logs_for_file()
    {
        $role = Role::firstOrCreate(['name' => 'manager']);
        $manager = User::factory()->create();
        $manager->assignRole($role);

        $client = Client::create([
            'business_name' => 'Test Client',
            'email' => 'client@test.com'
        ]);

        $file = File::create([
            'client_id' => $client->id,
            'file_type' => 'GST',
            'assessment_year' => '2024-2025',
            'status' => 'assigned',
        ]);

        \App\Models\WorkLog::create([
            'file_id' => $file->id,
            'user_id' => $manager->id,
            'date' => '2024-01-01',
            'hours_worked' => 1.0,
            'description' => 'Initial setup',
        ]);

        $response = $this->actingAs($manager)->getJson("/api/files/{$file->id}/logs");

        $response->assertStatus(200)
            ->assertJsonStructure([
                '*' => ['id', 'date', 'hours_worked', 'description', 'user']
            ]);
    }
}
