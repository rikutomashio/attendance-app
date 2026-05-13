<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddBeforeColumnsToAcrBreakTimes extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('attendance_correct_request_break_times', function (Blueprint $table) {
            $table->timestamp('before_break_start_at')->nullable()->after('attendance_correct_request_id');
            $table->timestamp('before_break_end_at')->nullable()->after('before_break_start_at');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('attendance_correct_request_break_times', function (Blueprint $table) {
            $table->dropColumn(['before_break_start_at', 'before_break_end_at']);
        });
    }
}
