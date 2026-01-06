<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use App\Models\NotificationType;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class NotificationController extends Controller
{
    /**
     * 通知設定画面を表示
     */
    public function index()
    {
        $user = Auth::user();
        $notificationTypes = NotificationType::where('is_active', true)->get();
        // ユーザーが有効にしている通知タイプのIDを取得
        $userNotificationTypes = $user->notificationTypes()
            ->wherePivot('is_enabled', true)
            ->withPivot('icon_image', 'is_enabled')
            ->pluck('notification_type_id')
            ->toArray();
        
        return view('notifications.settings', compact('user', 'notificationTypes', 'userNotificationTypes'));
    }

    /**
     * 通知設定を更新
     */
    public function update(Request $request)
    {
        $request->validate([
            'notification_type' => 'required|exists:notification_types,id',
        ]);

        $user = Auth::user();
        $selectedTypeId = $request->input('notification_type');
        
        // 全ての通知タイプを取得
        $allTypes = NotificationType::where('is_active', true)->pluck('id');
        
        // トランザクションで一括更新
        DB::transaction(function () use ($user, $selectedTypeId, $allTypes) {
            foreach ($allTypes as $typeId) {
                $isEnabled = ($typeId == $selectedTypeId);
                
                // 既存の設定を更新または新規作成
                $user->notificationTypes()->syncWithoutDetaching([
                    $typeId => ['is_enabled' => $isEnabled]
                ]);
            }
        });

        return back()->with('success', '通知設定を更新しました');
    }

    /**
     * 通知を送信
     */
    public function send(Request $request)
    {
        $request->validate([
            'receiver_id' => 'required|exists:users,id',
            'notification_type_id' => 'required',
            'message' => 'required|string|max:500',
        ]);

        $sender = Auth::user();
        $receiver = User::findOrFail($request->receiver_id);
        
        // 通知タイプを取得（システム定義またはカスタム）
        $notificationType = NotificationType::find($request->notification_type_id);
        $customType = null;
        
        if (!$notificationType) {
            $customType = \App\Models\CustomNotificationType::where('id', $request->notification_type_id)
                ->where('user_id', $sender->id)
                ->first();
            
            if (!$customType) {
                return response()->json(['error' => '無効な通知タイプです'], 400);
            }
        }

        // システム定義の通知タイプの場合、受信者が有効にしているか確認
        if ($notificationType) {
            $isEnabled = $receiver->notificationTypes()
                ->where('notification_type_id', $notificationType->id)
                ->wherePivot('is_enabled', true)
                ->exists();

            if (!$isEnabled) {
                return response()->json(['error' => '受信者がこの通知タイプを有効にしていません'], 400);
            }
        }

        // 偽装通知のタイトルと本文を生成
        $typeForCamouflage = $notificationType ?? $customType;
        $camouflage = $this->generateCamouflage($typeForCamouflage, $request->message);

        // アイコン画像のパスを取得
        $iconPath = null;
        if ($notificationType) {
            $userNotificationType = $receiver->notificationTypes()
                ->where('notification_type_id', $notificationType->id)
                ->first();
            if ($userNotificationType && $userNotificationType->pivot->icon_image) {
                $iconPath = Storage::url($userNotificationType->pivot->icon_image);
            }
        } elseif ($customType && $customType->icon_image) {
            $iconPath = Storage::url($customType->icon_image);
        }

        // 通知を作成（カスタムタイプの場合はnotification_type_idをnullに）
        $notification = Notification::create([
            'user_id' => $receiver->id,
            'sender_id' => $sender->id,
            'notification_type_id' => $notificationType ? $notificationType->id : null,
            'title' => $camouflage['title'],
            'body' => $camouflage['body'],
            'real_message' => $request->message,
        ]);

        return response()->json([
            'success' => true,
            'notification' => $notification->load('notificationType', 'sender'),
            'title' => $camouflage['title'],
            'body' => $camouflage['body'],
            'icon' => $iconPath ? url($iconPath) : null,
            'app_name' => $typeForCamouflage->app_name ?? $typeForCamouflage->name,
        ]);
    }

    /**
     * 通知一覧を取得（JSON）
     */
    public function list()
    {
        $user = Auth::user();
        $notifications = $user->notifications()
            ->with(['notificationType', 'sender'])
            ->latest()
            ->limit(50)
            ->get()
            ->map(function($notification) use ($user) {
                // アイコン画像のパスを追加
                $iconPath = null;
                if ($notification->notification_type_id) {
                    $userNotificationType = $user->notificationTypes()
                        ->where('notification_type_id', $notification->notification_type_id)
                        ->first();
                    if ($userNotificationType && $userNotificationType->pivot->icon_image) {
                        $iconPath = Storage::url($userNotificationType->pivot->icon_image);
                    }
                }
                
                $notificationArray = $notification->toArray();
                if ($iconPath) {
                    $notificationArray['icon_image'] = url($iconPath);
                }
                return $notificationArray;
            });

        return response()->json($notifications);
    }

    /**
     * 通知一覧画面を表示
     */
    public function listView()
    {
        return view('notifications.index');
    }

    /**
     * 通知を既読にする
     */
    public function markAsRead(Notification $notification)
    {
        if ($notification->user_id !== Auth::id()) {
            abort(403);
        }

        $notification->update([
            'is_read' => true,
            'read_at' => now(),
        ]);

        return response()->json(['success' => true]);
    }

    /**
     * 偽装通知のタイトルと本文を生成
     */
    private function generateCamouflage($type, string $realMessage): array
    {
        $templates = [
            'system' => [
                'titles' => [
                    'iCloudバックアップ',
                    'システム設定',
                    'ストレージ',
                    'スクリーンタイム',
                    'セキュリティ',
                    'バッテリー',
                    'Wi-Fi',
                    'Wallet',
                ],
                'bodies' => [
                    '前回のバックアップが正常に完了しました。',
                    'iOS 17.x のアップデート準備ができました。',
                    '重複した写真を整理して空き容量を増やせます。',
                    '先週の週間レポートが利用可能です。',
                    'パスワードの安全性を確認してください。',
                    'バックグラウンド処理を最適化しました。',
                    '公衆無線LANネットワークが利用可能です。',
                    'Apple Payの設定が完了していない可能性があります。',
                ],
            ],
            'weather' => [
                'titles' => [
                    '明日の天気',
                    '雨雲レーダー',
                    '気象情報',
                    '花粉情報',
                    '週間予報',
                    '台風情報',
                    '熱中症アラート',
                ],
                'bodies' => [
                    '明日は晴れのち曇り、降水確率は20%です。',
                    '1時間以内に雨が降り出す可能性があります。',
                    '乾燥注意報が発令されています。火の元にご注意ください。',
                    '明日の飛散量は「非常に多い」見込みです。',
                    '週末にかけて気温が下がる見込みです。',
                    '台風が発生しました。進路図を確認してください。',
                    '本日は「厳重警戒」レベルです。水分補給を忘れずに。',
                ],
            ],
            'ad' => [
                'titles' => [
                    '発送通知',
                    '配達完了',
                    '500円OFFクーポン',
                    '再入荷のお知らせ',
                    'ポイント有効期限',
                    'タイムセール',
                    'アンケートのお願い',
                ],
                'bodies' => [
                    'ご注文の商品が倉庫から発送されました。',
                    'お荷物を宅配ボックスにお届けしました。',
                    '有効期限が迫っています！今すぐ注文しましょう。',
                    'お気に入り登録した商品が入荷しました。',
                    '今月末で失効するポイントがあります。',
                    '24時間限定！対象商品が最大50%OFF。',
                    'ご利用の感想をお聞かせください（所要時間：1分）',
                ],
            ],
            'calendar' => [
                'titles' => [
                    '次の予定',
                    'カレンダー',
                    'リマインダー',
                    '共有カレンダー',
                    'ToDoリスト',
                    '定期バックアップ',
                ],
                'bodies' => [
                    '定例ミーティング（開始15分前）',
                    '明日は祝日です。アラーム設定を確認してください。',
                    '今日のタスク：銀行振込、資料確認',
                    '「プロジェクトA」に変更がありました。',
                    '期限切れのタスクが3件あります。',
                    'ドライブへの保存が完了しました。',
                ],
            ],
            'game' => [
                'titles' => [
                    'スタミナが全回復しました！冒険に出かけよう',
                    'ログインボーナスを受け取っていません',
                    '新しいフォロワー：ユーザー名さんがあなたをフォローしました',
                    'レベルアップ：おめでとうございます！',
                    'ギフトボックス：新しいアイテムが届きました',
                ],
                'bodies' => [
                    'スタミナが完全に回復しました。新しい冒険に出かけましょう',
                    '本日のログインボーナスを受け取っていません。受け取って特典をゲットしましょう',
                    '新しいフォロワーがあなたをフォローしました。プロフィールを確認してみましょう',
                    'レベルアップしました！新しいスキルやアイテムが解放されました',
                    'ギフトボックスに新しいアイテムが届きました。受け取って確認しましょう',
                ],
            ],
            'news' => [
                'titles' => [
                    'ニュース速報',
                    '今日のまとめ',
                    '注目記事',
                    'スポーツ速報',
                    '株価アラート',
                    'トレンド',
                ],
                'bodies' => [
                    '【経済】日経平均、小幅な値動き続く',
                    '主要ニュースのダイジェストが届きました。',
                    '今もっとも読まれている記事をチェック。',
                    '昨夜の試合結果ハイライト',
                    '登録銘柄の株価が目標値に達しました。',
                    'Twitterで話題のトピック：季節の変わり目',
                ],
            ],
        ];

        $theme = $type->theme_type ?? 'system';
        $template = $templates[$theme] ?? $templates['system'];

        return [
            'title' => $template['titles'][array_rand($template['titles'])],
            'body' => $template['bodies'][array_rand($template['bodies'])],
        ];
    }

    /**
     * 通知タイプのアイコン画像を更新
     */
    public function updateIcon(Request $request)
    {
        try {
            $request->validate([
                'notification_type_id' => 'required',
                'icon_image' => 'required|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
                'type_kind' => 'nullable|in:system,custom',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'error' => 'バリデーションエラー',
                'message' => $e->getMessage(),
                'errors' => $e->errors()
            ], 422);
        }

        $user = Auth::user();
        $typeId = $request->notification_type_id;
        $kind = $request->input('type_kind', 'system');

        try {
            if ($kind === 'custom') {
                // カスタム通知タイプの場合
                $customType = \App\Models\CustomNotificationType::where('id', $typeId)
                    ->where('user_id', $user->id)
                    ->firstOrFail();

                // 既存のアイコンを削除
                if ($customType->icon_image) {
                    Storage::disk('public')->delete($customType->icon_image);
                }

                // 新しいアイコンを保存
                $path = $request->file('icon_image')->store('custom-notification-icons', 'public');
                $customType->update(['icon_image' => $path]);

                return response()->json(['success' => true, 'icon_path' => Storage::url($path)]);
            } else {
                // システム定義の通知タイプの場合
                // 通知タイプが存在するか確認
                $notificationType = NotificationType::find($typeId);
                if (!$notificationType) {
                    return response()->json(['error' => '通知タイプが見つかりません'], 404);
                }

                // 既存のアイコンを削除
                $existing = $user->notificationTypes()
                    ->where('notification_type_id', $typeId)
                    ->first();
                
                if ($existing && $existing->pivot->icon_image) {
                    Storage::disk('public')->delete($existing->pivot->icon_image);
                }

                // 新しいアイコンを保存
                $path = $request->file('icon_image')->store('notification-icons', 'public');

                // ピボットテーブルを更新
                $user->notificationTypes()->syncWithoutDetaching([
                    $typeId => ['icon_image' => $path]
                ]);

                return response()->json(['success' => true, 'icon_path' => Storage::url($path)]);
            }
        } catch (\Exception $e) {
            \Log::error('Icon upload error: ' . $e->getMessage());
            return response()->json([
                'error' => 'アイコンのアップロードに失敗しました',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * 通知タイプのアイコン画像を削除
     */
    public function deleteIcon($typeId)
    {
        $user = Auth::user();
        
        $existing = $user->notificationTypes()
            ->where('notification_type_id', $typeId)
            ->first();
        
        if ($existing && $existing->pivot->icon_image) {
            Storage::disk('public')->delete($existing->pivot->icon_image);
            
            $user->notificationTypes()->syncWithoutDetaching([
                $typeId => ['icon_image' => null]
            ]);
        }

        return response()->json(['success' => true]);
    }

    /**
     * カスタム通知タイプを作成
     */
    public function createCustomType(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'app_name' => 'nullable|string|max:255',
            'icon' => 'nullable|string|max:10',
            'icon_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'color' => 'nullable|string|max:7',
            'theme_type' => 'nullable|string|in:system,weather,ad,calendar,game',
            'description' => 'nullable|string|max:500',
        ]);

        $user = Auth::user();
        
        $data = [
            'user_id' => $user->id,
            'name' => $request->name,
            'app_name' => $request->app_name,
            'icon' => $request->icon,
            'color' => $request->color ?? '#6B7280',
            'theme_type' => $request->theme_type ?? 'system',
            'description' => $request->description,
            'is_active' => true,
        ];

        if ($request->hasFile('icon_image')) {
            $data['icon_image'] = $request->file('icon_image')->store('custom-notification-icons', 'public');
        }

        $customType = \App\Models\CustomNotificationType::create($data);

        return response()->json(['success' => true, 'custom_type' => $customType]);
    }

    /**
     * カスタム通知タイプを削除
     */
    public function deleteCustomType($id)
    {
        $customType = \App\Models\CustomNotificationType::findOrFail($id);
        
        if ($customType->user_id !== Auth::id()) {
            abort(403);
        }

        if ($customType->icon_image) {
            Storage::disk('public')->delete($customType->icon_image);
        }

        $customType->delete();

        return response()->json(['success' => true]);
    }
}

