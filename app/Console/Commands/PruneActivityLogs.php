<?php

namespace App\Console\Commands;

use App\Central\Models\ActivityLog;
use Illuminate\Console\Command;

class PruneActivityLogs extends Command
{
    protected $signature = 'activity-logs:prune {--days=90 : Delete logs older than this many days}';

    protected $description = 'Delete activity log entries older than the configured retention period.';

    public function handle(): int
    {
        $days = (int) $this->option('days');
        $cutoff = now()->subDays($days);

        $deleted = ActivityLog::on('central')
            ->where('created_at', '<', $cutoff)
            ->delete();

        $this->info("Pruned {$deleted} activity log entries older than {$days} days.");

        return self::SUCCESS;
    }
}
