<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Models\File;
use Carbon\Carbon;

class FileDueNotificationController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $today = Carbon::today();
        $files = File::select('files.id', 'clients.business_name as client_name', 'files.estimated_completion_date', 'files.status', 'files.payment_id')
            ->join('clients', 'files.client_id', '=', 'clients.id')
            ->where('files.status', '!=', 'completed')
            ->whereNull('files.payment_id')
            ->whereNotNull('files.estimated_completion_date')
            ->get()
            ->map(function ($file) use ($today) {
                $dueDate = Carbon::parse($file->estimated_completion_date)->startOfDay();

                // Positive = future, 0 = today, Negative = past
                $daysDiff = $today->diffInDays($dueDate, false);

                return [
                    'id' => $file->id,
                    'name' => $file->client_name ?? ('File #' . $file->id),
                    'due_date' => $dueDate->toDateString(),

                    // Always non-negative
                    'days_left' => max(0, min(5, $daysDiff)),

                    // Always non-negative
                    'days_overdue' => max(0, -$daysDiff),

                    // Optional but very useful
                    'is_overdue' => $daysDiff < 0,
                    'is_due_today' => $daysDiff === 0,
                ];
            });

        return response()->json($files);
    }
}
