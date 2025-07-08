<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('attendance', function (Blueprint $table) {
            // Tambahkan kolom jika belum ada
            if (!Schema::hasColumn('attendance', 'schedule_id')) {
                $table->unsignedBigInteger('schedule_id')->after('id');
            }

            if (!Schema::hasColumn('attendance', 'location_id')) {
                $table->unsignedBigInteger('location_id')->after('schedule_id');
            }

            // Tambahkan foreign key setelah kolom ada
            $table->foreign('schedule_id')->references('id')->on('schedules')->onDelete('cascade');
            $table->foreign('location_id')->references('id')->on('locations')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::table('attendance', function (Blueprint $table) {
            $table->dropForeign(['schedule_id']);
            $table->dropForeign(['location_id']);
            $table->dropColumn(['schedule_id', 'location_id']);
        });
    }
};
