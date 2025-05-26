<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('message_recipients', function (Blueprint $table) {
            $table->id();
            $table->foreignId('message_id')->references('id')->on('sms_messages');
            $table->string('phone_number');
            $table->enum('status', ['completed', 'pending', 'failed'])->default('pending');
            $table->text('fail_reason')->nullable();
            $table->string('sent_at')->nullable();
            $table->string('phone_sms_id')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('message_recipients');
    }
};
