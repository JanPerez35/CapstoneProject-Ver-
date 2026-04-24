<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Review;
use Carbon\Carbon;

class PruneOldReviews extends Command
{
    protected $signature = 'data:prune-old-reviews';
    protected $description = 'Delete reviews older than 5 years';

    public function handle()
    {
        $cutoffDate = Carbon::now()->subYears(5);

        $deleted = Review::where('created_at', '<', $cutoffDate)->delete();

        $this->info("Deleted {$deleted} reviews older than 5 years.");

        return Command::SUCCESS;
    }
}