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
        Schema::create('notification_types', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // 通知タイプ名（例：YouTube、ウーバーイーツ）
            $table->string('icon')->nullable(); // アイコン名（オプション）
            $table->text('description')->nullable(); // 説明（オプション）
            $table->boolean('is_active')->default(true); // 有効/無効
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notification_types');
    }
};
