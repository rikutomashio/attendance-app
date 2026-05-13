<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('attendance_correct_requests', function (Blueprint $table) {

            // ① 既存FK削除（users参照）
            $table->dropForeign(['approved_by']);

            // ② admins に貼り替え
            $table->foreign('approved_by')
                ->references('id')
                ->on('admins')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('attendance_correct_requests', function (Blueprint $table) {

            $table->dropForeign(['approved_by']);

            $table->foreign('approved_by')
                ->references('id')
                ->on('users')
                ->nullOnDelete();
        });
    }
};
