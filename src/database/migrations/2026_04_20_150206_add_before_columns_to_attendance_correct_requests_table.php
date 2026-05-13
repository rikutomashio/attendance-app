<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddBeforeColumnsToAttendanceCorrectRequestsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('attendance_correct_requests', function (Blueprint $table) {
            $table->timestamp('before_clock_in_at')->nullable()->after('attendance_id');
            $table->timestamp('before_clock_out_at')->nullable()->after('before_clock_in_at');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('attendance_correct_requests', function (Blueprint $table) {
            $table->dropColumn([
                'before_clock_in_at',
                'before_clock_out_at',
            ]);
        });
    }
}
