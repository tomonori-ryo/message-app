@php
use Illuminate\Support\Facades\Storage;
@endphp
{{-- カード全体枠 --}}
<div class="bg-white p-3 pr-4 rounded-2xl shadow-sm border border-gray-100 flex items-center justify-between gap-3 transition active:scale-[0.99]">
    
    {{-- 左側（タップでチャットへ）：flex-1で余白を埋める --}}
    <a href="{{ route('chat', ['user' => $friend->id]) }}" class="flex-1 flex items-center gap-3 py-1">
        <div class="relative w-12 h-12 shrink-0">
            <div class="w-12 h-12 rounded-full bg-indigo-100 flex items-center justify-center text-indigo-600 font-bold text-lg overflow-hidden">
                @if($friend->avatar)
                    <img src="{{ Storage::url($friend->avatar) }}" alt="{{ $friend->name }}" class="w-full h-full object-cover">
                @else
                    {{ substr($friend->name, 0, 1) }}
                @endif
            </div>
            {{-- 未読通知の青色ドット --}}
            @php
                $hasUnread = isset($friendUnreadNotifications[(int)$friend->id]) && $friendUnreadNotifications[(int)$friend->id];
            @endphp
            @if($hasUnread)
                <div class="absolute top-0 right-0 w-3 h-3 bg-blue-500 rounded-full border-2 border-white z-10"></div>
            @endif
        </div>
        <div class="flex-1 flex items-center gap-2">
            <div class="flex-1">
                @php
                    $displayName = isset($friendDisplayNames[$friend->id]) && $friendDisplayNames[$friend->id] 
                        ? $friendDisplayNames[$friend->id] 
                        : $friend->name;
                @endphp
                <div class="font-bold text-gray-800 flex items-center gap-2">
                    <span>{{ $displayName }}</span>
                    <button onclick="editFriendDisplayName({{ $friend->id }}, '{{ addslashes($displayName) }}')" class="text-gray-400 hover:text-indigo-600 transition p-1" title="名前を編集">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-3 h-3">
                          <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0115.75 21H5.25A2.25 2.25 0 013 18.75V8.25A2.25 2.25 0 015.25 6H10" />
                        </svg>
                    </button>
                </div>
                @if($friend->username)
                    <div class="text-xs text-gray-400">{{ '@' . $friend->username }}</div>
                @else
                    <div class="text-xs text-gray-400">タップしてチャット</div>
                @endif
            </div>
            @php
                $hasNotificationType = isset($friendNotificationTypes[$friend->id]);
                $notificationType = $hasNotificationType ? $friendNotificationTypes[$friend->id] : null;
            @endphp
            @if($hasNotificationType && $notificationType)
                @php
                    $iconPath = null;
                    // カスタムアイコンを確認
                    if (isset($friendNotificationIcons[$friend->id])) {
                        $iconPath = Storage::url($friendNotificationIcons[$friend->id]);
                    } elseif (isset($notificationType->icon_image)) {
                        // カスタム通知タイプの場合
                        $iconPath = Storage::url($notificationType->icon_image);
                    }
                @endphp
                <div class="flex items-center gap-1.5 shrink-0">
                    @if($iconPath)
                        <img src="{{ $iconPath }}" alt="{{ $notificationType->name }}" class="w-5 h-5 rounded object-cover">
                    @elseif($notificationType->icon)
                        <span class="text-sm">{{ $notificationType->icon }}</span>
                    @else
                        <div class="w-5 h-5 rounded flex items-center justify-center text-[10px] font-bold text-white" style="background-color: {{ $notificationType->color ?? '#6B7280' }};">
                            {{ substr($notificationType->name ?? $notificationType->app_name ?? 'N', 0, 1) }}
                        </div>
                    @endif
                    <span class="text-xs text-indigo-600 font-medium whitespace-nowrap">{{ $notificationType->app_name ?? $notificationType->name }}</span>
                </div>
            @else
                <div class="text-xs text-gray-300 shrink-0">通知未設定</div>
            @endif
        </div>
    </a>

    {{-- 右側（メモ一覧ボタンとメニュー）：独立したリンク --}}
    <div class="flex items-center gap-2 shrink-0">
        <a href="{{ route('memos.by_user', $friend->id) }}" class="flex flex-col items-center justify-center w-14 h-12 bg-yellow-50 hover:bg-yellow-100 text-yellow-600 rounded-xl border border-yellow-100 transition">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5 mb-0.5">
              <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0115.75 21H5.25A2.25 2.25 0 013 18.75V8.25A2.25 2.25 0 015.25 6H10" />
            </svg>
            <span class="text-[10px] font-bold">メモ</span>
        </a>
        
        {{-- ドロップダウンメニュー --}}
        <div class="relative">
            <button onclick="toggleFriendMenu({{ $friend->id }})" class="p-2 text-gray-400 hover:text-gray-600 hover:bg-gray-100 rounded-full transition">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.75a.75.75 0 110-1.5.75.75 0 010 1.5zM12 12.75a.75.75 0 110-1.5.75.75 0 010 1.5zM12 18.75a.75.75 0 110-1.5.75.75 0 010 1.5z" />
                </svg>
            </button>
            
            <div id="friend-menu-{{ $friend->id }}" class="hidden absolute right-0 mt-2 w-40 bg-white rounded-xl shadow-lg border border-gray-100 overflow-hidden z-50">
                <form method="POST" action="{{ route('friends.remove', $friend->id) }}" onsubmit="return confirm('友達を解除しますか？')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 flex items-center gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4">
                          <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6a2.25 2.25 0 00-2.25 2.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15M12 9l-3 3m0 0l3 3m-3-3h12.75" />
                        </svg>
                        友達解除
                    </button>
                </form>
                <form method="POST" action="{{ route('blocks.add', $friend->id) }}" onsubmit="return confirm('このユーザーをブロックしますか？ブロックすると友達関係も解除されます。')">
                    @csrf
                    <button type="submit" class="w-full text-left px-4 py-2 text-sm text-red-500 hover:bg-red-50 flex items-center gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4">
                          <path stroke-linecap="round" stroke-linejoin="round" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636" />
                        </svg>
                        ブロック
                    </button>
                </form>
            </div>
        </div>
    </div>

</div>

