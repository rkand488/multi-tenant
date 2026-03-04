<?php

namespace App\Console\Commands;

use App\Central\Models\AuditLog;
use Illuminate\Console\Command;

class PruneAuditLogs extends Command
{
    protected $signature = 'audit-logs:prune {--days=365 : Delete audit logs older than this many days}';

    protected $description = 'Delete audit log entries older than the configured retention period.';

    public function handle(): int
    {
        $days = (int) $this->option('days');
        $cutoff = now()->subDays($days);

        $deleted = AuditLog::on('central')
            ->where('created_at', '<', $cutoff)
            ->delete();

        $this->info("Pruned {$deleted} audit log entries older than {$days} days.");

        return self::SUCCESS;
    }
}
