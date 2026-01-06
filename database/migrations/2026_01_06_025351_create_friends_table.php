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
        Schema::create('friends', function (Blueprint $table) {
            $table->id();
            // 自分
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            // 相手（usersテーブルのidを参照）
            $table->foreignId('friend_id')->constrained('users')->cascadeOnDelete();
            
            // 同じ人と何度も友達登録できないようにする設定
            $table->unique(['user_id', 'friend_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('friends');
    }
};