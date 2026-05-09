<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('machines', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();                    // e.g. "hv-jkt1", "s-srg2"
            $table->string('name')->nullable();                  // optional display name
            $table->foreignId('category_id')->constrained('machine_categories');
            $table->foreignId('area_id')->constrained('areas');  // uses existing areas table
            $table->string('status')->default('active');         // active, maintenance, retired
            $table->foreignId('paired_machine_id')->nullable()->constrained('machines')->nullOnDelete();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index('category_id');
            $table->index('area_id');
            $table->index('status');
            $table->index('paired_machine_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('machines');
    }
};
