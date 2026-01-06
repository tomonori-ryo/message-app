<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>通知一覧</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-50 font-sans text-gray-900 antialiased">

    {{-- ■ 上部ヘッダー --}}
    <header class="fixed top-0 left-0 right-0 bg-white/90 backdrop-blur-md z-50 h-16 flex items-center px-4 border-b border-gray-200/80 shadow-sm">
        <div class="max-w-3xl mx-auto w-full flex items-center gap-3">
            {{-- 戻るボタン --}}
            <a href="{{ route('chat') }}" class="p-2 -ml-2 text-gray-500 hover:text-indigo-600 hover:bg-gray-100 rounded-full transition-colors duration-200">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-5 h-5">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5" />
                </svg>
            </a>
            
            {{-- タイトル --}}
            <h1 class="font-bold text-lg text-gray-800 tracking-tight">通知一覧</h1>
        </div>
    </header>

    {{-- ■ メインコンテンツ --}}
    <main class="pt-20 pb-24 px-4 max-w-3xl mx-auto min-h-screen">
        
        <div class="space-y-4 pt-4" id="notifications-list">
            {{-- 通知はJavaScriptで動的に読み込まれます --}}
            <div class="text-center py-12 text-gray-400">
                <p>通知を読み込んでいます...</p>
            </div>
        </div>
    </main>

    <script>
        // 通知一覧を取得
        async function loadNotifications() {
            try {
                const response = await fetch('{{ route("notifications.list") }}', {
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    }
                });
                const notifications = await response.json();
                
                const list = document.getElementById('notifications-list');
                list.innerHTML = '';
                
                if (notifications.length === 0) {
                    list.innerHTML = '<div class="text-center py-12 text-gray-400"><p>通知がありません</p></div>';
                    return;
                }
                
                notifications.forEach(notification => {
                    const type = notification.notification_type;
                    const sender = notification.sender;
                    const isRead = notification.is_read;
                    
                    const notificationEl = document.createElement('div');
                    notificationEl.className = `bg-white rounded-2xl shadow-sm border-l-4 p-5 transition-all ${isRead ? 'opacity-60' : ''}`;
                    notificationEl.style.borderLeftColor = type.color || '#6B7280';
                    
                    const timeAgo = getTimeAgo(new Date(notification.created_at));
                    
                    notificationEl.innerHTML = `
                        <div class="flex items-start gap-3">
                            <div class="w-12 h-12 rounded-lg flex items-center justify-center text-2xl shrink-0" style="background-color: ${type.color || '#6B7280'}20;">
                                ${type.icon || '📢'}
                            </div>
                            <div class="flex-1 min-w-0">
                                <div class="flex items-start justify-between gap-2 mb-1">
                                    <div class="flex-1">
                                        <div class="flex items-center gap-2 mb-1">
                                            <span class="font-bold text-sm" style="color: ${type.color || '#6B7280'};">${type.app_name || type.name}</span>
                                            <span class="text-xs text-gray-400">${timeAgo}</span>
                                        </div>
                                        <h3 class="font-semibold text-gray-800 mb-1">${notification.title}</h3>
                                        <p class="text-sm text-gray-600 mb-2">${notification.body}</p>
                                        ${sender ? `<p class="text-xs text-gray-400">送信者: ${sender.name}</p>` : ''}
                                        ${notification.real_message ? `<div class="mt-3 p-3 bg-indigo-50 rounded-lg border border-indigo-100"><p class="text-xs text-indigo-800 font-medium">実際のメッセージ:</p><p class="text-sm text-indigo-900 mt-1">${notification.real_message}</p></div>` : ''}
                                    </div>
                                    ${!isRead ? '<div class="w-2 h-2 bg-indigo-600 rounded-full shrink-0 mt-1"></div>' : ''}
                                </div>
                                ${!isRead ? `<button onclick="markAsRead(${notification.id})" class="mt-2 text-xs text-indigo-600 hover:text-indigo-800">既読にする</button>` : ''}
                            </div>
                        </div>
                    `;
                    
                    list.appendChild(notificationEl);
                });
            } catch (error) {
                console.error('Error loading notifications:', error);
                document.getElementById('notifications-list').innerHTML = '<div class="text-center py-12 text-red-400"><p>通知の読み込みに失敗しました</p></div>';
            }
        }
        
        // 既読にする
        async function markAsRead(notificationId) {
            try {
                const response = await fetch(`/notifications/${notificationId}/read`, {
                    method: 'PATCH',
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    }
                });
                
                if (response.ok) {
                    loadNotifications();
                }
            } catch (error) {
                console.error('Error marking as read:', error);
            }
        }
        
        // 時間差を計算
        function getTimeAgo(date) {
            const now = new Date();
            const diff = now - date;
            const seconds = Math.floor(diff / 1000);
            const minutes = Math.floor(seconds / 60);
            const hours = Math.floor(minutes / 60);
            const days = Math.floor(hours / 24);
            
            if (days > 0) return `${days}日前`;
            if (hours > 0) return `${hours}時間前`;
            if (minutes > 0) return `${minutes}分前`;
            return 'たった今';
        }
        
        // ページ読み込み時に通知を取得
        loadNotifications();
        
        // 30秒ごとに自動更新
        setInterval(loadNotifications, 30000);
    </script>
</body>
</html>

