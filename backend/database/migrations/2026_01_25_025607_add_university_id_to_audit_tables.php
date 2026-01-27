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
        Schema::table('audit_logs', function (Blueprint $table) {
            $table->foreignId('university_id')->nullable()->constrained('universities')->nullOnDelete()->after('actor_id');
            $table->index('university_id');
        });

        Schema::table('record_histories', function (Blueprint $table) {
            $table->foreignId('university_id')->nullable()->constrained('universities')->nullOnDelete()->after('actor_id');
            $table->index('university_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('record_histories', function (Blueprint $table) {
            $table->dropConstrainedForeignId('university_id');
        });

        Schema::table('audit_logs', function (Blueprint $table) {
            $table->dropConstrainedForeignId('university_id');
        });
    }
};
