<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Spatie\Permission\Models\Role;

class SyncRolesFromUserRole extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'users:sync-roles {--chunk=200} {--dry-run}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync Spatie roles from users.role values';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $chunkSize = (int) $this->option('chunk');
        $dryRun = (bool) $this->option('dry-run');
        $updated = 0;

        User::query()
            ->select('id', 'role')
            ->orderBy('id')
            ->chunkById($chunkSize, function ($users) use (&$updated, $dryRun) {
                foreach ($users as $user) {
                    if (!$user->role) {
                        continue;
                    }

                    Role::findOrCreate($user->role);

                    if ($dryRun) {
                        $updated++;

                        continue;
                    }

                    if (!$user->hasRole($user->role)) {
                        $user->syncRoles([$user->role]);
                        $updated++;
                    }
                }
            });

        $this->info($dryRun
            ? "Dry run complete. {$updated} users would be synced."
            : "Role sync complete. {$updated} users updated.");

        return self::SUCCESS;
    }
}
