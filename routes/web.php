<?php

use App\Http\Controllers\ProfileController; // ←★これを追加！
use App\Http\Controllers\ChatController;
use App\Http\Controllers\HomeController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\AuthenticatedSessionController; 
Route::middleware('auth')->group(function () {
    
    // ■ アカウント名設定（既存ユーザー用）
    Route::get('/username/setup', [App\Http\Controllers\Auth\UsernameSetupController::class, 'show'])->name('username.setup');
    Route::post('/username/setup', [App\Http\Controllers\Auth\UsernameSetupController::class, 'store'])->name('username.setup.store');
    
    // ■ ホーム画面（ここを玄関にします）
    Route::get('/dashboard', function (Illuminate\Http\Request $request) {
        $user = auth()->user();

        // 友達リスト（チャット相手）
        $friends = $user->friends;

        // 友達カテゴリーを取得
        $categories = $user->friendCategories;
        
        // カテゴリーごとに友達を整理
        $friendsByCategory = [];
        $uncategorizedFriends = collect();
        $friendCategoryIds = []; // 友達のカテゴリーIDを保存
        $friendDisplayNames = []; // 友達の表示名を保存
        
        foreach ($friends as $friend) {
            // pivotからcategory_idとdisplay_nameを取得
            $friendRelation = DB::table('friends')
                ->where('user_id', $user->id)
                ->where('friend_id', $friend->id)
                ->first();
            
            $categoryId = $friendRelation->category_id ?? null;
            $displayName = $friendRelation->display_name ?? null;
            
            $friendCategoryIds[$friend->id] = $categoryId;
            $friendDisplayNames[$friend->id] = $displayName;
            
            if ($categoryId) {
                if (!isset($friendsByCategory[$categoryId])) {
                    $friendsByCategory[$categoryId] = collect();
                }
                $friendsByCategory[$categoryId]->push($friend);
            } else {
                $uncategorizedFriends->push($friend);
            }
        }
        
        // 各友達の通知タイプ設定を取得（一括取得で効率化）
        $friendNotificationTypes = [];
        $friendNotificationIcons = [];
        
        if ($friends->isNotEmpty()) {
            $friendIds = $friends->pluck('id')->toArray();
            $settings = App\Models\UserSenderNotificationType::where('user_id', $user->id)
                ->whereIn('sender_id', $friendIds)
                ->get();
            
            // 設定をsender_idでインデックス化（数値キーとして）
            $settingsBySenderId = [];
            foreach ($settings as $setting) {
                $settingsBySenderId[(int)$setting->sender_id] = $setting;
            }
            
            if (!empty($settingsBySenderId)) {
                $notificationTypeIds = collect($settings)->pluck('notification_type_id')->filter()->unique()->toArray();
                
                if (!empty($notificationTypeIds)) {
                    $systemTypesMap = App\Models\NotificationType::whereIn('id', $notificationTypeIds)->get()->keyBy('id');
                    $customTypesMap = App\Models\CustomNotificationType::whereIn('id', $notificationTypeIds)
                        ->where('user_id', $user->id)
                        ->get()
                        ->keyBy('id');
                    
                    // ユーザーの通知タイプ設定（カスタムアイコン用）
                    $userNotificationTypes = $user->notificationTypes()
                        ->withPivot('icon_image')
                        ->get()
                        ->keyBy('id');
                    
                    foreach ($friends as $friend) {
                        $friendId = (int)$friend->id;
                        $setting = $settingsBySenderId[$friendId] ?? null;
                        
                        if ($setting && $setting->notification_type_id) {
                            $typeId = (int)$setting->notification_type_id;
                            
                            // システム定義の通知タイプを確認
                            if ($systemTypesMap->has($typeId)) {
                                $systemType = $systemTypesMap->get($typeId);
                                $friendNotificationTypes[$friendId] = $systemType;
                                
                                // カスタムアイコンを確認（user_notification_typesテーブルから）
                                $userNotificationType = $userNotificationTypes->get($typeId);
                                if ($userNotificationType && $userNotificationType->pivot->icon_image) {
                                    $friendNotificationIcons[$friendId] = $userNotificationType->pivot->icon_image;
                                }
                            } elseif ($customTypesMap->has($typeId)) {
                                // カスタム通知タイプを確認
                                $customType = $customTypesMap->get($typeId);
                                $friendNotificationTypes[$friendId] = $customType;
                                if ($customType->icon_image) {
                                    $friendNotificationIcons[$friendId] = $customType->icon_image;
                                }
                            }
                        }
                    }
                }
            }
        }

        // 検索クエリがある場合
        $searchQuery = $request->input('search');
        $searchResults = collect();
        
        if ($searchQuery) {
            $friendIds = $friends->pluck('id')->toArray();
            
            $searchResults = App\Models\User::where('id', '!=', $user->id)
                ->where(function($query) use ($searchQuery, $friendIds) {
                    // 友達追加されているユーザーは、名前だけで検索可能
                    $query->whereIn('id', $friendIds)
                          ->where('name', 'like', '%' . $searchQuery . '%');
                })
                ->orWhere(function($query) use ($searchQuery, $friendIds) {
                    // 友達追加されていないユーザーは、アカウント名で検索
                    // または、アカウント名が設定されているユーザーで名前で検索
                    $query->whereNotIn('id', $friendIds)
                          ->where(function($q) use ($searchQuery) {
                              $q->where('username', 'like', '%' . $searchQuery . '%')
                                ->orWhere(function($subQ) use ($searchQuery) {
                                    $subQ->whereNotNull('username')
                                         ->where('name', 'like', '%' . $searchQuery . '%');
                                });
                          });
                })
                ->get();
        }

        // まだ友達じゃない人（追加候補）- 検索時は表示しない
        $others = collect();
        if (!$searchQuery) {
        $others = App\Models\User::where('id', '!=', $user->id)
                    ->whereNotIn('id', $friends->pluck('id'))
                        ->get();
        }

        // 各友達からの未読通知があるかどうかを取得
        $friendUnreadNotifications = [];
        if ($friends->isNotEmpty()) {
            $friendIds = $friends->pluck('id')->toArray();
            // sender_idがnullでない通知のみを取得し、sender_idでグループ化
            $unreadNotifications = App\Models\Notification::where('user_id', $user->id)
                ->whereNotNull('sender_id')
                ->whereIn('sender_id', $friendIds)
                ->where('is_read', false)
                ->get();
            
            // sender_idでグループ化（数値キーとして）
            $notificationsBySender = [];
            foreach ($unreadNotifications as $notification) {
                $senderId = (int)$notification->sender_id;
                if (!isset($notificationsBySender[$senderId])) {
                    $notificationsBySender[$senderId] = [];
                }
                $notificationsBySender[$senderId][] = $notification;
            }
            
            // 各友達について未読通知があるかチェック
            foreach ($friendIds as $friendId) {
                $friendIdInt = (int)$friendId;
                $friendUnreadNotifications[$friendIdInt] = isset($notificationsBySender[$friendIdInt]) && count($notificationsBySender[$friendIdInt]) > 0;
            }
        }

        // 通知タイプを取得（システム定義 + カスタム）
        $systemTypes = App\Models\NotificationType::where('is_active', true)->get();
        $customTypes = App\Models\CustomNotificationType::where('user_id', $user->id)
            ->where('is_active', true)
                    ->get();

        return view('dashboard', [
            'friends' => $friends,
            'categories' => $categories,
            'friendsByCategory' => $friendsByCategory,
            'uncategorizedFriends' => $uncategorizedFriends,
            'friendCategoryIds' => $friendCategoryIds,
            'friendDisplayNames' => $friendDisplayNames,
            'friendUnreadNotifications' => $friendUnreadNotifications,
            'others' => $others,
            'searchResults' => $searchResults,
            'searchQuery' => $searchQuery,
            'systemTypes' => $systemTypes,
            'customTypes' => $customTypes,
            'friendNotificationTypes' => $friendNotificationTypes,
            'friendNotificationIcons' => $friendNotificationIcons,
        ]);
    })->name('dashboard');

    // ... その他のルート（友達追加、チャット、プロフィールなど）はそのまま
    Route::post('/friends/{user_id}', function ($user_id) {
        auth()->user()->friends()->syncWithoutDetaching([$user_id]);
        return back();
    })->name('friends.add');
    
    Route::get('/qr/show', [App\Http\Controllers\QrCodeController::class, 'show'])->name('qr.show');
    Route::get('/qr/scan', [App\Http\Controllers\QrCodeController::class, 'scan'])->name('qr.scan');
    Route::get('/friends/add-by-qr/{user_id}', [App\Http\Controllers\QrCodeController::class, 'addByQr'])->name('friends.add-by-qr');
    
    Route::delete('/friends/{user_id}', function ($user_id) {
        $user = auth()->user();
        // 友達関係を解除（双方向）
        $user->friends()->detach($user_id);
        // 相手からも自分を削除
        $friend = \App\Models\User::find($user_id);
        if ($friend) {
            $friend->friends()->detach($user->id);
        }
        return back()->with('success', '友達を解除しました');
    })->name('friends.remove');
    
    Route::patch('/friends/{user_id}/display-name', [App\Http\Controllers\FriendController::class, 'updateDisplayName'])->name('friends.updateDisplayName');
    
    Route::post('/blocks/{user_id}', function ($user_id) {
        $user = auth()->user();
        // ブロックする前に友達関係も解除
        $user->friends()->detach($user_id);
        $blockedUser = \App\Models\User::find($user_id);
        if ($blockedUser) {
            $blockedUser->friends()->detach($user->id);
        }
        // ブロックを追加
        $user->blockedUsers()->syncWithoutDetaching([$user_id]);
        return back()->with('success', 'ユーザーをブロックしました');
    })->name('blocks.add');
    
    Route::delete('/blocks/{user_id}', function ($user_id) {
        auth()->user()->blockedUsers()->detach($user_id);
        return back()->with('success', 'ブロックを解除しました');
    })->name('blocks.remove');
    
    Route::get('/chat/{user?}', [App\Http\Controllers\ChatController::class, 'index'])->name('chat');
    Route::post('/chat/{user}', [App\Http\Controllers\ChatController::class, 'store'])->name('chat.store');
    Route::post('/chat/{user}/announce', [App\Http\Controllers\ChatController::class, 'announce'])->name('chat.announce');
    Route::post('/chat/announcement/{message}/dismiss', [App\Http\Controllers\ChatController::class, 'dismissAnnouncement'])->name('chat.announcement.dismiss');
    Route::delete('/chat/message/{message}', [App\Http\Controllers\ChatController::class, 'destroy'])->name('chat.message.destroy');
    Route::post('/chat/sender-notification-type', [App\Http\Controllers\ChatController::class, 'updateSenderNotificationType'])->name('chat.updateSenderNotificationType');
    
    Route::get('/profile', [App\Http\Controllers\ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [App\Http\Controllers\ProfileController::class, 'update'])->name('profile.update');
    Route::post('/profile/avatar', [App\Http\Controllers\ProfileController::class, 'updateAvatar'])->name('profile.avatar.update');
    Route::delete('/profile/avatar', [App\Http\Controllers\ProfileController::class, 'deleteAvatar'])->name('profile.avatar.delete');
    Route::delete('/profile', [App\Http\Controllers\ProfileController::class, 'destroy'])->name('profile.destroy');
    Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');
    // ★メモ機能
    Route::get('/memos/latest', [App\Http\Controllers\MemoController::class, 'latest'])->name('memos.latest'); // ← これを追加！
    Route::get('/memos', [App\Http\Controllers\MemoController::class, 'index'])->name('memos.index');
    Route::get('/memos/create', [App\Http\Controllers\MemoController::class, 'create'])->name('memos.create');
    Route::post('/memos', [App\Http\Controllers\MemoController::class, 'store'])->name('memos.store');
    Route::get('/memos/{memo}', [App\Http\Controllers\MemoController::class, 'edit'])->name('memos.edit');
    Route::patch('/memos/{memo}', [App\Http\Controllers\MemoController::class, 'update'])->name('memos.update');
    Route::delete('/memos/{memo}', [App\Http\Controllers\MemoController::class, 'destroy'])->name('memos.destroy');
    Route::get('/memos/user/{user}', [App\Http\Controllers\MemoController::class, 'indexByUser'])->name('memos.by_user');
    
    // ★通知設定
    Route::get('/notifications/settings', [App\Http\Controllers\NotificationController::class, 'index'])->name('notifications.settings');
    Route::patch('/notifications/settings', [App\Http\Controllers\NotificationController::class, 'update'])->name('notifications.update');
    Route::post('/notifications/icon', [App\Http\Controllers\NotificationController::class, 'updateIcon'])->name('notifications.updateIcon');
    Route::delete('/notifications/icon/{typeId}', [App\Http\Controllers\NotificationController::class, 'deleteIcon'])->name('notifications.deleteIcon');
    Route::post('/notifications/custom-type', [App\Http\Controllers\NotificationController::class, 'createCustomType'])->name('notifications.createCustomType');
    Route::delete('/notifications/custom-type/{id}', [App\Http\Controllers\NotificationController::class, 'deleteCustomType'])->name('notifications.deleteCustomType');
    Route::post('/notifications/send', [App\Http\Controllers\NotificationController::class, 'send'])->name('notifications.send');
    Route::get('/notifications/list', [App\Http\Controllers\NotificationController::class, 'list'])->name('notifications.list');
    Route::get('/notifications', [App\Http\Controllers\NotificationController::class, 'listView'])->name('notifications.index');
    Route::patch('/notifications/{notification}/read', [App\Http\Controllers\NotificationController::class, 'markAsRead'])->name('notifications.read');
    
    // ★友達カテゴリー管理
    Route::post('/friend-categories', [App\Http\Controllers\FriendCategoryController::class, 'store'])->name('friend-categories.store');
    Route::patch('/friend-categories/{category}', [App\Http\Controllers\FriendCategoryController::class, 'update'])->name('friend-categories.update');
    Route::delete('/friend-categories/{category}', [App\Http\Controllers\FriendCategoryController::class, 'destroy'])->name('friend-categories.destroy');
    Route::post('/friend-categories/assign', [App\Http\Controllers\FriendCategoryController::class, 'assignFriend'])->name('friend-categories.assign');
});
// require __DIR__.'/auth.php'; //