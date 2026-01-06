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
        Schema::table('user_notification_types', function (Blueprint $table) {
            $table->string('icon_image')->nullable()->after('is_enabled'); // カスタムアイコン画像のパス
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('user_notification_types', function (Blueprint $table) {
            $table->dropColumn('icon_image');
        });
    }
};
