<?php

namespace App\Console\Commands;

use App\Models\AuditLog;
use App\Models\RecordHistory;
use App\Models\SystemSetting;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

/**
 * @method mixed option(string $key)
 * @method void info(string $string)
 * @method void error(string $string)
 */
class PruneActivityLogs extends Command
{
    public const SUCCESS = 0;

    public const FAILURE = 1;

    protected $signature = 'activity:prune {--days=} {--dry-run}';

    protected $description = 'Prune audit logs and activity feed records beyond retention.';

    public function handle(): int
    {
        $days = $this->option('days');

        if ($days === null) {
            $days = SystemSetting::getValue('activity.retention_days', 90);
        }

        $days = (int) $days;

        if ($days < 1) {
            $this->error('Retention days must be at least 1.');

            return self::FAILURE;
        }

        $cutoff = Carbon::now()->subDays($days);

        $auditCount = AuditLog::where('created_at', '<', $cutoff)->count();
        $historyCount = RecordHistory::where('created_at', '<', $cutoff)->count();

        if ($this->option('dry-run')) {
            $this->info("Audit logs to prune: {$auditCount}");
            $this->info("Activity records to prune: {$historyCount}");

            return self::SUCCESS;
        }

        AuditLog::where('created_at', '<', $cutoff)->delete();
        RecordHistory::where('created_at', '<', $cutoff)->delete();

        $this->info("Pruned {$auditCount} audit logs and {$historyCount} activity records.");

        return self::SUCCESS;
    }
}
