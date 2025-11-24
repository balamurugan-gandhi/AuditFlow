<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\File;
use App\Models\WorkLog;
use App\Models\Client;
use App\Models\User;
use Carbon\Carbon;

class FileSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $clients = Client::all();
        $employees = User::role('employee')->get();
        $allUsers = User::all();
        
        if ($clients->isEmpty()) {
            $this->command->warn('No clients found. Please seed clients first.');
            return;
        }

        $fileTypes = ['Income Tax', 'GST', 'Audit', 'Accounting', 'Consulting'];
        $statuses = ['assigned', 'in-progress', 'pending-info', 'ready-to-file', 'filed', 'completed'];
        $currentYear = date('Y');
        
        // Use only current assessment year for all files
        $currentAssessmentYear = "{$currentYear}-" . ($currentYear + 1);
        
        $financialYears = [
            ($currentYear - 1) . "-{$currentYear}",
            ($currentYear - 2) . "-" . ($currentYear - 1),
            ($currentYear - 3) . "-" . ($currentYear - 2),
        ];

        $this->command->info('Creating 75 dummy files with work logs...');

        for ($i = 0; $i < 75; $i++) {
            $status = $statuses[array_rand($statuses)];
            $assignedTo = null;
            
            // Assign to employee for all statuses
            if (!$employees->isEmpty()) {
                $assignedTo = $employees->random()->id;
            } elseif (!$allUsers->isEmpty()) {
                $assignedTo = $allUsers->random()->id;
            }

            $estimatedDate = null;
            $actualDate = null;
            $paymentRequestDate = null;
            $assignedDate = Carbon::now()->subDays(rand(10, 60));

            // Set dates based on status
            if (in_array($status, ['assigned', 'in-progress', 'pending-info', 'ready-to-file'])) {
                $estimatedDate = Carbon::now()->addDays(rand(5, 60))->format('Y-m-d');
            }

            if (in_array($status, ['filed', 'completed'])) {
                $actualDate = Carbon::now()->subDays(rand(1, 30))->format('Y-m-d');
                $paymentRequestDate = Carbon::now()->subDays(rand(5, 45))->format('Y-m-d');
            } elseif (in_array($status, ['ready-to-file'])) {
                $paymentRequestDate = Carbon::now()->subDays(rand(1, 15))->format('Y-m-d');
            }

            $file = File::create([
                'client_id' => $clients->random()->id,
                'file_type' => $fileTypes[array_rand($fileTypes)],
                'assessment_year' => $currentAssessmentYear,  // All files for current year
                'financial_year' => $financialYears[array_rand($financialYears)],
                'status' => $status,
                'assigned_to' => $assignedTo,
                'estimated_completion_date' => $estimatedDate,
                'actual_completion_date' => $actualDate,
                'payment_request_date' => $paymentRequestDate,
                'notes' => $this->generateRandomNote($status),
            ]);

            // Create work logs for assigned files
            if ($assignedTo) {
                $this->createWorkLogsForFile($file, $assignedTo, $assignedDate, $actualDate);
            }
        }

        $this->command->info('Successfully created 75 files with work logs!');
    }

    private function createWorkLogsForFile($file, $assignedTo, $assignedDate, $actualDate)
    {
        $status = $file->status;
        $numLogs = 0;
        
        // Determine number of work log entries based on status
        if (in_array($status, ['completed', 'filed'])) {
            $numLogs = rand(3, 6);  // Completed files have more logs
        } elseif (in_array($status, ['in-progress', 'ready-to-file'])) {
            $numLogs = rand(2, 4);
        } elseif ($status === 'assigned') {
            $numLogs = rand(1, 2);
        }

        $endDate = $actualDate ? Carbon::parse($actualDate) : Carbon::now();
        $daysBetween = $assignedDate->diffInDays($endDate);
        
        for ($i = 0; $i < $numLogs; $i++) {
            $logDate = $assignedDate->copy()->addDays(rand(0, max(1, $daysBetween)));
            $hoursWorked = rand(1, 8) + (rand(0, 1) * 0.5);  // 1-8.5 hours
            
            WorkLog::create([
                'file_id' => $file->id,
                'user_id' => $assignedTo,
                'date' => $logDate->format('Y-m-d'),
                'hours_worked' => $hoursWorked,
                'description' => $this->generateWorkLogDescription($file->file_type, $i, $numLogs),
                'status_update' => $i === $numLogs - 1 ? $status : null,
                'pending_requirements' => $status === 'pending-info' && $i === $numLogs - 1 ? 'Waiting for client documents' : null,
            ]);
        }
    }

    private function generateWorkLogDescription($fileType, $logIndex, $totalLogs)
    {
        $descriptions = [
            0 => "Initial review of {$fileType} documents",
            1 => "Processing {$fileType} data and calculations",
            2 => "Preparing {$fileType} forms and schedules",
            3 => "Final review and quality check",
            4 => "Client communication and clarifications",
            5 => "Filing preparation and documentation",
        ];

        return $descriptions[$logIndex] ?? "Working on {$fileType}";
    }

    private function generateRandomNote($status): ?string
    {
        $notes = [
            'assigned' => [
                'Assigned to team member for processing',
                'Initial review scheduled',
            ],
            'in-progress' => [
                'Currently processing tax returns',
                'Preparing financial statements',
                'Working on compliance requirements',
            ],
            'pending-info' => [
                'Waiting for additional documents from client',
                'Client needs to provide bank statements',
                'Missing PAN card copy',
            ],
            'ready-to-file' => [
                'Ready for final review and filing',
                'All documents verified and ready',
            ],
            'filed' => [
                'Successfully filed with authorities',
                'Filing completed, awaiting acknowledgment',
            ],
            'completed' => [
                'All work completed successfully',
                'Client notified of completion',
            ],
        ];

        $statusNotes = $notes[$status] ?? ['Processing'];
        return rand(0, 1) ? $statusNotes[array_rand($statusNotes)] : null;
    }
}
