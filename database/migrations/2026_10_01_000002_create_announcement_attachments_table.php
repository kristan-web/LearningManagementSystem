<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
 public function up(): void { Schema::create('announcement_attachments', function (Blueprint $table) { $table->increments('attachment_id'); $table->unsignedInteger('announcement_id'); $table->string('file_name', 255); $table->string('file_url', 500); $table->unsignedInteger('file_size'); $table->dateTime('uploaded_at')->useCurrent(); $table->foreign('announcement_id')->references('announcement_id')->on('announcements')->cascadeOnDelete()->cascadeOnUpdate(); }); }
 public function down(): void { Schema::dropIfExists('announcement_attachments'); }
};
