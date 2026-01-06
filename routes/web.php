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

        // 検索クエリがある場合
        $searchQuery = $request->input('search');
        $searchResults = collect();
        
        if ($searchQuery) {
            $searchResults = App\Models\User::where('id', '!=', $user->id)
                ->where(function($query) use ($searchQuery) {
                    $query->where('username', 'like', '%' . $searchQuery . '%')
                          ->orWhere('name', 'like', '%' . $searchQuery . '%');
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

        // 通知タイプを取得（システム定義 + カスタム）
        $systemTypes = App\Models\NotificationType::where('is_active', true)->get();
        $customTypes = App\Models\CustomNotificationType::where('user_id', $user->id)
            ->where('is_active', true)
            ->get();

        return view('dashboard', [
            'friends' => $friends,
            'others' => $others,
            'searchResults' => $searchResults,
            'searchQuery' => $searchQuery,
            'systemTypes' => $systemTypes,
            'customTypes' => $customTypes,
        ]);
    })->name('dashboard');

    // ... その他のルート（友達追加、チャット、プロフィールなど）はそのまま
    Route::post('/friends/{user_id}', function ($user_id) {
        auth()->user()->friends()->syncWithoutDetaching([$user_id]);
        return back();
    })->name('friends.add');
    
    Route::get('/chat/{user?}', [App\Http\Controllers\ChatController::class, 'index'])->name('chat');
    Route::post('/chat/{user}', [App\Http\Controllers\ChatController::class, 'store'])->name('chat.store');
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
});
// require __DIR__.'/auth.php'; //