<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Message;
use App\Models\NotificationType;
use App\Models\CustomNotificationType;
use App\Models\UserSenderNotificationType;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ChatController extends Controller
{
    // 1. チャット画面を表示
    public function index($userId = null)
    {
        $currentUser = Auth::user();
        $chatPartner = null;
        
        // ユーザーIDが指定されている場合、そのユーザーとのチャットを表示
        if ($userId) {
            $chatPartner = \App\Models\User::findOrFail($userId);
            
            // 友達関係を確認
            $isFriend = $currentUser->friends()->where('friend_id', $userId)->exists() ||
                       $chatPartner->friends()->where('friend_id', $currentUser->id)->exists();
            
            if (!$isFriend) {
                abort(403, 'このユーザーとのチャットは許可されていません');
            }
            
            // 現在のユーザーと指定されたユーザー間のメッセージのみを取得
            // 後方互換性のため、receiver_idがnullのメッセージも含める（古いメッセージ）
            $messages = Message::where(function($query) use ($currentUser, $userId) {
                $query->where(function($q) use ($currentUser, $userId) {
                    // 新しい形式：receiver_idが指定されているメッセージ
                    $q->where(function($subQ) use ($currentUser, $userId) {
                        $subQ->where('user_id', $currentUser->id)
                             ->where('receiver_id', $userId);
                    })->orWhere(function($subQ) use ($currentUser, $userId) {
                        $subQ->where('user_id', $userId)
                             ->where('receiver_id', $currentUser->id);
                    });
                })->orWhere(function($q) use ($currentUser, $userId) {
                    // 古い形式：receiver_idがnullのメッセージ（後方互換性のため）
                    $q->whereNull('receiver_id')
                      ->where(function($subQ) use ($currentUser, $userId) {
                          $subQ->where('user_id', $currentUser->id)
                               ->orWhere('user_id', $userId);
                      });
                });
            })
            ->with(['user', 'receiver'])
            ->oldest()
            ->get();
        } else {
            // ユーザーIDが指定されていない場合は、最新のチャット相手を表示
            $latestMessage = Message::where(function($query) use ($currentUser) {
                $query->where('user_id', $currentUser->id)
                      ->orWhere('receiver_id', $currentUser->id);
            })
            ->latest()
            ->first();
            
            if ($latestMessage) {
                $chatPartner = $latestMessage->user_id == $currentUser->id 
                    ? $latestMessage->receiver 
                    : $latestMessage->user;
                
                if ($chatPartner) {
                    // リダイレクトしてチャットパートナーを指定
                    return redirect()->route('chat', ['user' => $chatPartner->id]);
                }
            }
            
            $messages = collect();
        }
        
        // 通知タイプを取得（システム定義 + カスタム）
        $systemTypes = NotificationType::where('is_active', true)->get();
        $customTypes = CustomNotificationType::where('user_id', Auth::id())
            ->where('is_active', true)
            ->get();
        
        // 全ての通知タイプをマージ
        $allNotificationTypes = $systemTypes->concat($customTypes);
        
        // チャット相手ごとの通知タイプ設定を取得
        $senderNotificationTypes = [];
        if ($chatPartner) {
            $setting = UserSenderNotificationType::where('user_id', Auth::id())
                ->where('sender_id', $chatPartner->id)
                ->first();
            $senderNotificationTypes[$chatPartner->id] = $setting ? $setting->notification_type_id : null;
        }
        
        // カスタム通知タイプも含める（通知タイプ選択用）
        $allTypesForSelection = $systemTypes->map(function($type) {
            $type->is_custom = false;
            return $type;
        })->concat($customTypes->map(function($type) {
            $type->is_custom = true;
            return $type;
        }));
        
        return view('chat', [
            'messages' => $messages,
            'notificationTypes' => $allTypesForSelection,
            'chatPartner' => $chatPartner,
            'senderNotificationTypes' => $senderNotificationTypes,
        ]);
    }

    // 2. メッセージを保存
    public function store(Request $request, $userId)
    {
        // 空っぽ送信を防ぐ
        $request->validate(['body' => 'required']);

        $receiver = \App\Models\User::findOrFail($userId);
        $currentUser = $request->user();
        
        // 友達関係を確認
        $isFriend = $currentUser->friends()->where('friend_id', $userId)->exists() ||
                   $receiver->friends()->where('friend_id', $currentUser->id)->exists();
        
        if (!$isFriend) {
            abort(403, 'このユーザーにメッセージを送信することは許可されていません');
        }

        // ログイン中のユーザーIDと受信者IDを使って保存
        $currentUser->messages()->create([
            'body' => $request->input('body'),
            'receiver_id' => $userId
        ]);

        // 元の画面に戻る
        return back();
    }

    /**
     * チャット相手ごとの通知タイプ設定を更新
     */
    public function updateSenderNotificationType(Request $request)
    {
        $request->validate([
            'sender_id' => 'required|exists:users,id',
            'notification_type_id' => 'nullable',
        ]);

        $user = Auth::user();
        $senderId = $request->sender_id;
        $notificationTypeId = $request->notification_type_id;

        // notification_type_idがnullの場合は削除
        if ($notificationTypeId === null || $notificationTypeId === '') {
            UserSenderNotificationType::where('user_id', $user->id)
                ->where('sender_id', $senderId)
                ->delete();
        } else {
            // 通知タイプが存在するか確認（システム定義またはカスタム）
            $exists = NotificationType::where('id', $notificationTypeId)->exists() ||
                     CustomNotificationType::where('id', $notificationTypeId)
                         ->where('user_id', $user->id)
                         ->exists();

            if (!$exists) {
                return response()->json(['error' => '無効な通知タイプです'], 400);
            }

            UserSenderNotificationType::updateOrCreate(
                [
                    'user_id' => $user->id,
                    'sender_id' => $senderId,
                ],
                [
                    'notification_type_id' => $notificationTypeId,
                ]
            );
        }

        return response()->json(['success' => true]);
    }
}