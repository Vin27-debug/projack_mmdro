<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\BackupLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;

class BackupController extends Controller
{
    public function index()
    {
        $files = File::files(
            storage_path('app/backups')
        );
        $logs = BackupLog::latest()->get();

        return view(
            'superadmin.backups.index',
            compact('files', 'logs')
        );
    }

    public function create()
    {
        if (!File::exists(storage_path('app/backups'))) {
            File::makeDirectory(
                storage_path('app/backups'),
                0755,
                true
            );
        }

        $database = env('DB_DATABASE');
        $username = env('DB_USERNAME');
        $password = env('DB_PASSWORD');
        $host = env('DB_HOST', 'localhost');

        $filename = 'backup_' . now()->format('Y_m_d_His') . '.sql';
        $path = storage_path('app/backups/' . $filename);

        // Use which command to find mysqldump executable
        if (PHP_OS_FAMILY === 'Windows') {
            $mysqldump = 'mysqldump';
        } else {
            $mysqldump = '/usr/bin/mysqldump';
        }

        $command = sprintf(
            "%s -h %s -u %s -p%s %s > %s",
            $mysqldump,
            escapeshellarg($host),
            escapeshellarg($username),
            escapeshellarg($password),
            escapeshellarg($database),
            escapeshellarg($path)
        );

        exec($command, $output, $result);

        if ($result !== 0) {
            return back()->with('error', 'Backup failed.');
        }

        BackupLog::create([
            'filename' => $filename,
            'file_size' => File::size($path),
            'status' => 'success'
        ]);

        return back()->with('success', 'Backup created successfully.');
    }

    public function download($file)
    {
        return response()->download(
            storage_path(
                'app/backups/' . $file
            )
        );
    }

    public function restore(Request $request)
    {
        $request->validate([
            'backup_file' => 'required|string'
        ]);

        // Get list of valid backup files (whitelist validation)
        $backupDir = storage_path('app/backups');
        $backupFiles = collect(File::files($backupDir))
            ->map(fn($f) => $f->getBasename())
            ->toArray();

        // Validate backup file is in our whitelist
        if (!in_array($request->backup_file, $backupFiles)) {
            return back()->with('error', 'Invalid backup file selected.');
        }

        $file = $backupDir . '/' . $request->backup_file;

        // Use safe command with proper escaping
        $database = env('DB_DATABASE');
        $username = env('DB_USERNAME');
        $password = env('DB_PASSWORD');
        $host = env('DB_HOST', 'localhost');

        $command = sprintf(
            "mysql -h %s -u %s -p%s %s < %s",
            escapeshellarg($host),
            escapeshellarg($username),
            escapeshellarg($password),
            escapeshellarg($database),
            escapeshellarg($file)
        );

        exec($command, $output, $result);

        if ($result !== 0) {
            return back()->with('error', 'Database restore failed.');
        }

        return back()->with('success', 'Database restored successfully.');
    }
}
