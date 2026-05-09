<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('machine_attendances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('staff_id')->constrained('staff');
            $table->date('date');
            $table->string('photo_pergi')->nullable();
            $table->timestamp('photo_pergi_at')->nullable();
            $table->string('photo_pulang')->nullable();
            $table->timestamp('photo_pulang_at')->nullable();
            $table->text('catatan')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users');
            $table->foreignId('updated_by')->nullable()->constrained('users');
            $table->timestamps();

            $table->unique(['staff_id', 'date']);
            $table->index('date');
            $table->index('staff_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('machine_attendances');
    }
};
