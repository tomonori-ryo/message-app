<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>チャット</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
@php
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
@endphp
<body class="bg-slate-100 font-sans text-gray-900 antialiased overflow-hidden fixed w-full h-full">

    {{-- ■ ヘッダー --}}
    <div class="fixed top-0 left-0 right-0 bg-white border-b z-40 h-16 flex items-center justify-between px-4 shadow-sm">
        <div class="flex items-center gap-4">
            <a href="{{ route('dashboard') }}" class="text-gray-500 hover:text-gray-800 transition p-2 -ml-2 rounded-full hover:bg-gray-100">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-6 h-6">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5" />
                </svg>
            </a>
            <h1 class="font-bold text-lg text-gray-800">
                @if($chatPartner)
                    @if(isset($partnerBlockedMe) && $partnerBlockedMe || isset($partnerRemovedMe) && $partnerRemovedMe)
                        ユーザーが退室しました
                    @else
                        {{ $chatPartnerDisplayName ?? $chatPartner->name }}
                    @endif
                @else
                    チャットルーム
                @endif
            </h1>
        </div>
        
        {{-- メニューボタン --}}
        <div class="relative z-50">
            <button id="menu-btn" class="p-2 rounded-full hover:bg-gray-100 transition text-gray-600">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-7 h-7">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" />
                </svg>
            </button>

            {{-- ドロップダウン --}}
            <div id="menu-dropdown" class="hidden absolute right-0 mt-2 w-48 bg-white rounded-xl shadow-lg border border-gray-100 overflow-hidden">
                <button onclick="openQuickMemo()" class="w-full text-left px-4 py-3 text-sm text-gray-700 hover:bg-gray-50 border-b border-gray-100 flex items-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4 text-gray-500">
                      <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0115.75 21H5.25A2.25 2.25 0 013 18.75V8.25A2.25 2.25 0 015.25 6H10" />
                    </svg>
                    クイックメモ
                </button>
                <a href="{{ route('memos.index') }}" class="block px-4 py-3 text-sm text-gray-700 hover:bg-gray-50 border-b border-gray-100 flex items-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4 text-gray-500">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 12h16.5m-16.5 3.75h16.5M3.75 19.5h16.5M5.625 4.5h12.75a1.875 1.875 0 010 3.75H5.625a1.875 1.875 0 010-3.75z" />
                    </svg>
                    メモ一覧を見る
                </a>
                <a href="{{ route('notifications.settings', $chatPartner ? ['return_to' => 'chat', 'user_id' => $chatPartner->id] : []) }}" class="block px-4 py-3 text-sm text-gray-700 hover:bg-gray-50 border-b border-gray-100 flex items-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4 text-gray-500">
                      <path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 005.454-1.31A8.967 8.967 0 0118 9.75v-.7V9A6 6 0 006 9v.75a8.967 8.967 0 01-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 01-5.714 0m5.714 0a3 3 0 11-5.714 0" />
                    </svg>
                    通知設定
                </a>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="w-full text-left px-4 py-3 text-sm text-red-500 hover:bg-red-50 flex items-center gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4">
                          <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6a2.25 2.25 0 00-2.25 2.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15m3 0l3-3m0 0l-3-3m3 3H9" />
                        </svg>
                        ログアウト
                    </button>
                </form>
            </div>
        </div>
    </div>

    {{-- ■ アナウンス表示エリア（固定表示・吹き出し風） --}}
    <div id="announcement-banner" class="fixed left-0 right-0 hidden" style="top: 64px; z-index: 35;">
        <div class="max-w-2xl mx-auto px-4 pt-3">
            <div class="bg-white border-2 border-indigo-300 rounded-2xl shadow-lg px-4 py-3 relative">
                {{-- 吹き出しの矢印 --}}
                <div class="absolute -top-2 left-6 w-4 h-4 bg-white border-l-2 border-t-2 border-indigo-300 transform rotate-45"></div>
                <div class="flex items-start gap-3">
                    <div class="w-8 h-8 rounded-full bg-indigo-100 flex items-center justify-center text-indigo-600 shrink-0">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5">
                          <path stroke-linecap="round" stroke-linejoin="round" d="M10.34 15.84c-.688-.06-1.386-.09-2.09-.09H7.5a4.5 4.5 0 110-9h.75c.704 0 1.402-.03 2.09-.09m0 9.18c.253.962.584 1.892.985 2.783.247.55.06 1.21-.463 1.511l-.615.38a1.125 1.125 0 01-1.447-.849l-.267-1.281a1.125 1.125 0 00-.758-1.219l-.22-.076a1.125 1.125 0 01-.564-1.582c.15-.472.32-.928.51-1.368m0 9.18a23.91 23.91 0 01-8.835 2.33M6 20.25a23.91 23.91 0 008.835-2.33m0 0a23.93 23.93 0 003.281-.477m.415-.011c.677-.236 1.354-.522 2.003-.79a24.238 24.238 0 001.281-5.821c0-.578-.276-1.097-.463-1.511a20.022 20.022 0 00-.985-2.783m0 0a23.91 23.91 0 00-8.835-2.33m0 0a6.718 6.718 0 01-7.007-.656" />
                        </svg>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-xs font-semibold text-indigo-600 mb-1">アナウンス</p>
                        <p id="announcement-text" class="text-sm text-gray-800 break-words"></p>
                    </div>
                    <button onclick="dismissAnnouncement()" class="text-gray-400 hover:text-gray-600 transition p-1 shrink-0 -mt-1 -mr-1">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5">
                          <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- ■ チャットエリア --}}
    <div class="pt-16 h-screen overflow-y-auto" onclick="closeMenu()" id="chat-container" style="padding-bottom: {{ $chatPartner ? '180px' : '0px' }};">
        <div id="chat-area" class="p-4 flex flex-col gap-4 pb-4">
            @if($chatPartner)
                @include('components.message-list', ['messages' => $messages])
            @else
                <div class="text-center text-gray-400 py-20">
                    <p class="text-lg mb-2">チャット相手を選択してください</p>
                    <p class="text-sm mb-4">ホーム画面から友達を選択してチャットを開始できます</p>
                    <a href="{{ route('dashboard') }}" class="inline-block bg-indigo-600 text-white px-6 py-2 rounded-lg hover:bg-indigo-700 transition">
                        ホームに戻る
                    </a>
                </div>
            @endif
        </div>
    </div>

    {{-- ■ チャット入力欄 --}}
    <div class="fixed bottom-0 left-0 right-0 bg-white border-t p-3 sm:p-4 pb-4 sm:pb-6 z-30 shadow-[0_-4px_6px_-1px_rgba(0,0,0,0.05)]" id="chat-input-container">
        @if($chatPartner)
        <form id="chat-form" method="POST" action="{{ route('chat.store', ['user' => $chatPartner->id]) }}">
            @csrf
            <div class="flex items-center gap-2 max-w-2xl mx-auto">
                <input type="text" name="body" id="message-input" class="flex-1 bg-gray-100 border-0 focus:ring-2 focus:ring-indigo-500 rounded-full px-4 py-3 text-base" placeholder="メッセージを入力..." required autocomplete="off">
                <button type="submit" id="send-button" class="bg-indigo-600 text-white rounded-full p-3 shadow-md active:scale-95 transition disabled:opacity-50 disabled:cursor-not-allowed" disabled>
                    <svg id="send-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-6 h-6"><path stroke-linecap="round" stroke-linejoin="round" d="M6 12L3.269 3.126A59.768 59.768 0 0121.485 12 59.77 59.77 0 013.27 20.876L5.999 12zm0 0h7.5" /></svg>
                    <svg id="loading-spinner" class="hidden w-6 h-6 animate-spin" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                </button>
            </div>
        </form>
        @else
        <div class="text-center text-gray-400 py-4">
            <p class="text-sm">チャット相手を選択してください</p>
            <a href="{{ route('dashboard') }}" class="text-indigo-600 hover:text-indigo-700 text-sm mt-2 inline-block">
                ホームに戻る
            </a>
        </div>
        @endif
    </div>

    {{-- ■ 通知送信モーダル --}}
    @if($chatPartner)
    <div id="notification-modal" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm">
        <div class="bg-white w-full max-w-md mx-4 rounded-2xl shadow-2xl max-h-[90vh] overflow-hidden flex flex-col">
            {{-- ヘッダー --}}
            <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between">
                <h2 class="font-bold text-lg text-gray-800">偽装通知を送信</h2>
                <button onclick="closeNotificationModal()" class="text-gray-400 hover:text-gray-600 transition p-2 -mr-2">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-6 h-6">
                      <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            {{-- コンテンツ --}}
            <div class="flex-1 overflow-y-auto p-6 space-y-6">
                {{-- 送信先表示 --}}
                <div class="bg-indigo-50 border border-indigo-200 rounded-xl p-4">
                    <p class="text-xs text-indigo-600 mb-1">送信先</p>
                    <p class="font-semibold text-indigo-900">{{ $chatPartner->name }}</p>
                </div>

                {{-- 通知タイプ選択 --}}
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-3">通知タイプを選択</label>
                    <div class="space-y-2">
                        @foreach($notificationTypes as $type)
                            @php
                                $isCustom = $type instanceof \App\Models\CustomNotificationType;
                                $userNotificationType = !$isCustom ? Auth::user()->notificationTypes()
                                    ->where('notification_type_id', $type->id)
                                    ->first() : null;
                                $customIcon = !$isCustom && $userNotificationType ? $userNotificationType->pivot->icon_image : ($isCustom ? $type->icon_image : null);
                            @endphp
                            <label class="flex items-center gap-3 p-4 border-2 rounded-xl cursor-pointer transition-all notification-type-option peer-checked:border-indigo-500 peer-checked:bg-indigo-50 peer-checked:shadow-md" 
                                   style="border-color: {{ $type->color ?? '#6B7280' }}40; border-left-width: 4px; border-left-color: {{ $type->color ?? '#6B7280' }};">
                                <input 
                                    type="radio" 
                                    name="notification_type" 
                                    value="{{ $type->id }}" 
                                    class="sr-only peer"
                                    data-color="{{ $type->color ?? '#6B7280' }}"
                                    data-icon="{{ $type->icon }}"
                                    data-app-name="{{ $type->app_name ?? $type->name }}"
                                    onchange="updateSelectedType(this)"
                                >
                                <div class="w-12 h-12 rounded-lg flex items-center justify-center text-2xl shrink-0 transition-all peer-checked:scale-110 overflow-hidden" 
                                     style="background-color: {{ $type->color ?? '#6B7280' }}20;">
                                    @if($customIcon)
                                        <img src="{{ image_url($customIcon) }}" alt="カスタムアイコン" class="w-full h-full object-cover">
                                    @else
                                        {{ $type->icon ?? '📢' }}
                                    @endif
                                </div>
                                <div class="flex-1">
                                    <div class="font-semibold text-gray-800 peer-checked:text-indigo-900 transition-colors">{{ $type->name }}</div>
                                    <div class="text-xs text-gray-500 peer-checked:text-indigo-700 transition-colors">偽装アプリ: {{ $type->app_name ?? $type->name }}</div>
                                </div>
                                <div class="w-6 h-6 border-2 rounded-full flex items-center justify-center shrink-0 transition-all peer-checked:border-indigo-600 peer-checked:bg-indigo-600" 
                                     style="border-color: {{ $type->color ?? '#6B7280' }}60;">
                                    <svg class="w-4 h-4 text-white hidden peer-checked:block" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                    </svg>
                                </div>
                            </label>
                        @endforeach
                    </div>
                </div>

                {{-- メッセージ入力 --}}
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">メッセージ</label>
                    <textarea 
                        id="notification-message" 
                        class="w-full bg-gray-50 border border-gray-200 rounded-xl px-4 py-3 text-base focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 resize-none" 
                        rows="4" 
                        placeholder="送信するメッセージを入力..."
                        required
                    ></textarea>
                </div>
            </div>

            {{-- フッター --}}
            <div class="px-6 py-4 border-t border-gray-200 flex items-center justify-end gap-3">
                <button onclick="closeNotificationModal()" class="px-4 py-2 text-gray-600 hover:bg-gray-100 rounded-lg transition-colors text-sm font-medium">
                    キャンセル
                </button>
                <button onclick="sendNotification()" class="px-6 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors text-sm font-medium shadow-md">
                    送信
                </button>
            </div>
        </div>
    </div>
    @endif

    {{-- ■ ★隠しエディタ --}}
    <div id="quick-memo-overlay" class="hidden fixed inset-0 z-50 flex items-start justify-center bg-black/30 backdrop-blur-sm transition-opacity">
        <div class="bg-white w-full sm:w-[600px] h-[80vh] rounded-b-3xl shadow-2xl flex flex-col transform transition-transform duration-500 -translate-y-full" id="memo-sheet">
            
            {{-- ヘッダー --}}
            <div class="px-4 py-3 border-b border-gray-100 flex items-center justify-between bg-white/95 backdrop-blur-md">
                
                {{-- 左側：整理ボタン --}}
                <a href="{{ route('memos.index') }}" class="text-gray-400 hover:text-blue-500 transition p-2 rounded-full hover:bg-gray-100">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
                      <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12.75V12A2.25 2.25 0 014.5 9.75h15A2.25 2.25 0 0121.75 12v.75m-8.69-6.44l-2.12-2.12a1.5 1.5 0 00-1.061-.44H4.5A2.25 2.25 0 002.25 6v12a2.25 2.25 0 002.25 2.25h15A2.25 2.25 0 0021.75 18V9a2.25 2.25 0 00-2.25-2.25h-5.379a1.5 1.5 0 01-1.06-.44z" />
                    </svg>
                </a>

                {{-- 中央：状態表示 --}}
                <span id="memo-status" class="text-xs text-gray-400 font-medium">読込中...</span>

                <div class="flex items-center gap-1">
                    {{-- 新規作成ボタン --}}
                    <button onclick="createNewMemo()" class="text-gray-400 hover:text-green-500 transition p-2 rounded-full hover:bg-gray-100" title="新しいメモにする">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
                          <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                        </svg>
                    </button>
                    
                    {{-- 完了ボタン（ここを押すと保存して閉じる、変更なければすぐ閉じる） --}}
                    <button onclick="saveQuickMemo()" class="text-yellow-500 font-bold text-sm px-3 py-2 hover:bg-yellow-50 rounded-full transition ml-1">
                        完了
                    </button>
                </div>
            </div>

            {{-- エディタ本体 --}}
            <div class="flex-1 p-6 overflow-hidden relative">
                <textarea 
                    id="quick-memo-content" 
                    class="w-full h-full bg-transparent border-none focus:ring-0 text-gray-800 placeholder-gray-300 resize-none text-lg leading-relaxed p-0" 
                    placeholder="ここにメモを入力..."></textarea>
            </div>
        </div>
    </div>

    <script>
        // --- 変数定義 ---
        const menuBtn = document.getElementById('menu-btn');
        const menuDropdown = document.getElementById('menu-dropdown');
        const overlay = document.getElementById('quick-memo-overlay');
        const sheet = document.getElementById('memo-sheet');
        const content = document.getElementById('quick-memo-content');
        const statusLabel = document.getElementById('memo-status');
        const chatContainer = document.getElementById('chat-container');
        const chatInputContainer = document.getElementById('chat-input-container');
        
        let currentMemoId = null;
        let initialContent = ''; // ★開いたときの内容を覚えておく変数

        // --- Service WorkerとPush通知の初期化 ---
        let serviceWorkerRegistration = null;

        async function registerServiceWorker() {
            if ('serviceWorker' in navigator) {
                try {
                    const registration = await navigator.serviceWorker.register('/sw.js');
                    serviceWorkerRegistration = registration;
                    console.log('Service Worker登録成功:', registration.scope);
                    return registration;
                } catch (error) {
                    console.error('Service Worker登録失敗:', error);
                    return null;
                }
            }
            return null;
        }

        async function requestNotificationPermission() {
            if ('Notification' in window) {
                if (Notification.permission === 'default') {
                    const permission = await Notification.requestPermission();
                    if (permission === 'granted') {
                        console.log('通知の許可が得られました');
                        // Service Workerを登録
                        await registerServiceWorker();
                    }
                } else if (Notification.permission === 'granted') {
                    // 既に許可されている場合はService Workerを登録
                    await registerServiceWorker();
                }
            }
        }

        // ページ読み込み時に通知許可をリクエスト
        if ('Notification' in window) {
            requestNotificationPermission();
        }

        // 通知を表示する関数（Service Worker経由でロック画面にも表示）
        async function showBrowserNotification(title, body, icon = null, tag = null) {
            if ('Notification' in window && Notification.permission === 'granted') {
                // Service Workerが登録されている場合はService Worker経由で表示
                if (serviceWorkerRegistration) {
                    try {
                        await serviceWorkerRegistration.showNotification(title, {
                            body: body,
                            icon: icon || '/favicon.ico',
                            badge: icon || '/favicon.ico',
                            tag: tag || 'notification',
                            requireInteraction: false,
                            silent: false,
                            vibrate: [200, 100, 200],
                        });
                        return;
                    } catch (error) {
                        console.error('Service Worker通知エラー:', error);
                    }
                }

                // Service Workerが使えない場合は通常の通知
                const options = {
                    body: body,
                    icon: icon || '/favicon.ico',
                    badge: icon || '/favicon.ico',
                    tag: tag || 'notification',
                    requireInteraction: false,
                    silent: false,
                };

                const notification = new Notification(title, options);

                notification.onclick = function() {
                    window.focus();
                    notification.close();
                };

                // 5秒後に自動で閉じる
                setTimeout(() => {
                    notification.close();
                }, 5000);
            }
        }

        // --- チャットエリアのパディングを動的に調整（スマホ対応） ---
        function adjustChatPadding() {
            if (chatContainer && chatInputContainer) {
                const inputHeight = chatInputContainer.offsetHeight;
                // スマホでは余分なパディングを追加（最低でも180px以上）
                const isMobile = window.innerWidth < 640;
                const extraPadding = isMobile ? 40 : 30;
                const minPadding = isMobile ? 180 : 150;
                const calculatedPadding = Math.max(inputHeight + extraPadding, minPadding);
                chatContainer.style.paddingBottom = `${calculatedPadding}px`;
                
                // スクロール位置を調整（最下部にスクロール）
                setTimeout(() => {
                    chatContainer.scrollTop = chatContainer.scrollHeight;
                }, 100);
            }
        }

        // 初期化とリサイズ時の調整
        if (chatContainer && chatInputContainer) {
            // DOMContentLoaded後に実行
            function initChatPadding() {
                adjustChatPadding();
                // 複数回スクロールを試みる（レンダリング待ち）
                setTimeout(() => {
                    chatContainer.scrollTop = chatContainer.scrollHeight;
                }, 100);
                setTimeout(() => {
                    chatContainer.scrollTop = chatContainer.scrollHeight;
                }, 300);
                setTimeout(() => {
                    chatContainer.scrollTop = chatContainer.scrollHeight;
                }, 500);
            }
            
            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', initChatPadding);
            } else {
                initChatPadding();
            }
            
            window.addEventListener('resize', () => {
                adjustChatPadding();
                setTimeout(() => {
                    chatContainer.scrollTop = chatContainer.scrollHeight;
                }, 100);
            });
            
            // 入力フィールドのフォーカス時にも調整（キーボード表示時）
            const chatInput = document.querySelector('input[name="body"]');
            if (chatInput) {
                chatInput.addEventListener('focus', () => {
                    setTimeout(() => {
                        adjustChatPadding();
                        // キーボード表示時にスクロール位置を調整
                        setTimeout(() => {
                            chatContainer.scrollTop = chatContainer.scrollHeight;
                        }, 300);
                    }, 300);
                });
                chatInput.addEventListener('blur', () => {
                    setTimeout(() => {
                        adjustChatPadding();
                        chatContainer.scrollTop = chatContainer.scrollHeight;
                    }, 300);
                });
            }
        }

        // --- フォーム送信時の二重送信防止とローディング表示 ---
        const chatForm = document.getElementById('chat-form');
        const sendButton = document.getElementById('send-button');
        const messageInput = document.getElementById('message-input');
        const sendIcon = document.getElementById('send-icon');
        const loadingSpinner = document.getElementById('loading-spinner');

        // 入力フィールドの変更を監視してボタンの有効/無効を切り替え
        if (messageInput && sendButton) {
            messageInput.addEventListener('input', function() {
                if (this.value.trim()) {
                    sendButton.disabled = false;
                } else {
                    sendButton.disabled = true;
                }
            });
        }

        if (chatForm && sendButton && chatContainer) {
            chatForm.addEventListener('submit', function(e) {
                // ボタンを無効化
                sendButton.disabled = true;
                // アイコンを非表示にしてスピナーを表示
                if (sendIcon) sendIcon.classList.add('hidden');
                if (loadingSpinner) loadingSpinner.classList.remove('hidden');

                // メッセージ送信後にスクロール位置を最下部に
                setTimeout(() => {
                    adjustChatPadding();
                    chatContainer.scrollTop = chatContainer.scrollHeight;
                }, 100);

                // フォーム送信が完了したらボタンを再度有効化
                // ただし、ページがリロードされる場合は自動でリセットされる
                setTimeout(() => {
                    sendButton.disabled = false;
                    if (sendIcon) sendIcon.classList.remove('hidden');
                    if (loadingSpinner) loadingSpinner.classList.add('hidden');
                    if (messageInput) messageInput.value = '';
                    if (messageInput) messageInput.focus();
                }, 2000); // 2秒後にリセット（通常のリクエスト時間を想定）
            });
        }

        // --- メニュー制御 ---
        menuBtn.addEventListener('click', (e) => {
            e.stopPropagation();
            menuDropdown.classList.toggle('hidden');
        });

        function closeMenu() {
            if (!menuDropdown.classList.contains('hidden')) {
                menuDropdown.classList.add('hidden');
            }
        }

        // --- エディタ開閉 & データ取得 ---
        function openQuickMemo() {
            closeMenu();
            overlay.classList.remove('hidden');
            
            setTimeout(() => {
                sheet.classList.remove('-translate-y-full');
            }, 10);

            statusLabel.innerText = '読込中...';
            content.value = '';
            
            fetch('{{ route("memos.latest") }}')
                .then(res => res.json())
                .then(data => {
                    if (data && data.id) {
                        currentMemoId = data.id;
                        content.value = data.content;
                        initialContent = data.content; // ★初期値を保存
                        statusLabel.innerText = '編集';
                    } else {
                        currentMemoId = null;
                        content.value = '';
                        initialContent = ''; // ★初期値は空
                        statusLabel.innerText = '新規';
                    }
                    content.focus();
                })
                .catch(err => {
                    console.error(err);
                    statusLabel.innerText = '新規';
                    currentMemoId = null;
                    initialContent = '';
                });
        }

        function closeQuickMemo() {
            sheet.classList.add('-translate-y-full');
            setTimeout(() => {
                overlay.classList.add('hidden');
            }, 500);
        }

        // --- 新規作成モードへの切り替え ---
        function createNewMemo() {
            if(confirm('現在の内容をクリアして、新しいメモを作成しますか？')) {
                currentMemoId = null;
                content.value = '';
                initialContent = ''; // 新規状態にする
                content.focus();
                statusLabel.innerText = '新規';
            }
        }

        // --- 保存処理 (さらに賢くなった版) ---
        function saveQuickMemo() {
            const text = content.value;
            
            // ① 空っぽなら、何もせず閉じる
            if (!text.trim()) {
                closeQuickMemo();
                return;
            }

            // ② 内容が変わっていなければ、通信せず即座に閉じる
            if (text === initialContent) {
                closeQuickMemo();
                return;
            }

            // ③ 変更があれば保存処理へ
            statusLabel.innerText = '保存中...';

            let url = '{{ route("memos.store") }}'; 
            let method = 'POST';

            if (currentMemoId) {
                url = `/memos/${currentMemoId}`; 
                method = 'PATCH';
            }

            fetch(url, {
                method: method,
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ content: text })
            })
            .then(response => {
                if (response.ok) {
                    statusLabel.innerText = '保存完了';
                    setTimeout(closeQuickMemo, 500);
                } else {
                    alert('保存に失敗しました');
                    statusLabel.innerText = 'エラー';
                }
            })
            .catch(error => console.error('Error:', error));
        }

        // --- チャット自動更新 ---
        const chatArea = document.getElementById('chat-area');
        
        // 初期スクロール位置を最下部に設定（複数回試行）
        if (chatContainer) {
            const scrollToBottom = () => {
                chatContainer.scrollTop = chatContainer.scrollHeight;
            };
            setTimeout(scrollToBottom, 100);
            setTimeout(scrollToBottom, 300);
            setTimeout(scrollToBottom, 500);
            setTimeout(scrollToBottom, 1000);
        }
        
        @if($chatPartner)
        setInterval(() => {
            if (!overlay.classList.contains('hidden')) return;

            fetch('{{ route("chat", ["user" => $chatPartner->id]) }}')
                .then(response => response.text())
                .then(html => {
                    const parser = new DOMParser();
                    const doc = parser.parseFromString(html, 'text/html');
                    const newContent = doc.getElementById('chat-area').innerHTML;
                    if (chatContainer) {
                        const isAtBottom = chatContainer.scrollHeight - chatContainer.scrollTop <= chatContainer.clientHeight + 50;
                        chatArea.innerHTML = newContent;
                        if (isAtBottom) {
                            setTimeout(() => {
                                chatContainer.scrollTop = chatContainer.scrollHeight;
                            }, 50);
                        }
                    }
                });
        }, 3000);
        @endif

        // --- 未読通知をチェックしてブラウザ通知を表示 ---
        let lastNotificationId = null;
        async function checkNewNotifications() {
            try {
                const response = await fetch('{{ route("notifications.list") }}');
                const notifications = await response.json();
                
                if (notifications && notifications.length > 0) {
                    const latestNotification = notifications[0];
                    
                    // 新しい通知がある場合
                    if (!lastNotificationId || latestNotification.id !== lastNotificationId) {
                        if (latestNotification && !latestNotification.is_read) {
                            // アイコン画像のパスを取得
                            let iconPath = null;
                            if (latestNotification.notification_type && latestNotification.notification_type.icon_image) {
                                iconPath = '{{ url("/") }}/storage/' + latestNotification.notification_type.icon_image;
                            }
                            
                            showBrowserNotification(
                                latestNotification.title || '通知',
                                latestNotification.body || '',
                                iconPath || null,
                                'notification-' + latestNotification.id
                            );
                            
                            lastNotificationId = latestNotification.id;
                        }
                    }
                }
            } catch (error) {
                console.error('通知チェックエラー:', error);
            }
        }

        // 5秒ごとに通知をチェック
        setInterval(checkNewNotifications, 5000);

        // --- 通知送信モーダル制御 ---
        @if($chatPartner)
        const notificationModal = document.getElementById('notification-modal');
        
        function openNotificationModal() {
            if (notificationModal) {
                notificationModal.classList.remove('hidden');
            }
        }

        function closeNotificationModal() {
            if (notificationModal) {
                notificationModal.classList.add('hidden');
                // フォームをリセット
                document.querySelectorAll('input[name="notification_type"]').forEach(radio => {
                    radio.checked = false;
                });
                document.getElementById('notification-message').value = '';
                // 選択状態のスタイルをリセット
                document.querySelectorAll('.notification-type-option').forEach(option => {
                    option.classList.remove('border-indigo-500', 'bg-indigo-50', 'shadow-md');
                });
            }
        }

        // モーダル外をクリックで閉じる
        if (notificationModal) {
            notificationModal.addEventListener('click', (e) => {
                if (e.target === notificationModal) {
                    closeNotificationModal();
                }
            });
        }

        // 選択された通知タイプを更新
        function updateSelectedType(radio) {
            // 全てのオプションから選択状態のスタイルをリセット
            document.querySelectorAll('.notification-type-option').forEach(option => {
                option.classList.remove('border-indigo-500', 'bg-indigo-50', 'shadow-md');
            });
            
            // 選択されたオプションにスタイルを適用
            if (radio.checked) {
                const option = radio.closest('.notification-type-option');
                option.classList.add('border-indigo-500', 'bg-indigo-50', 'shadow-md');
            }
        }

        // 通知送信
        async function sendNotification() {
            const notificationTypeId = document.querySelector('input[name="notification_type"]:checked')?.value;
            const message = document.getElementById('notification-message').value.trim();

            if (!notificationTypeId) {
                alert('通知タイプを選択してください');
                return;
            }

            if (!message) {
                alert('メッセージを入力してください');
                return;
            }

            try {
                const response = await fetch('{{ route("notifications.send") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({
                        receiver_id: {{ $chatPartner->id }},
                        notification_type_id: notificationTypeId,
                        message: message
                    })
                });

                const data = await response.json();

                if (response.ok) {
                    // 通知許可を確認
                    if ('Notification' in window && Notification.permission === 'granted') {
                        // ブラウザ通知を表示
                        showBrowserNotification(
                            data.title || '通知',
                            data.body || message,
                            data.icon || null,
                            'notification-' + Date.now()
                        );
                    } else if ('Notification' in window && Notification.permission === 'default') {
                        // 通知許可をリクエスト
                        const permission = await Notification.requestPermission();
                        if (permission === 'granted') {
                            showBrowserNotification(
                                data.title || '通知',
                                data.body || message,
                                data.icon || null,
                                'notification-' + Date.now()
                            );
                        }
                    }
                    
                    alert('通知を送信しました！');
                    closeNotificationModal();
                } else {
                    alert(data.error || '通知の送信に失敗しました');
                }
            } catch (error) {
                console.error('Error:', error);
                alert('通知の送信に失敗しました');
            }
        }
        @endif

        // --- メッセージコンテキストメニュー ---
        let contextMenu = null;
        let selectedMessageId = null;
        let selectedMessageBody = null;

        function createContextMenu() {
            if (contextMenu) {
                contextMenu.remove();
            }

            contextMenu = document.createElement('div');
            contextMenu.id = 'message-context-menu';
            contextMenu.className = 'fixed bg-white rounded-xl shadow-lg border border-gray-200 py-2 z-50 hidden';
            contextMenu.innerHTML = `
                <button onclick="announceMessage()" class="w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 flex items-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10.34 15.84c-.688-.06-1.386-.09-2.09-.09H7.5a4.5 4.5 0 110-9h.75c.704 0 1.402-.03 2.09-.09m0 9.18c.253.962.584 1.892.985 2.783.247.55.06 1.21-.463 1.511l-.615.38a1.125 1.125 0 01-1.447-.849l-.267-1.281a1.125 1.125 0 00-.758-1.219l-.22-.076a1.125 1.125 0 01-.564-1.582c.15-.472.32-.928.51-1.368m0 9.18a23.91 23.91 0 01-8.835 2.33M6 20.25a23.91 23.91 0 008.835-2.33m0 0a23.93 23.93 0 003.281-.477m.415-.011c.677-.236 1.354-.522 2.003-.79a24.238 24.238 0 001.281-5.821c0-.578-.276-1.097-.463-1.511a20.022 20.022 0 00-.985-2.783m0 0a23.91 23.91 0 00-8.835-2.33m0 0a6.718 6.718 0 01-7.007-.656" />
                    </svg>
                    アナウンス
                </button>
                <button onclick="copyToMemo()" class="w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 flex items-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0115.75 21H5.25A2.25 2.25 0 013 18.75V8.25A2.25 2.25 0 015.25 6H10" />
                    </svg>
                    メモに書き写し
                </button>
                <button onclick="deleteMessage()" class="w-full text-left px-4 py-2 text-sm text-red-500 hover:bg-red-50 flex items-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0" />
                    </svg>
                    削除
                </button>
            `;
            document.body.appendChild(contextMenu);
        }

        function showContextMenu(event, messageId, messageBody) {
            event.preventDefault();
            event.stopPropagation();
            
            selectedMessageId = messageId;
            selectedMessageBody = messageBody;
            
            if (!contextMenu) {
                createContextMenu();
            }
            
            contextMenu.classList.remove('hidden');
            
            const x = event.clientX || (event.touches && event.touches[0] ? event.touches[0].clientX : 0);
            const y = event.clientY || (event.touches && event.touches[0] ? event.touches[0].clientY : 0);
            const menuWidth = 200;
            const menuHeight = 150;
            
            // 画面外に出ないように調整
            const left = Math.min(x, window.innerWidth - menuWidth - 10);
            const top = Math.min(y, window.innerHeight - menuHeight - 10);
            
            contextMenu.style.left = `${left}px`;
            contextMenu.style.top = `${top}px`;
        }

        function hideContextMenu() {
            if (contextMenu) {
                contextMenu.classList.add('hidden');
            }
        }

        // メッセージバブルにイベントリスナーを追加
        document.addEventListener('DOMContentLoaded', function() {
            createContextMenu();
            
            // 右クリックイベント
            document.addEventListener('contextmenu', function(e) {
                const messageBubble = e.target.closest('.message-bubble');
                if (messageBubble) {
                    const messageId = messageBubble.dataset.messageId;
                    const messageBody = messageBubble.dataset.messageBody;
                    showContextMenu(e, messageId, messageBody);
                }
            });
            
            // 長押しイベント（モバイル）
            let touchStartTime = 0;
            let touchTimer = null;
            
            document.addEventListener('touchstart', function(e) {
                const messageBubble = e.target.closest('.message-bubble');
                if (messageBubble) {
                    touchStartTime = Date.now();
                    const messageId = messageBubble.dataset.messageId;
                    const messageBody = messageBubble.dataset.messageBody;
                    
                    touchTimer = setTimeout(() => {
                        const touch = e.touches[0] || e.changedTouches[0];
                        const fakeEvent = {
                            clientX: touch.clientX,
                            clientY: touch.clientY,
                            preventDefault: () => {},
                            stopPropagation: () => {}
                        };
                        showContextMenu(fakeEvent, messageId, messageBody);
                    }, 500);
                }
            });
            
            document.addEventListener('touchend', function() {
                if (touchTimer) {
                    clearTimeout(touchTimer);
                    touchTimer = null;
                }
            });
            
            // メニュー外をクリックで閉じる
            document.addEventListener('click', function(e) {
                if (contextMenu && !contextMenu.contains(e.target)) {
                    hideContextMenu();
                }
            });
        });

        // アナウンス機能（選択したメッセージを上部に表示）
        async function announceMessage() {
            hideContextMenu();
            
            console.log('announceMessage called, selectedMessageBody:', selectedMessageBody);
            
            @if($chatPartner)
            if (!selectedMessageBody) {
                alert('メッセージが選択されていません');
                return;
            }
            
            // アナウンスをデータベースに保存
            try {
                const response = await fetch('{{ route("chat.announce", ["user" => $chatPartner->id]) }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({
                        message: selectedMessageBody
                    })
                });
                
                const data = await response.json();
                
                if (response.ok) {
                    // 上部バナーにアナウンスを表示
                    showAnnouncementBanner(selectedMessageBody);
                } else {
                    alert(data.error || 'アナウンスの保存に失敗しました');
                }
            } catch (error) {
                console.error('Error:', error);
                alert('アナウンスの保存に失敗しました');
            }
            @else
            alert('チャット相手が選択されていません');
            @endif
        }
        
        // グローバルスコープで確実に呼び出せるようにする
        window.announceMessage = announceMessage;

        // アナウンスバナーを表示
        function showAnnouncementBanner(messageText) {
            console.log('showAnnouncementBanner called with:', messageText);
            
            const banner = document.getElementById('announcement-banner');
            const announcementText = document.getElementById('announcement-text');
            
            console.log('banner element:', banner);
            console.log('announcementText element:', announcementText);
            
            if (!banner) {
                console.error('アナウンスバナーが見つかりません');
                return;
            }
            
            if (!announcementText) {
                console.error('アナウンステキスト要素が見つかりません');
                return;
            }

            // メッセージテキストを設定
            announcementText.textContent = messageText;
            console.log('メッセージテキストを設定しました:', messageText);
            
            // バナーを表示（hiddenクラスを削除）
            banner.classList.remove('hidden');
            console.log('バナーのhiddenクラスを削除しました');
            
            // チャットエリアのパディングを調整（バナー表示後に高さを取得）
            const chatContainer = document.getElementById('chat-container');
            if (chatContainer) {
                // 少し待ってからバナーの高さを取得
                setTimeout(() => {
                    const bannerHeight = banner.offsetHeight || 80;
                    console.log('バナーの高さ:', bannerHeight);
                    chatContainer.style.paddingTop = `${64 + bannerHeight}px`; // ヘッダー64px + バナー高さ
                }, 10);
            }
        }

        // アナウンスバナーを閉じる
        function dismissAnnouncement() {
            const banner = document.getElementById('announcement-banner');
            if (!banner) {
                return;
            }
            
            // バナーを非表示
            banner.classList.add('hidden');
            
            // チャットエリアのパディングを元に戻す
            const chatContainer = document.getElementById('chat-container');
            if (chatContainer) {
                chatContainer.style.paddingTop = '64px'; // ヘッダーの高さのみ
            }
            
            // アナウンスを閉じたことをサーバーに記録
            @if(isset($latestAnnouncement) && $latestAnnouncement)
            fetch('{{ route("chat.announcement.dismiss", ["message" => $latestAnnouncement->id]) }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    console.log('アナウンスを閉じたことを記録しました');
                } else {
                    console.error('アナウンスの閉じた記録に失敗しました:', data.message);
                }
            })
            .catch(error => {
                console.error('Error dismissing announcement:', error);
            });
            @endif
        }
        
        // グローバルスコープで確実に呼び出せるようにする
        window.dismissAnnouncement = dismissAnnouncement;

        // ページ読み込み時にアナウンスを表示
        document.addEventListener('DOMContentLoaded', function() {
            @if(isset($latestAnnouncement) && $latestAnnouncement)
                showAnnouncementBanner('{{ addslashes($latestAnnouncement->body) }}');
            @endif
        });

        // メモに書き写し
        function copyToMemo() {
            hideContextMenu();
            
            if (!selectedMessageBody) {
                alert('メッセージが選択されていません');
                return;
            }
            
            // クイックメモを開く
            openQuickMemo();
            
            // メッセージをテキストエリアに追加
            setTimeout(() => {
                const memoContent = document.getElementById('quick-memo-content');
                if (memoContent) {
                    const currentContent = memoContent.value.trim();
                    if (currentContent) {
                        memoContent.value = currentContent + '\n\n' + selectedMessageBody;
                    } else {
                        memoContent.value = selectedMessageBody;
                    }
                    memoContent.focus();
                    // カーソルを最後に移動
                    memoContent.setSelectionRange(memoContent.value.length, memoContent.value.length);
                }
            }, 300);
        }

        // メッセージ削除
        async function deleteMessage() {
            hideContextMenu();
            
            if (!selectedMessageId) {
                alert('メッセージが選択されていません');
                return;
            }
            
            if (!confirm('このメッセージを削除しますか？')) {
                return;
            }
            
            try {
                const response = await fetch(`/chat/message/${selectedMessageId}`, {
                    method: 'DELETE',
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    }
                });
                
                if (response.ok) {
                    // メッセージをDOMから削除
                    const messageBubble = document.querySelector(`[data-message-id="${selectedMessageId}"]`);
                    if (messageBubble) {
                        const messageContainer = messageBubble.closest('.flex');
                        if (messageContainer) {
                            messageContainer.remove();
                        }
                    }
                } else {
                    const data = await response.json();
                    alert(data.error || 'メッセージの削除に失敗しました');
                }
            } catch (error) {
                console.error('Error:', error);
                alert('メッセージの削除に失敗しました');
            }
        }

    </script>
</body>
</html>