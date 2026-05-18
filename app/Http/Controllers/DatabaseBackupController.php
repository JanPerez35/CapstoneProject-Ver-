<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\File;
use Symfony\Component\Process\Process;
use App\Models\ActivityLog;
use ZipArchive;

/**
 * Class DatabaseBackupController
 *
 * Handles database backup downloads for authorized administrators.
 *
 * Responsibilities:
 * - reading the active database connection configuration
 * - executing the configured MariaDB/MySQL dump binary
 * - storing the temporary SQL dump in the application backup directory
 * - compressing the SQL dump into a downloadable ZIP file
 * - deleting temporary files after the download is prepared
 * - recording the backup download in the activity log
 */
class DatabaseBackupController extends Controller
{

    /**
     * Generates and downloads a ZIP backup of the current application database.
     *
     * Reads the active Laravel database connection settings, validates that the
     * DB_DUMP_BINARY path is configured, and uses the external dump binary
     * to create a SQL backup file. The SQL file is then compressed into a ZIP
     * archive and returned as a downloadable response.
     *
     * The database password is passed through the MYSQL_PWD environment variable
     * instead of being written directly into the shell command. The temporary SQL
     * dump is deleted after being added to the ZIP file, and the ZIP file is
     * deleted automatically after it is sent to the user.
     *
     * If the dump process fails, the SQL file is missing, the SQL file is empty,
     * or the ZIP archive cannot be created, the method aborts with a 500 error.
     *
     * A successful backup download is recorded in the activity log with the
     * authenticated user's id, role, IP address, action, and timestamp.
     *
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse
     */
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

        ActivityLog::create([
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