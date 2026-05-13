<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAttendanceCorrectRequestBreakTimesTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('attendance_correct_request_break_times', function (Blueprint $table) {
            $table->id();

            // 修正申請との紐付け
            $table->unsignedBigInteger('attendance_correct_request_id');

            $table->foreign('attendance_correct_request_id', 'acrbt_acr_id_fk')
                ->references('id')
                ->on('attendance_correct_requests')
                ->cascadeOnDelete();

            // 修正後の休憩時間
            $table->timestamp('requested_break_start_at')->nullable();
            $table->timestamp('requested_break_end_at')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::dropIfExists('attendance_correct_request_break_times');
    }
}
