<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
 public function up(): void { Schema::create('announcement_comments', function (Blueprint $table) {
  $table->increments('comment_id'); $table->unsignedInteger('announcement_id'); $table->unsignedInteger('user_id'); $table->unsignedInteger('parent_comment_id')->nullable(); $table->text('body'); $table->dateTime('created_at')->useCurrent();
  $table->index(['announcement_id', 'parent_comment_id'], 'idx_comment_announcement_parent');
  $table->foreign('announcement_id')->references('announcement_id')->on('announcements')->cascadeOnDelete()->cascadeOnUpdate();
  $table->foreign('user_id')->references('user_id')->on('users')->cascadeOnDelete()->cascadeOnUpdate();
  $table->foreign('parent_comment_id')->references('comment_id')->on('announcement_comments')->cascadeOnDelete();
 }); }
 public function down(): void { Schema::dropIfExists('announcement_comments'); }
};
