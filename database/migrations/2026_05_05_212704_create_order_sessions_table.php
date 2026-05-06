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
        Schema::create('order_sessions', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('service_order_id');
            $table->foreign('service_order_id')
                ->references('id')
                ->on('service_orders')
                ->onDelete('cascade');
            $table->unsignedSmallInteger('session_number')->default(1);
            $table->date('tanggal')->nullable();
            $table->time('jam')->nullable();
            $table->string('type')->default('kerja'); // kerja, pickup, delivery, survey
            $table->string('status')->default('booked'); // booked, proses, done
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index('service_order_id');
            $table->index('tanggal');
            $table->unique(['service_order_id', 'session_number']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_sessions');
    }
};
