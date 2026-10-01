<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /** Help requests sent from the Support page (any role); handled by admins. */
    public function up(): void
    {
        Schema::create('support_requests', function (Blueprint $table) {
            $table->increments('support_request_id');
            $table->unsignedInteger('user_id');
            $table->string('category', 60);
            $table->enum('priority', ['Low', 'Normal', 'High', 'Urgent'])->nullable();
            $table->string('subject', 120);
            $table->text('message');
            $table->string('attachment_path', 500)->nullable();
            $table->string('attachment_name', 255)->nullable();
            $table->enum('status', ['Open', 'Resolved'])->default('Open');
            $table->dateTime('resolved_at')->nullable();
            $table->timestamps();

            $table->index('status', 'idx_support_status');
            $table->foreign('user_id')->references('user_id')->on('users')->cascadeOnDelete()->cascadeOnUpdate();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('support_requests');
    }
};
