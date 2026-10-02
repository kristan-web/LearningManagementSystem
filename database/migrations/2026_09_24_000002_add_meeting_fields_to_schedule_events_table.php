<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('schedule_events', function (Blueprint $table) {
            $table->enum('event_type', ['Personal', 'Quiz', 'Review', 'Announcement', 'School', 'Meeting'])
                ->default('Personal')->change();

            $table->unsignedInteger('schedule_id')->nullable()->after('subject_id');
            $table->string('meeting_link', 255)->nullable()->after('status');
            $table->string('meeting_provider', 20)->default('manual')->after('meeting_link');
            $table->enum('meeting_status', ['Scheduled', 'Live', 'Ended'])->nullable()->after('meeting_provider');

            $table->foreign('schedule_id')->references('schedule_id')->on('schedules')->nullOnDelete()->cascadeOnUpdate();
        });
    }

    public function down(): void
    {
        Schema::table('schedule_events', function (Blueprint $table) {
            $table->dropForeign(['schedule_id']);
            $table->dropColumn(['schedule_id', 'meeting_link', 'meeting_provider', 'meeting_status']);
            $table->enum('event_type', ['Personal', 'Quiz', 'Review', 'Announcement', 'School'])
                ->default('Personal')->change();
        });
    }
};
