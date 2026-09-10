<?php

namespace App\Console\Commands;

use App\Models\BackupLog;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Symfony\Component\Process\Process;

class BackupDatabase extends Command
{
    protected $signature = 'backup:database {--type=daily}';

    protected $description = 'Create a database backup and log the result';

    public function handle(): int
    {
        if (config('database.default') !== 'mysql') {
            $this->error('Database backups are only supported for MySQL databases.');

            return self::FAILURE;
        }

        $type = $this->option('type');
        $backupDir = storage_path('app/backups');

        if (!File::exists($backupDir)) {
            File::makeDirectory($backupDir, 0755, true);
        }

        $filename = 'backup_' . $type . '_' . now()->format('Y_m_d_His') . '.sql';
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

        if (!$process->isSuccessful() || !File::exists($path)) {
            BackupLog::create([
                'type' => $type,
                'filename' => $filename,
                'status' => 'failed',
                'path' => $path,
                'message' => trim($process->getErrorOutput() ?: $process->getOutput()) ?: 'Backup creation failed',
            ]);

            $this->error('Backup failed.');

            return self::FAILURE;
        }

        $retention = config('backup.retention', 30);
        $files = collect(File::files($backupDir))
            ->filter(fn($file) => Str::startsWith(basename($file), 'backup_'))
            ->sortByDesc(fn($file) => filemtime($file));

        foreach ($files->slice($retention) as $file) {
            File::delete($file);
        }

        BackupLog::create([
            'type' => $type,
            'filename' => $filename,
            'status' => 'completed',
            'path' => $path,
            'message' => 'Backup completed successfully',
        ]);

        $this->info('Backup created successfully.');

        return self::SUCCESS;
    }

    private function mysqlDumpBinary(): string
    {
        return PHP_OS_FAMILY === 'Windows' ? 'mysqldump.exe' : 'mysqldump';
    }
}
