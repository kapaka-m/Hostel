<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('announcements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('university_id')->constrained()->cascadeOnDelete();
            $table->foreignId('dorm_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->string('title');
            $table->text('body');
            $table->enum('audience', ['UNIVERSITY', 'DORM'])->default('UNIVERSITY');
            $table->enum('status', ['DRAFT', 'SCHEDULED', 'PUBLISHED', 'EXPIRED'])->default('DRAFT');
            $table->timestamp('publish_at')->nullable();
            $table->timestamp('expire_at')->nullable();
            $table->timestamps();

            $table->index(['university_id', 'status']);
            $table->index(['dorm_id', 'status']);
            $table->index(['publish_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('announcements');
    }
};
