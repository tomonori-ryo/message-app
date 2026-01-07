<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Message;
use App\Models\Notification;
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
            
            // ブロック関係を確認
            $isBlocked = $currentUser->blockedUsers()->where('users.id', $userId)->exists() ||
                        $currentUser->blockedBy()->where('users.id', $userId)->exists();
            
            if ($isBlocked) {
                abort(403, 'このユーザーとのチャットは許可されていません');
            }
            
            // 友達関係を確認
            $isFriend = $currentUser->friends()->where('friend_id', $userId)->exists() ||
                       $chatPartner->friends()->where('friend_id', $currentUser->id)->exists();
            
            if (!$isFriend) {
                abort(403, 'このユーザーとのチャットは許可されていません');
            }
            
            // 相手が自分をブロックしているか、友達解除しているかを確認
            $partnerBlockedMe = $chatPartner->blockedUsers()->where('users.id', $currentUser->id)->exists();
            $partnerRemovedMe = !$chatPartner->friends()->where('friend_id', $currentUser->id)->exists() && 
                               $currentUser->friends()->where('friend_id', $userId)->exists();
            
            // 現在のユーザーと指定されたユーザー間のメッセージのみを取得
            // セキュリティのため、必ず両方のユーザーIDを条件に含める
            $messages = Message::where(function($query) use ($currentUser, $userId) {
                // 新しい形式：receiver_idが指定されているメッセージ
                // 現在のユーザーからチャット相手へのメッセージ
                $query->where(function($q) use ($currentUser, $userId) {
                    $q->where('user_id', $currentUser->id)
                      ->where('receiver_id', $userId);
                })
                // チャット相手から現在のユーザーへのメッセージ
                ->orWhere(function($q) use ($currentUser, $userId) {
                    $q->where('user_id', $userId)
                      ->where('receiver_id', $currentUser->id);
                });
            })
            ->with(['user', 'receiver'])
            ->oldest()
            ->get();
        } else {
            // ユーザーIDが指定されていない場合は、最新のチャット相手を表示
            // セキュリティのため、必ず現在のユーザーが送信者または受信者のメッセージのみを取得
            $latestMessage = Message::where(function($query) use ($currentUser) {
                // 現在のユーザーが送信者で、receiver_idが設定されているメッセージ
                $query->where(function($q) use ($currentUser) {
                    $q->where('user_id', $currentUser->id)
                      ->whereNotNull('receiver_id');
                })
                // 現在のユーザーが受信者のメッセージ
                ->orWhere(function($q) use ($currentUser) {
                    $q->where('receiver_id', $currentUser->id);
                });
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
        
        // チャット画面を開いたときに、このチャット相手からの未読通知を既読にする
        if ($chatPartner) {
            Notification::where('user_id', $currentUser->id)
                ->where('sender_id', $userId)
                ->where('is_read', false)
                ->update([
                    'is_read' => true,
                    'read_at' => now(),
                ]);
        }
        
        // 最新のアナウンスを取得（閉じられていないもののみ）
        $latestAnnouncement = null;
        $chatPartnerDisplayName = null;
        if ($chatPartner) {
            // 閉じられたアナウンスのIDを取得
            $dismissedAnnouncementIds = DB::table('user_dismissed_announcements')
                ->where('user_id', $currentUser->id)
                ->pluck('message_id')
                ->toArray();
            
            $latestAnnouncement = Message::where(function($query) use ($currentUser, $userId) {
                $query->where(function($q) use ($currentUser, $userId) {
                    $q->where('user_id', $currentUser->id)
                      ->where('receiver_id', $userId);
                })->orWhere(function($q) use ($currentUser, $userId) {
                    $q->where('user_id', $userId)
                      ->where('receiver_id', $currentUser->id);
                });
            })
            ->where('is_announcement', true)
            ->whereNotIn('id', $dismissedAnnouncementIds) // 閉じられたアナウンスを除外
            ->latest()
            ->first();
            
            // チャット相手の表示名を取得
            $friendRelation = DB::table('friends')
                ->where('user_id', $currentUser->id)
                ->where('friend_id', $userId)
                ->first();
            $chatPartnerDisplayName = $friendRelation->display_name ?? null;
        }
        
        return view('chat', [
            'messages' => $messages,
            'notificationTypes' => $allTypesForSelection,
            'chatPartner' => $chatPartner,
            'chatPartnerDisplayName' => $chatPartnerDisplayName,
            'senderNotificationTypes' => $senderNotificationTypes,
            'partnerBlockedMe' => $partnerBlockedMe ?? false,
            'partnerRemovedMe' => $partnerRemovedMe ?? false,
            'latestAnnouncement' => $latestAnnouncement,
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

    /**
     * メッセージを削除
     */
    public function destroy(Message $message)
    {
        $user = Auth::user();
        
        // 自分のメッセージのみ削除可能
        if ($message->user_id !== $user->id) {
            abort(403, 'このメッセージを削除する権限がありません');
        }
        
        $message->delete();
        
        return response()->json(['success' => true]);
    }

    /**
     * アナウンスを保存
     */
    public function announce(Request $request, $userId)
    {
        $request->validate([
            'message' => 'required|string|max:500',
        ]);

        $receiver = \App\Models\User::findOrFail($userId);
        $currentUser = $request->user();
        
        // 友達関係を確認
        $isFriend = $currentUser->friends()->where('friend_id', $userId)->exists() ||
                   $receiver->friends()->where('friend_id', $currentUser->id)->exists();
        
        if (!$isFriend) {
            abort(403, 'このユーザーにアナウンスを送信することは許可されていません');
        }

        // アナウンスとしてメッセージを保存
        $message = $currentUser->messages()->create([
            'body' => $request->input('message'),
            'receiver_id' => $userId,
            'is_announcement' => true,
        ]);

        return response()->json([
            'success' => true,
            'message' => $message->body,
        ]);
    }

    /**
     * アナウンスを閉じたことを記録
     */
    public function dismissAnnouncement(Request $request, Message $message)
    {
        $user = Auth::user();
        
        // アナウンスかどうか確認
        if (!$message->is_announcement) {
            abort(400, 'このメッセージはアナウンスではありません');
        }
        
        // このアナウンスが現在のユーザーに関連しているか確認
        $isRelated = ($message->user_id === $user->id && $message->receiver_id) ||
                    ($message->receiver_id === $user->id && $message->user_id);
        
        if (!$isRelated) {
            abort(403, 'このアナウンスを閉じる権限がありません');
        }
        
        // 閉じたことを記録（重複を防ぐ）
        DB::table('user_dismissed_announcements')->updateOrInsert(
            [
                'user_id' => $user->id,
                'message_id' => $message->id,
            ],
            [
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );
        
        return response()->json(['success' => true]);
    }
}