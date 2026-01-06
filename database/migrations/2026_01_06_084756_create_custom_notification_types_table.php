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
        Schema::create('custom_notification_types', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete(); // 作成者
            $table->string('name'); // 通知タイプ名
            $table->string('app_name')->nullable(); // 偽装アプリ名
            $table->string('icon')->nullable(); // デフォルトアイコン（絵文字など）
            $table->string('icon_image')->nullable(); // カスタムアイコン画像
            $table->string('color')->nullable(); // テーマカラー
            $table->string('theme_type')->nullable(); // テーマタイプ（system, weather, ad, calendar, game）
            $table->text('description')->nullable(); // 説明
            $table->boolean('is_active')->default(true); // 有効/無効
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('custom_notification_types');
    }
};
