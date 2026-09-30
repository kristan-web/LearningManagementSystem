<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
 public function up(): void { Schema::create('schedule_events', function (Blueprint $table) { $table->increments('event_id'); $table->enum('created_by_role', ['Student','Teacher', 'Admin']); $table->unsignedInteger('created_by_id'); $table->unsignedInteger('section_id')->nullable(); $table->unsignedInteger('subject_id')->nullable(); $table->string('title', 150); $table->text('description')->nullable(); $table->enum('event_type', ['Personal','Quiz','Review','Announcement', 'School'])->default('Personal'); $table->dateTime('start_datetime'); $table->dateTime('end_datetime'); $table->enum('status', ['Scheduled','Cancelled','Done'])->default('Scheduled'); $table->dateTime('created_at')->useCurrent(); }); }
 public function down(): void { Schema::dropIfExists('schedule_events'); }
};
