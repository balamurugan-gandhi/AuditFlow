<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Client;
use Illuminate\Support\Facades\Hash;

class EmployeeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Ensure the employee role exists
        $employeeRole = \Spatie\Permission\Models\Role::where('name', 'employee')->first();
        
        if (!$employeeRole) {
            $this->command->error('Employee role not found. Please run RoleSeeder first.');
            return;
        }

        $clients = Client::all();

        if ($clients->isEmpty()) {
            $this->command->warn('No clients found. Skipping client assignment.');
        }

        for ($i = 1; $i <= 5; $i++) {
            $email = "employee{$i}@auditflow.com";
            
            $user = User::firstOrCreate(
                ['email' => $email],
                [
                    'name' => "Test Employee {$i}",
                    'password' => Hash::make('password'),
                ]
            );

            if (!$user->hasRole('employee')) {
                $user->assignRole($employeeRole);
            }

            // Assign random clients (e.g., 5-10 clients per employee)
            if ($clients->isNotEmpty()) {
                $randomClients = $clients->random(min(10, $clients->count()));
                $user->clients()->syncWithoutDetaching($randomClients->pluck('id'));
                $this->command->info("Assigned " . $randomClients->count() . " clients to {$user->name}");
            }
        }
    }
}
