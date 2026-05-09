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
        Schema::create('machine_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');           // e.g. "Hydrovacuum"
            $table->string('slug')->unique(); // e.g. "hydrovacuum"
            $table->string('code_prefix');    // e.g. "hv" — used for auto-suggesting machine codes
            $table->integer('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('machine_categories');
    }
};
