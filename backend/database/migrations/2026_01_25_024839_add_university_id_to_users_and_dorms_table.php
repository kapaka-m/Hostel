<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('university_id')->nullable()->constrained('universities')->nullOnDelete()->after('role');
        });

        Schema::table('dorms', function (Blueprint $table) {
            $table->foreignId('university_id')->nullable()->constrained('universities')->nullOnDelete()->after('id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('dorms', function (Blueprint $table) {
            $table->dropConstrainedForeignId('university_id');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropConstrainedForeignId('university_id');
        });
    }
};
