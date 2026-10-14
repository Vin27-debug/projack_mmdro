<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\BackupLog;
use App\Services\AuditService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Symfony\Component\Process\Process;

class BackupController extends Controller
{
    public function index()
    {
        $backupDir = $this->backupDirectory();

        $files = File::exists($backupDir)
            ? collect(File::files($backupDir))
            ->sortByDesc(fn($file) => filemtime($file))
            ->values()
            : [];

        $logs = BackupLog::latest()->get();

        return view(
            'superadmin.backups.index',
            compact('files', 'logs') + ['driver' => $this->driver(), 'isSupported' => ! $this->isUnsupportedDatabase()]
        );
    }

    public function create()
    {
        if ($this->isUnsupportedDatabase()) {
            return back()->with('error', 'Database backups require MySQL, MariaDB, or a file-based SQLite database.');
        }

        $backupDir = $this->backupDirectory();

        if (!File::exists($backupDir)) {
            File::makeDirectory($backupDir, 0755, true);
        }

        $filename = 'backup_' . now()->format('Y_m_d_His_u') . '.' . $this->extension();
        $path = $backupDir . DIRECTORY_SEPARATOR . $filename;

        if ($this->driver() === 'sqlite') {
            $source = $this->sqlitePath();
            if (! File::isFile($source) || ! File::copy($source, $path) || ! $this->isValidBackup($path)) {
                File::delete($path);
                return back()->with('error', 'SQLite backup failed. The configured database file is unavailable or invalid.');
            }

            BackupLog::create(['type' => 'manual', 'filename' => $filename, 'status' => 'success', 'path' => 'private/backups/'.$filename, 'message' => 'Backup created successfully.']);
            AuditService::log('created', 'Database Backup', "Database backup {$filename} created.");
            return back()->with('success', 'Backup created successfully.');
        }
        $connection = config('database.connections.'.$this->driver());

        $process = new Process([
            $this->mysqlDumpBinary(),
            '--host=' . ($connection['host'] ?? '127.0.0.1'),
            '--port=' . ($connection['port'] ?? 3306),
            '--user=' . ($connection['username'] ?? ''),
            '--single-transaction',
            '--routines',
            '--events',
            '--result-file=' . $path,
            $connection['database'] ?? '',
        ], null, ['MYSQL_PWD' => (string) ($connection['password'] ?? '')]);

        $process->setTimeout(300);
        $process->run();

        if (!$process->isSuccessful() || ! $this->isValidBackup($path)) {
            if (File::exists($path)) {
                File::delete($path);
            }

            Log::error('MySQL backup failed.', [
                'output' => $process->getOutput(),
                'error' => $process->getErrorOutput(),
            ]);

            return back()->with('error', trim($process->getErrorOutput() ?: $process->getOutput()) ?: 'Backup failed validation.');
        }

        BackupLog::create([
            'type' => 'manual',
            'filename' => $filename,
            'status' => 'success',
            'path' => 'private/backups/'.$filename,
            'message' => 'Backup created successfully.',
        ]);

        AuditService::log('created', 'Database Backup', "Database backup {$filename} created.");
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
        $request->validate([
            'backup_file' => 'required|string',
            'confirm_restore' => 'accepted',
        ]);

        if ($this->isUnsupportedDatabase()) {
            return back()->with('error', 'Database restores require MySQL, MariaDB, or a file-based SQLite database.');
        }

        $file = $this->resolveBackupFilePath($request->backup_file);

        if ($file === null) {
            return back()->with('error', 'The selected backup is missing, invalid, or unsupported.');
        }

        if (! $this->isValidBackup($file)) {
            return back()->with('error', 'The selected backup is corrupted, empty, or unsupported.');
        }

        // Always take a recoverable copy after explicit confirmation and before any restore.
        if ($this->driver() === 'sqlite') {
            $safety = 'pre_restore_' . now()->format('Y_m_d_His_u') . '.sqlite';
            $safetyPath = $this->backupDirectory().DIRECTORY_SEPARATOR.$safety;
            if (! File::copy($this->sqlitePath(), $safetyPath)) {
                return back()->with('error', 'Restore stopped because a safety backup could not be created.');
            }
            if (! File::copy($file, $this->sqlitePath())) {
                return back()->with('error', 'SQLite restore failed. The safety backup remains available.');
            }
            BackupLog::create(['type' => 'restore', 'filename' => basename($file), 'status' => 'success', 'path' => 'private/backups/'.basename($file), 'message' => "Database restored successfully; safety backup {$safety} created first."]);
            AuditService::log('restored', 'Database Backup', "Database restored from ".basename($file)."; safety backup {$safety} created first.");
            return back()->with('success', 'Database restored successfully. A safety backup was created first.');
        }

        $safetyFilename = $this->createMysqlSafetyBackup();
        $connection = config('database.connections.'.$this->driver());

        $process = new Process([
            $this->mysqlClientBinary(),
            '--host=' . ($connection['host'] ?? '127.0.0.1'),
            '--port=' . ($connection['port'] ?? 3306),
            '--user=' . ($connection['username'] ?? ''),
            $connection['database'] ?? '',
        ], null, ['MYSQL_PWD' => (string) ($connection['password'] ?? '')]);

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
            'path' => 'private/backups/'.basename($file),
            'message' => "Database restored successfully; safety backup {$safetyFilename} created first.",
        ]);

        AuditService::log('restored', 'Database Backup', 'Database restored from '.basename($file)."; safety backup {$safetyFilename} created first.");
        return back()->with('success', 'Database restored successfully. A safety backup was created first.');
    }

    private function isUnsupportedDatabase(): bool
    {
        return ! in_array($this->driver(), ['mysql', 'mariadb', 'sqlite'], true)
            || ($this->driver() === 'sqlite' && (! File::isFile($this->sqlitePath()) || $this->sqlitePath() === ':memory:'));
    }

    private function resolveBackupFilePath(string $file): ?string
    {
        $requestedFile = basename($file);

        if ($requestedFile !== $file || !Str::endsWith($requestedFile, '.'.$this->extension())) {
            return null;
        }

        $backupDir = realpath($this->backupDirectory());

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

    private function driver(): string
    {
        return (string) config('database.default');
    }

    private function extension(): string
    {
        return $this->driver() === 'sqlite' ? 'sqlite' : 'sql';
    }

    private function backupDirectory(): string
    {
        return storage_path('app/private/backups');
    }

    private function sqlitePath(): string
    {
        return (string) config('database.connections.sqlite.database');
    }

    private function isValidBackup(string $path): bool
    {
        if (! File::isFile($path) || File::size($path) < 16 || File::size($path) > 1073741824) {
            return false;
        }

        $header = file_get_contents($path, false, null, 0, 65536);

        if ($header === false) {
            return false;
        }

        return $this->driver() === 'sqlite'
            ? str_starts_with($header, "SQLite format 3\000")
            : ! str_contains($header, "\000") && (bool) preg_match('/(?:-- MySQL dump|CREATE (?:DATABASE|TABLE)|INSERT INTO)/i', $header);
    }

    private function createMysqlSafetyBackup(): string
    {
        File::ensureDirectoryExists($this->backupDirectory(), 0750, true);
        $filename = 'pre_restore_'.now()->format('Y_m_d_His_u').'.sql';
        $path = $this->backupDirectory().DIRECTORY_SEPARATOR.$filename;
        $connection = config('database.connections.'.$this->driver());
        $process = new Process([
            $this->mysqlDumpBinary(), '--host='.($connection['host'] ?? '127.0.0.1'),
            '--port='.($connection['port'] ?? 3306), '--user='.($connection['username'] ?? ''),
            '--single-transaction', '--routines', '--events', '--result-file='.$path, $connection['database'] ?? '',
        ], null, ['MYSQL_PWD' => (string) ($connection['password'] ?? '')]);
        $process->setTimeout(300);
        $process->run();

        if (! $process->isSuccessful() || ! $this->isValidBackup($path)) {
            File::delete($path);
            throw new \RuntimeException('Restore stopped because a safety backup could not be created.');
        }

        return $filename;
    }
}
