<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\BackupLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Symfony\Component\Process\Process;

class BackupController extends Controller
{
    public function index()
    {
        $backupDir = storage_path('app/backups');

        $files = File::exists($backupDir)
            ? collect(File::files($backupDir))
            ->sortByDesc(fn($file) => filemtime($file))
            ->values()
            ->all()
            : [];

        $logs = BackupLog::latest()->get();

        return view(
            'superadmin.backups.index',
            compact('files', 'logs')
        );
    }

    public function create()
    {
        if ($this->isUnsupportedDatabase()) {
            return back()->with('error', 'Database backups are only supported for MySQL databases.');
        }

        $backupDir = storage_path('app/backups');

        if (!File::exists($backupDir)) {
            File::makeDirectory($backupDir, 0755, true);
        }

        $filename = 'backup_' . now()->format('Y_m_d_His') . '.sql';
        $path = $backupDir . DIRECTORY_SEPARATOR . $filename;
        $connection = config('database.connections.mysql');

        $process = new Process([
            $this->mysqlDumpBinary(),
            '--host=' . ($connection['host'] ?? '127.0.0.1'),
            '--port=' . ($connection['port'] ?? 3306),
            '--user=' . ($connection['username'] ?? ''),
            '--password=' . ($connection['password'] ?? ''),
            '--result-file=' . $path,
            $connection['database'] ?? '',
        ]);

        $process->setTimeout(300);
        $process->run();

        if (!$process->isSuccessful()) {
            if (File::exists($path)) {
                File::delete($path);
            }

            Log::error('MySQL backup failed.', [
                'output' => $process->getOutput(),
                'error' => $process->getErrorOutput(),
            ]);

            return back()->with('error', trim($process->getErrorOutput() ?: $process->getOutput()) ?: 'Backup failed.');
        }

        BackupLog::create([
            'type' => 'manual',
            'filename' => $filename,
            'status' => 'success',
            'path' => $path,
            'message' => 'Backup created successfully.',
        ]);

        return back()->with('success', 'Backup created successfully.');
    }

    public function download($file)
    {
        $resolvedPath = $this->resolveBackupFilePath($file);

        if ($resolvedPath === null) {
            abort(404);
        }

        return response()->download($resolvedPath);
    }

    public function restore(Request $request)
    {
        if ($this->isUnsupportedDatabase()) {
            return back()->with('error', 'Database restores are only supported for MySQL databases.');
        }

        $request->validate([
            'backup_file' => 'required|string',
        ]);

        $file = $this->resolveBackupFilePath($request->backup_file);

        if ($file === null) {
            return back()->with('error', 'Invalid backup file selected.');
        }

        $connection = config('database.connections.mysql');

        $process = new Process([
            $this->mysqlClientBinary(),
            '--host=' . ($connection['host'] ?? '127.0.0.1'),
            '--port=' . ($connection['port'] ?? 3306),
            '--user=' . ($connection['username'] ?? ''),
            '--password=' . ($connection['password'] ?? ''),
            $connection['database'] ?? '',
        ]);

        $process->setTimeout(300);
        $process->setInput(file_get_contents($file));
        $process->run();

        if (!$process->isSuccessful()) {
            Log::error('MySQL restore failed.', [
                'output' => $process->getOutput(),
                'error' => $process->getErrorOutput(),
            ]);

            return back()->with('error', trim($process->getErrorOutput() ?: $process->getOutput()) ?: 'Database restore failed.');
        }

        BackupLog::create([
            'type' => 'restore',
            'filename' => basename($file),
            'status' => 'success',
            'path' => $file,
            'message' => 'Database restored successfully.',
        ]);

        return back()->with('success', 'Database restored successfully.');
    }

    private function isUnsupportedDatabase(): bool
    {
        return config('database.default') !== 'mysql';
    }

    private function resolveBackupFilePath(string $file): ?string
    {
        $requestedFile = basename($file);

        if ($requestedFile !== $file || !Str::endsWith($requestedFile, '.sql')) {
            return null;
        }

        $backupDir = realpath(storage_path('app/backups'));

        if ($backupDir === false) {
            return null;
        }

        $resolvedFile = realpath($backupDir . DIRECTORY_SEPARATOR . $requestedFile);

        if ($resolvedFile === false) {
            return null;
        }

        if ($resolvedFile !== $backupDir . DIRECTORY_SEPARATOR . $requestedFile) {
            return null;
        }

        return $resolvedFile;
    }

    private function mysqlDumpBinary(): string
    {
        return PHP_OS_FAMILY === 'Windows' ? 'mysqldump.exe' : 'mysqldump';
    }

    private function mysqlClientBinary(): string
    {
        return PHP_OS_FAMILY === 'Windows' ? 'mysql.exe' : 'mysql';
    }
}
