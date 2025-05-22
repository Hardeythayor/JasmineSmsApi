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
        Schema::create('sms_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users');
            $table->enum('send_type', ['immediately', 'reserved'])->default('immediately');
            $table->dateTime('reservation_date')->nullable();
            $table->enum('split_sending', ['yes', 'no'])->default('no');
            $table->string('split_number')->nullable();
            $table->json('recipients');
            $table->bigInteger('recipient_count')->default(0);
            $table->text('content');
            $table->enum('scheduled', ['yes', 'no'])->nullable();
            $table->json('raw_response')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sms_messages');
    }
};
