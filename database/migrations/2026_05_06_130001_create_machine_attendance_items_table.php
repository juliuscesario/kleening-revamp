<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('machine_attendance_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('machine_attendance_id')->constrained('machine_attendances')->cascadeOnDelete();
            $table->foreignId('machine_id')->constrained('machines');
            $table->timestamps();

            $table->unique(['machine_attendance_id', 'machine_id']);
            $table->index('machine_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('machine_attendance_items');
    }
};
