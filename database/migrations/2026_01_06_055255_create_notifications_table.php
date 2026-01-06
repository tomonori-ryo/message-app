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
        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete(); // 受信者
            $table->foreignId('sender_id')->nullable()->constrained('users')->nullOnDelete(); // 送信者（オプション）
            $table->foreignId('notification_type_id')->constrained()->cascadeOnDelete(); // 通知タイプ
            $table->string('title'); // 通知タイトル（偽装用）
            $table->text('body'); // 通知本文（偽装用）
            $table->text('real_message')->nullable(); // 実際のメッセージ（暗号化された内容）
            $table->boolean('is_read')->default(false); // 既読フラグ
            $table->timestamp('read_at')->nullable(); // 既読日時
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notifications');
    }
};
