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
        Schema::create('user_sender_notification_types', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete(); // 受信者（自分）
            $table->foreignId('sender_id')->constrained('users')->cascadeOnDelete(); // 送信者（チャット相手）
            $table->foreignId('notification_type_id')->nullable()->constrained()->nullOnDelete(); // 通知タイプ（nullの場合はデフォルト）
            $table->timestamps();
            
            // 同じ送信者からの通知設定は1つだけ
            $table->unique(['user_id', 'sender_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_sender_notification_types');
    }
};
