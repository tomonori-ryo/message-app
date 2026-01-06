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
        Schema::table('notification_types', function (Blueprint $table) {
            $table->string('app_name')->nullable()->after('name'); // 偽装アプリ名（例：設定、天気）
            $table->string('color')->nullable()->after('icon'); // テーマカラー（例：#6B7280）
            $table->string('theme_type')->nullable()->after('color'); // テーマタイプ（system, weather, ad, calendar, game）
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('notification_types', function (Blueprint $table) {
            $table->dropColumn(['app_name', 'color', 'theme_type']);
        });
    }
};
