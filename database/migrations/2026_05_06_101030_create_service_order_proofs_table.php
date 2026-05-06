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
        Schema::create('service_order_proofs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('service_order_id');
            $table->unsignedBigInteger('order_session_id')->nullable();
            $table->unsignedBigInteger('staff_id')->nullable();
            $table->string('type');
            $table->string('file_path');
            $table->timestamps();

            $table->foreign('service_order_id')->references('id')->on('service_orders')->onDelete('cascade');
            $table->foreign('order_session_id')->references('id')->on('order_sessions')->nullOnDelete();
            $table->foreign('staff_id')->references('id')->on('staff')->nullOnDelete();

            $table->unique(['service_order_id', 'type']);
            $table->index('service_order_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('service_order_proofs');
    }
};
