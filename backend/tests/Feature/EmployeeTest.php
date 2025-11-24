<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use Spatie\Permission\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;

class EmployeeTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_create_an_employee()
    {
        $role = Role::firstOrCreate(['name' => 'admin']);
        $admin = User::factory()->create();
        $admin->assignRole($role);
        
        // Ensure employee role exists
        Role::firstOrCreate(['name' => 'employee']);

        $response = $this->actingAs($admin)->postJson('/api/employees', [
            'name' => 'John Doe',
            'email' => 'john@auditflow.com',
            'password' => 'password',
            'role' => 'employee',
        ]);

        $response->assertStatus(201)
            ->assertJson([
                'name' => 'John Doe',
                'email' => 'john@auditflow.com',
            ]);

        $this->assertDatabaseHas('users', [
            'email' => 'john@auditflow.com',
        ]);
    }

    public function test_admin_can_list_employees()
    {
        $admin = User::factory()->create();
        $role = Role::firstOrCreate(['name' => 'admin']);
        $admin->assignRole($role);

        $employee = User::factory()->create();
        $employeeRole = Role::firstOrCreate(['name' => 'employee']);
        Role::firstOrCreate(['name' => 'manager']);
        $employee->assignRole($employeeRole);

        $response = $this->actingAs($admin)->getJson('/api/employees');

        $response->assertStatus(200)
            ->assertJsonStructure([
                '*' => ['id', 'name', 'email', 'roles']
            ]);
    }
}
