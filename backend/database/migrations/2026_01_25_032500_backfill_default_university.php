<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasTable('universities')) {
            return;
        }

        $now = now();
        $defaultId = DB::table('universities')->where('code', 'DEFAULT')->value('id');

        if (!$defaultId) {
            $defaultId = DB::table('universities')->insertGetId([
                'name' => 'Default University',
                'code' => 'DEFAULT',
                'address' => null,
                'contact_email' => null,
                'contact_phone' => null,
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        if (Schema::hasTable('dorms')) {
            DB::table('dorms')
                ->whereNull('university_id')
                ->update(['university_id' => $defaultId]);
        }

        if (Schema::hasTable('users')) {
            DB::table('users')
                ->whereNull('university_id')
                ->where('role', 'UNIVERSITY_ADMIN')
                ->update(['university_id' => $defaultId]);
        }
    }

    public function down(): void
    {
        // Intentionally left as a no-op to avoid destructive data loss.
    }
};
