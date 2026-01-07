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
        Schema::create('friend_categories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete(); // カテゴリーを作成したユーザー
            $table->string('name'); // カテゴリー名
            $table->integer('order')->default(0); // 表示順序
            $table->timestamps();
            
            // ユーザーごとに同じ名前のカテゴリーを作れないようにする（オプション）
            // $table->unique(['user_id', 'name']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('friend_categories');
    }
};
