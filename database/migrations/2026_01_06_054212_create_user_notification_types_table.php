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
        Schema::create('user_notification_types', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('notification_type_id')->constrained()->cascadeOnDelete();
            $table->boolean('is_enabled')->default(true); // この通知タイプを有効にするか
            $table->timestamps();
            
            // ユーザーと通知タイプの組み合わせは一意
            $table->unique(['user_id', 'notification_type_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_notification_types');
    }
};
