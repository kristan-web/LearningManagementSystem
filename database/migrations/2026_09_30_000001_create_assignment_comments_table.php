<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
 public function up(): void { Schema::create('assignment_comments', function (Blueprint $table) {
  $table->increments('comment_id'); $table->unsignedInteger('assignment_id'); $table->unsignedInteger('user_id'); $table->unsignedInteger('parent_comment_id')->nullable(); $table->text('body'); $table->dateTime('created_at')->useCurrent();
  $table->index(['assignment_id', 'parent_comment_id'], 'idx_comment_assignment_parent');
  $table->foreign('assignment_id')->references('assignment_id')->on('assignments')->cascadeOnDelete()->cascadeOnUpdate();
  $table->foreign('user_id')->references('user_id')->on('users')->cascadeOnDelete()->cascadeOnUpdate();
  $table->foreign('parent_comment_id')->references('comment_id')->on('assignment_comments')->cascadeOnDelete();
 }); }
 public function down(): void { Schema::dropIfExists('assignment_comments'); }
};
