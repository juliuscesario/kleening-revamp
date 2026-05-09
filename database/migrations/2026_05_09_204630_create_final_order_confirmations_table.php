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
        Schema::create('final_order_confirmations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('service_order_id')->unique()->constrained('service_orders')->cascadeOnDelete();
            $table->longText('content');
            $table->foreignId('submitted_by')->constrained('users');
            $table->timestamp('submitted_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('final_order_confirmations');
    }
};
