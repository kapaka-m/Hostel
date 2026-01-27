<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        $driver = DB::getDriverName();

        if ($driver === 'mysql') {
            DB::statement('ALTER TABLE room_assignments ADD COLUMN active_student_id BIGINT UNSIGNED GENERATED ALWAYS AS (CASE WHEN active = 1 THEN student_id END) STORED');
            DB::statement('CREATE UNIQUE INDEX room_assignments_active_student_unique ON room_assignments (active_student_id)');
        } elseif ($driver === 'pgsql') {
            DB::statement('CREATE UNIQUE INDEX room_assignments_active_student_unique ON room_assignments (student_id) WHERE active = true');
        } elseif ($driver === 'sqlite') {
            DB::statement('CREATE UNIQUE INDEX room_assignments_active_student_unique ON room_assignments (student_id) WHERE active = 1');
        }
    }

    public function down(): void
    {
        $driver = DB::getDriverName();

        if ($driver === 'mysql') {
            DB::statement('DROP INDEX room_assignments_active_student_unique ON room_assignments');
            DB::statement('ALTER TABLE room_assignments DROP COLUMN active_student_id');
        } elseif ($driver === 'pgsql' || $driver === 'sqlite') {
            DB::statement('DROP INDEX IF EXISTS room_assignments_active_student_unique');
        }
    }
};
