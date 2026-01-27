<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('floors', function (Blueprint $table) {
            $table->id();
            $table->foreignId('dorm_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('number');
            $table->unsignedInteger('bathrooms')->default(4);
            $table->unsignedInteger('kitchens')->default(2);
            $table->unsignedInteger('showers')->default(2);
            $table->timestamps();

            $table->unique(['dorm_id', 'number']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('floors');
    }
};
