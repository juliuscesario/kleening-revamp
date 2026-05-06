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
        Schema::create('order_session_staff', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('order_session_id');
            $table->foreign('order_session_id')
                ->references('id')
                ->on('order_sessions')
                ->onDelete('cascade');
            $table->unsignedBigInteger('staff_id');
            $table->foreign('staff_id')
                ->references('id')
                ->on('staff')
                ->onDelete('cascade');
            $table->string('signature_image')->nullable();
            $table->timestamps();

            $table->unique(['order_session_id', 'staff_id']);
            $table->index('staff_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_session_staff');
    }
};
