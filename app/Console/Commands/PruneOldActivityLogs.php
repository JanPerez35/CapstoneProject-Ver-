<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\ActivityLog;
use Carbon\Carbon;

class PruneOldActivityLogs extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'logs:prune-activity';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Delete activity logs older than 5 years';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $cutoffDate = Carbon::now()->subYears(5);

        $deleted = ActivityLog::where('created_at', '<', $cutoffDate)->delete();

        $this->info("Deleted {$deleted} activity logs older than {$cutoffDate->toDateTimeString()}.");

        return Command::SUCCESS;
    }
}