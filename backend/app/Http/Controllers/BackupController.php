<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Illuminate\Support\Facades\Storage;

class BackupController extends Controller
{
    public function download(Request $request)
    {
        if (!$request->user()->hasRole('admin')) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $filename = 'backup-' . date('Y-m-d-H-i-s') . '.sql';
        $path = storage_path('app/' . $filename);

        $command = sprintf(
            'mysqldump --skip-ssl --user=%s --password=%s --host=%s %s > %s',
            config('database.connections.mysql.username'),
            config('database.connections.mysql.password'),
            config('database.connections.mysql.host'),
            config('database.connections.mysql.database'),
            $path
        );

        // Using exec for simplicity as Process might have issues with redirection operators in some envs
        // Ensure your env variables are safe.
        exec($command, $output, $returnVar);

        if ($returnVar !== 0) {
            return response()->json(['message' => 'Backup failed'], 500);
        }

        return response()->download($path)->deleteFileAfterSend(true);
    }
}
