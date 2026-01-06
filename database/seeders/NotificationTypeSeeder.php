<?php

namespace Database\Seeders;

use App\Models\NotificationType;
use Illuminate\Database\Seeder;

class NotificationTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $types = [
            // 1. システム設定風（グレー基調）- 最も警戒すべき相手
            [
                'name' => 'システム設定',
                'app_name' => '設定',
                'icon' => '⚙️',
                'color' => '#6B7280',
                'theme_type' => 'system',
                'description' => 'システム設定風の通知で、最も警戒すべき相手からの連絡を受け取ります',
                'is_active' => true,
            ],
            // 2. 天気予報風（ブルー/オレンジ基調）- パートナー/家族
            [
                'name' => '天気予報',
                'app_name' => '天気',
                'icon' => '☀️',
                'color' => '#3B82F6',
                'theme_type' => 'weather',
                'description' => '天気予報風の通知で、パートナーや家族からの連絡を受け取ります',
                'is_active' => true,
            ],
            // 3. 広告・クーポン風（赤/緑基調）- 友人/遊び仲間
            [
                'name' => 'ショッピング',
                'app_name' => 'Uber Eats',
                'icon' => '🛒',
                'color' => '#EF4444',
                'theme_type' => 'ad',
                'description' => 'ショッピングアプリ風の通知で、友人や遊び仲間からの連絡を受け取ります',
                'is_active' => true,
            ],
            // 4. カレンダー・タスク風（白/青基調）- 仕事関係
            [
                'name' => 'カレンダー',
                'app_name' => 'カレンダー',
                'icon' => '📅',
                'color' => '#2563EB',
                'theme_type' => 'calendar',
                'description' => 'カレンダー風の通知で、仕事関係からの連絡を受け取ります',
                'is_active' => true,
            ],
            // 5. ゲーム・SNS風（カラフル）- 趣味の友達
            [
                'name' => 'ゲーム',
                'app_name' => 'ゲーム',
                'icon' => '🎮',
                'color' => '#8B5CF6',
                'theme_type' => 'game',
                'description' => 'ゲーム風の通知で、趣味の友達からの連絡を受け取ります',
                'is_active' => true,
            ],
        ];

        foreach ($types as $type) {
            NotificationType::create($type);
        }
    }
}
