<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /** Optional note per attendance mark (e.g. "sick"); existing rows keep NULL. */
    public function up(): void
    {
        Schema::table('attendance_records', function (Blueprint $table) {
            $table->string('remarks', 255)->nullable()->after('status');
        });
    }

    public function down(): void
    {
        Schema::table('attendance_records', function (Blueprint $table) {
            $table->dropColumn('remarks');
        });
    }
};
