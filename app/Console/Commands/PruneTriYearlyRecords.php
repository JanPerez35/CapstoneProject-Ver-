<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Lending;
use App\Models\LendingItem;
use App\Models\FacilityCostReportItem;
use Carbon\Carbon;

class PruneTriYearlyRecords extends Command
{
    protected $signature = 'data:prune-tri-yearly-records';
    protected $description = 'Delete lending and facility report item records older than 3 year';

    public function handle()
    {
        $cutoffDate = Carbon::now()->subYears(3);

        $totalDeletedLendingItems = 0;
        $totalDeletedLendings = 0;
        $totalDeletedFacilityItems = 0;

        // Delete lending_items linked to old lendings
        $oldLendingIds = Lending::where('created_at', '<', $cutoffDate)->pluck('id');

        if ($oldLendingIds->isNotEmpty()) {
            $totalDeletedLendingItems = LendingItem::whereIn('lending_id', $oldLendingIds)->delete();
            $totalDeletedLendings = Lending::whereIn('id', $oldLendingIds)->delete();
        }

        // Delete old facility cost report items
        $totalDeletedFacilityItems = FacilityCostReportItem::where('created_at', '<', $cutoffDate)->delete();

        $this->info("Deleted {$totalDeletedLendingItems} lending_items older than 3 year.");
        $this->info("Deleted {$totalDeletedLendings} lendings older than 3 year.");
        $this->info("Deleted {$totalDeletedFacilityItems} facility_cost_report_items older than 3 year.");

        return Command::SUCCESS;
    }
}