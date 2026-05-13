<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('staff_attendances', function (Blueprint $table) {
            $table->id();
            $table->string('nik', 50);                    // Hadirr employee ID
            $table->string('nama', 255);                  // Employee name from Hadirr
            $table->date('tanggal');                       // Attendance date
            $table->datetime('clock_in')->nullable();      // Clock in timestamp
            $table->datetime('clock_out')->nullable();     // Clock out timestamp
            $table->string('status', 50)->nullable();      // Short code: PW, A, S, L, etc.
            $table->string('raw_status', 100)->nullable(); // Full string from API
            $table->text('notes')->nullable();             // Admin notes or late notes from Hadirr
            $table->string('clock_in_location', 255)->nullable();
            $table->string('clock_out_location', 255)->nullable();
            $table->json('hadirr_raw')->nullable();        // Full raw API response for this record
            $table->datetime('synced_at');                 // When this record was last synced
            $table->timestamps();

            // Unique constraint: one record per employee per day
            $table->unique(['nik', 'tanggal']);

            // Indexes for queries
            $table->index('tanggal');
            $table->index('nik');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('staff_attendances');
    }
};
