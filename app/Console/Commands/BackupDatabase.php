<?php

namespace App\Console\Commands;

use App\Models\BackupLog;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class BackupDatabase extends Command
{
    protected $signature = 'backup:database {--type=daily}';

    protected $description = 'Create a database backup and log the result';

    public function handle(): int
    {
        $type = $this->option('type');
        $backupDir = storage_path('app/backups');

        if (!File::exists($backupDir)) {
            File::makeDirectory($backupDir, 0755, true);
        }

        $filename = 'backup_' . $type . '_' . now()->format('Y_m_d_His') . '.sql';
        $path = $backupDir . DIRECTORY_SEPARATOR . $filename;

        $database = env('DB_DATABASE');
        $username = env('DB_USERNAME');
        $password = env('DB_PASSWORD');

        $mysqldump = 'C:\\laragon\\bin\\mysql\\mysql-8.4.3-winx64\\bin\\mysqldump.exe';
        $command = '"' . $mysqldump . '" -u' . $username . ' ' . $database . ' > "' . $path . '"';

        exec($command, $output, $result);

        if ($result !== 0 || !File::exists($path)) {
            BackupLog::create([
                'type' => $type,
                'filename' => $filename,
                'status' => 'failed',
                'path' => $path,
                'message' => 'Backup creation failed',
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
}
