<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mentor_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('mentor_chat_id')->constrained('mentor_chats')->onDelete('cascade');
            $table->enum('sender', ['user', 'mentor']);
            $table->text('content');
            $table->json('supported_actions')->nullable(); // لتخزين الأفعال والروابط المدعومة
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mentor_messages');
    }
};
