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
        Schema::create('attendance', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained('employee')->onDelete('cascade');
            $table->dateTime('clock_in')->nullable();
            $table->decimal('latitude_clock_in', 10, 7)->nullable();
            $table->decimal('longitude_clock_in', 10, 7)->nullable();
            $table->string('photo_clock_in')->nullable();
            $table->dateTime('clock_out')->nullable();
            $table->decimal('latitude_clock_out', 10, 7)->nullable();
            $table->decimal('longitude_clock_out', 10, 7)->nullable();
            $table->string('photo_clock_out')->nullable();
            $table->enum('status', ['Hadir', 'Telat', 'Alfa', 'Sakit', 'Izin', 'Cuti'])->default('Hadir');
            $table->text('note')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attendence');
    }
};
