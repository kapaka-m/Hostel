<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('room_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->foreignId('room_id')->constrained()->cascadeOnDelete();
            $table->boolean('active')->default(true);
            $table->dateTime('from_date');
            $table->dateTime('to_date')->nullable();
            $table->timestamps();

            $table->index(['room_id', 'active']);
            $table->index(['student_id', 'active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('room_assignments');
    }
};
