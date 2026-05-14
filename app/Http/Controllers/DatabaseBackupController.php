<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\File;
use Symfony\Component\Process\Process;
use App\Models\ActivityLog;
use ZipArchive;

class DatabaseBackupController extends Controller
{
    public function download()
    {
        $connection = config('database.default');
        $dbConfig = config("database.connections.$connection");

        $database = $dbConfig['database'];
        $username = $dbConfig['username'];
        $password = $dbConfig['password'];
        $host = $dbConfig['host'] ?? '127.0.0.1';
        $port = $dbConfig['port'] ?? 3306;

        $dumpBinary = env('DB_DUMP_BINARY');

        if (!$dumpBinary) {
            abort(500, 'DB_DUMP_BINARY is not configured in the .env file.');
        }

        $backupDirectory = storage_path('app/backups');

        if (!File::exists($backupDirectory)) {
            File::makeDirectory($backupDirectory, 0755, true);
        }

        $timestamp = now()->format('Y-m-d_H-i-s');

        $sqlFilename = "maikine_backup_{$timestamp}.sql";
        $zipFilename = "maikine_backup_{$timestamp}.zip";

        $sqlPath = $backupDirectory . DIRECTORY_SEPARATOR . $sqlFilename;
        $zipPath = $backupDirectory . DIRECTORY_SEPARATOR . $zipFilename;

        /*
         * The dump binary is wrapped in quotes because your local Windows path has spaces:
         * C:\Program Files\MariaDB 11.8\bin\mariadb-dump.exe
         */
        $command = sprintf(
            '"%s" --user=%s --host=%s --port=%s --single-transaction --quick --lock-tables=false %s > %s',
            $dumpBinary,
            escapeshellarg($username),
            escapeshellarg($host),
            escapeshellarg($port),
            escapeshellarg($database),
            escapeshellarg($sqlPath)
        );

        $process = Process::fromShellCommandline($command);

        // Give the backup up to 5 minutes.
        $process->setTimeout(300);

        /*
         * This avoids writing the password directly in the command string.
         */
        $process->setEnv([
            'MYSQL_PWD' => $password,
        ]);

        $process->run();

        if (!$process->isSuccessful()) {
            if (File::exists($sqlPath)) {
                File::delete($sqlPath);
            }

            abort(500, 'Database backup failed: ' . $process->getErrorOutput());
        }

        if (!File::exists($sqlPath) || File::size($sqlPath) === 0) {
            abort(500, 'Database backup failed: SQL dump file was not created.');
        }

        $zip = new ZipArchive();

        if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            if (File::exists($sqlPath)) {
                File::delete($sqlPath);
            }

            abort(500, 'Could not create ZIP backup file.');
        }

        $zip->addFile($sqlPath, $sqlFilename);
        $zip->close();

        File::delete($sqlPath);

        \App\Models\ActivityLog::create([
            'user_id'    => auth()->id(),
            'role'       => auth()->user()?->role_label,
            'action'     => 'Respaldo de la base de datos',
            'ip_address' => request()->ip(),
            'comment'    => 'El usuario descargó un respaldo completo de la base de datos.',
            'created_at' => now(),
        ]);

        return response()
            ->download($zipPath, $zipFilename)
            ->deleteFileAfterSend(true);
    }
}