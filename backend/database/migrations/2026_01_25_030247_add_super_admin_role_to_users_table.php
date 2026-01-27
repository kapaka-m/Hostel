<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::getConnection()->getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE users MODIFY role ENUM('UNIVERSITY_ADMIN', 'DORM_ADMIN', 'STUDENT', 'SUPER_ADMIN') DEFAULT 'STUDENT'");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::getConnection()->getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE users MODIFY role ENUM('UNIVERSITY_ADMIN', 'DORM_ADMIN', 'STUDENT') DEFAULT 'STUDENT'");
        }
    }
};
