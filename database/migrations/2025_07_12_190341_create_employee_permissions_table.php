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
        Schema::create('employee_permissions', function (Blueprint $table) {
            $table->id();
            
            $table->foreignId('employee_id')->constrained('employee')->onDelete('cascade');
            $table->enum('type', ['izin', 'sakit', 'cuti']);
            $table->date('start_date');
            $table->date('end_date');

            $table->enum('status', ['menunggu', 'disetujui', 'ditolak'])->default('menunggu');

            $table->text('reason')->nullable();

            $table->string('attachment')->nullable();

            $table->timestamp('approved_at')->nullable();
            $table->timestamp('rejected_at')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employee_permissions');
    }
};
