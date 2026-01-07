<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>ホーム</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-slate-50 font-sans text-gray-900 antialiased">

    {{-- ■ 上部ヘッダー --}}
    <div class="fixed top-0 left-0 right-0 bg-white z-50 h-16 flex items-center justify-between px-4 border-b shadow-sm">
        <h1 class="font-bold text-xl text-slate-800">ホーム</h1>
        
        <div class="flex items-center gap-2">
            {{-- ログアウト --}}
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="text-xs text-red-500 border border-red-500 rounded px-3 py-2 hover:bg-red-50 transition">
                    ログアウト
                </button>
            </form>

            {{-- プロフィール --}}
            <a href="{{ route('profile.edit') }}" class="p-2 bg-gray-100 rounded-full hover:bg-gray-200 transition">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6 text-gray-600">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M17.982 18.725A7.488 7.488 0 0012 15.75a7.488 7.488 0 00-5.982 2.975m11.963 0a9 9 0 10-11.963 0m11.963 0A8.966 8.966 0 0112 21a8.966 8.966 0 01-5.982-2.275M15 9.75a3 3 0 11-6 0 3 3 0 016 0z" />
                </svg>
            </a>
        </div>
    </div>

    {{-- ■ メインコンテンツ --}}
    <div class="pt-20 pb-20 px-4 min-h-screen">
        
        {{-- 自分の情報 --}}
        <div class="mb-6 flex items-center gap-3">
            <div class="w-14 h-14 rounded-full bg-slate-200 flex items-center justify-center border-2 border-white shadow-sm overflow-hidden">
                @if(auth()->user()->avatar)
                    <img src="{{ Storage::url(auth()->user()->avatar) }}" alt="{{ auth()->user()->name }}" class="w-full h-full object-cover">
                @else
                    <span class="text-xl font-bold text-gray-500">{{ substr(auth()->user()->name, 0, 1) }}</span>
                @endif
            </div>
            <div>
                <p class="text-xs text-gray-400">ログイン中</p>
                <h2 class="text-lg font-bold text-gray-800">{{ auth()->user()->name }}</h2>
                @if(auth()->user()->username)
                    <p class="text-xs text-gray-500">{{ '@' . auth()->user()->username }}</p>
                @endif
            </div>
        </div>

        {{-- ② ユーザー検索 --}}
        <div class="mb-8">
            <h3 class="text-sm font-bold text-gray-500 mb-2 pl-1">ユーザー検索</h3>
            <form method="GET" action="{{ route('dashboard') }}" class="mb-2">
                <div class="flex gap-2">
                    <input 
                        type="text" 
                        name="search" 
                        value="{{ $searchQuery ?? '' }}"
                        placeholder="アカウント名または名前で検索..." 
                        class="flex-1 bg-white border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                    >
                    <button type="submit" class="bg-indigo-600 text-white px-6 py-2.5 rounded-xl hover:bg-indigo-700 transition text-sm font-medium">
                        検索
                    </button>
                    @if($searchQuery)
                        <a href="{{ route('dashboard') }}" class="bg-gray-200 text-gray-700 px-4 py-2.5 rounded-xl hover:bg-gray-300 transition text-sm font-medium">
                            クリア
                        </a>
                    @endif
                </div>
            </form>
            <div class="text-center">
                <button type="button" onclick="showMyQrCode()" class="text-xs text-indigo-600 hover:text-indigo-700 underline">
                    QRコードで追加
                </button>
            </div>

            {{-- 検索結果 --}}
            @if($searchQuery)
                <div class="flex flex-col gap-2">
                    @if($searchResults->isNotEmpty())
                        @foreach ($searchResults as $result)
                            @php
                                $isFriend = $friends->contains('id', $result->id);
                            @endphp
                            <div class="bg-white p-3 rounded-2xl shadow-sm border border-gray-100 flex items-center justify-between">
                                <div class="flex items-center gap-3 flex-1">
                                    <div class="w-10 h-10 rounded-full bg-gray-200 flex items-center justify-center text-gray-500 font-bold text-sm overflow-hidden shrink-0">
                                        @if($result->avatar)
                                            <img src="{{ Storage::url($result->avatar) }}" alt="{{ $result->name }}" class="w-full h-full object-cover">
                                        @else
                                            {{ substr($result->name, 0, 1) }}
                                        @endif
                                    </div>
                                    <div>
                                        <div class="font-medium text-gray-700 text-sm">{{ $result->name }}</div>
                                        @if($result->username)
                                            <div class="text-xs text-gray-400">{{ '@' . $result->username }}</div>
                                        @else
                                            <div class="text-xs text-gray-400">{{ $result->email }}</div>
                                        @endif
                                    </div>
                                </div>
                                @if(!$isFriend && $result->id !== auth()->id())
                                    <form method="POST" action="{{ route('friends.add', $result->id) }}">
                                        @csrf
                                        <button type="submit" class="bg-indigo-600 text-white px-4 py-2 rounded-lg hover:bg-indigo-700 transition text-sm font-medium">
                                            友達追加
                                        </button>
                                    </form>
                                @elseif($isFriend)
                                    <span class="text-xs text-gray-400 px-4 py-2">友達</span>
                                @endif
                            </div>
                        @endforeach
                    @else
                        <div class="bg-white p-6 rounded-2xl text-center border border-dashed border-gray-300">
                            <p class="text-gray-400 text-sm">検索結果が見つかりませんでした</p>
                        </div>
                    @endif
                </div>
            @endif
        </div>

        {{-- ① 友達リスト（カテゴリー管理ボタン付き） --}}
        <div class="mb-8">
            <div class="flex items-center justify-between mb-3">
                <h3 class="text-sm font-bold text-gray-500 pl-1">友達リスト</h3>
                <button onclick="openCategoryManager()" class="text-xs text-indigo-600 hover:text-indigo-700 font-medium px-3 py-1.5 bg-indigo-50 hover:bg-indigo-100 rounded-lg transition">
                    カテゴリー管理
                </button>
            </div>
            
            {{-- タブUI --}}
            @if($friends->isNotEmpty())
            <div class="flex gap-2 mb-4 overflow-x-auto pb-2 scrollbar-hide" style="scrollbar-width: none; -ms-overflow-style: none;">
                <button onclick="switchCategoryTab('all')" id="tab-all" class="friend-tab active flex-shrink-0 px-4 py-2 text-sm font-medium rounded-lg bg-indigo-600 text-white transition">
                    全て
                </button>
                @foreach ($categories as $category)
                    <button onclick="switchCategoryTab({{ $category->id }})" id="tab-{{ $category->id }}" class="friend-tab flex-shrink-0 px-4 py-2 text-sm font-medium rounded-lg bg-gray-100 text-gray-700 hover:bg-gray-200 transition">
                        {{ $category->name }}
                    </button>
                @endforeach
            </div>
            @endif
            
            {{-- 友達リストコンテンツ --}}
            <div id="friends-content">
                {{-- 「全て」タブのコンテンツ --}}
                <div id="friends-all" class="friends-tab-content space-y-4">
                    {{-- カテゴリーごとに友達を表示 --}}
                    @foreach ($categories as $category)
                        @php
                            $categoryFriends = $friendsByCategory[$category->id] ?? collect();
                        @endphp
                        @if($categoryFriends->isNotEmpty())
                            <div class="space-y-2">
                                <div class="flex items-center justify-between px-2">
                                    <h4 class="text-xs font-semibold text-gray-600">{{ $category->name }}</h4>
                                    <span class="text-xs text-gray-400">{{ $categoryFriends->count() }}人</span>
                                </div>
                                @foreach ($categoryFriends as $friend)
                                    @include('dashboard.partials.friend-card', [
                                        'friend' => $friend,
                                        'friendDisplayNames' => $friendDisplayNames,
                                        'friendNotificationTypes' => $friendNotificationTypes,
                                        'friendNotificationIcons' => $friendNotificationIcons,
                                        'friendUnreadNotifications' => $friendUnreadNotifications
                                    ])
                                @endforeach
                            </div>
                        @endif
                    @endforeach
                    
                    {{-- カテゴリー未分類の友達 --}}
                    @if($uncategorizedFriends->isNotEmpty())
                        <div class="space-y-2">
                            <div class="flex items-center justify-between px-2">
                                <h4 class="text-xs font-semibold text-gray-600">未分類</h4>
                                <span class="text-xs text-gray-400">{{ $uncategorizedFriends->count() }}人</span>
                            </div>
                            @foreach ($uncategorizedFriends as $friend)
                                @include('dashboard.partials.friend-card', [
                                    'friend' => $friend,
                                    'friendDisplayNames' => $friendDisplayNames,
                                    'friendNotificationTypes' => $friendNotificationTypes,
                                    'friendNotificationIcons' => $friendNotificationIcons,
                                    'friendUnreadNotifications' => $friendUnreadNotifications
                                ])
                            @endforeach
                        </div>
                    @endif
                </div>
                
                {{-- カテゴリーごとのタブコンテンツ --}}
                @foreach ($categories as $category)
                    @php
                        $categoryFriends = $friendsByCategory[$category->id] ?? collect();
                    @endphp
                    <div id="friends-{{ $category->id }}" class="friends-tab-content hidden space-y-2">
                        @if($categoryFriends->isNotEmpty())
                            @foreach ($categoryFriends as $friend)
                                @include('dashboard.partials.friend-card', [
                                    'friend' => $friend,
                                    'friendDisplayNames' => $friendDisplayNames,
                                    'friendNotificationTypes' => $friendNotificationTypes,
                                    'friendNotificationIcons' => $friendNotificationIcons,
                                    'friendUnreadNotifications' => $friendUnreadNotifications
                                ])
                            @endforeach
                        @else
                            <div class="bg-white p-6 rounded-2xl text-center border border-dashed border-gray-300">
                                <p class="text-gray-400 text-sm">このカテゴリーには友達がいません</p>
                            </div>
                        @endif
                    </div>
                @endforeach
                
                @if ($friends->isEmpty())
                    <div class="bg-white p-6 rounded-2xl text-center border border-dashed border-gray-300">
                        <p class="text-gray-400 text-sm">まだ友達がいません</p>
                    </div>
                @endif
            </div>
        </div>


        {{-- ③ 通知タイプ管理 --}}
        <div class="mb-8">
            <button onclick="toggleNotificationManagement()" class="w-full bg-white p-4 rounded-2xl shadow-sm border border-gray-100 flex items-center justify-between hover:bg-gray-50 transition">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-lg bg-purple-100 flex items-center justify-center text-purple-600">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
                          <path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 005.454-1.31A8.967 8.967 0 0118 9.75v-.7V9A6 6 0 006 9v.75a8.967 8.967 0 01-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 01-5.714 0m5.714 0a3 3 0 11-5.714 0" />
                        </svg>
                    </div>
                    <div class="text-left">
                        <h3 class="font-bold text-gray-800">通知管理</h3>
                        <p class="text-xs text-gray-500">通知タイプの設定とカスタム作成</p>
                    </div>
                </div>
                <svg id="notification-management-arrow" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5 text-gray-400 transition-transform">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
                </svg>
            </button>
            
            <div id="notification-management-content" class="hidden mt-3 space-y-3">
                <div class="flex justify-end">
                    <button onclick="openCreateCustomTypeModal()" class="text-xs bg-indigo-600 text-white px-3 py-1.5 rounded-full hover:bg-indigo-700 transition">
                        + 新規作成
                    </button>
                </div>
                
                <div class="space-y-3">
                {{-- システム定義の通知タイプ --}}
                @foreach($systemTypes as $type)
                    @php
                        $userNotificationType = auth()->user()->notificationTypes()
                            ->where('notification_type_id', $type->id)
                            ->first();
                        $customIcon = $userNotificationType?->pivot->icon_image ?? null;
                    @endphp
                    <div class="bg-white p-4 rounded-2xl shadow-sm border border-gray-100">
                        <div class="flex items-center gap-3">
                            <div class="w-12 h-12 rounded-lg flex items-center justify-center text-2xl shrink-0 overflow-hidden border-2 border-gray-200" 
                                 style="background-color: {{ $type->color ?? '#6B7280' }}20;">
                                @if($customIcon)
                                    <img src="{{ Storage::url($customIcon) }}" alt="アイコン" class="w-full h-full object-cover">
                                @else
                                    {{ $type->icon ?? '📢' }}
                                @endif
                            </div>
                            <div class="flex-1">
                                <div class="font-semibold text-gray-800">{{ $type->name }}</div>
                                <div class="text-xs text-gray-500">{{ $type->app_name ?? $type->name }}</div>
                            </div>
                            <button onclick="openIconEditModal({{ $type->id }}, 'system', '{{ $type->name }}')" class="text-xs bg-gray-100 text-gray-700 px-3 py-1.5 rounded-lg hover:bg-gray-200 transition">
                                アイコン変更
                            </button>
                        </div>
                    </div>
                @endforeach

                {{-- カスタム通知タイプ --}}
                @foreach($customTypes as $type)
                    <div class="bg-white p-4 rounded-2xl shadow-sm border-2 border-indigo-200">
                        <div class="flex items-center gap-3">
                            <div class="w-12 h-12 rounded-lg flex items-center justify-center text-2xl shrink-0 overflow-hidden border-2 border-indigo-300" 
                                 style="background-color: {{ $type->color ?? '#6B7280' }}20;">
                                @if($type->icon_image)
                                    <img src="{{ Storage::url($type->icon_image) }}" alt="アイコン" class="w-full h-full object-cover">
                                @elseif($type->icon)
                                    {{ $type->icon }}
                                @else
                                    📢
                                @endif
                            </div>
                            <div class="flex-1">
                                <div class="font-semibold text-gray-800">{{ $type->name }}</div>
                                <div class="text-xs text-gray-500">{{ $type->app_name ?? $type->name }}</div>
                            </div>
                            <div class="flex gap-2">
                                <button onclick="openIconEditModal({{ $type->id }}, 'custom', '{{ $type->name }}')" class="text-xs bg-gray-100 text-gray-700 px-3 py-1.5 rounded-lg hover:bg-gray-200 transition">
                                    アイコン変更
                                </button>
                                <button onclick="deleteCustomType({{ $type->id }})" class="text-xs bg-red-100 text-red-700 px-3 py-1.5 rounded-lg hover:bg-red-200 transition">
                                    削除
                                </button>
                            </div>
                        </div>
                    </div>
                @endforeach
                </div>
            </div>
        </div>
    </div>

    {{-- アイコン編集モーダル --}}
    <div id="icon-edit-modal" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm">
        <div class="bg-white w-full max-w-md mx-4 rounded-2xl shadow-2xl">
            <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between">
                <h2 class="font-bold text-lg text-gray-800" id="icon-edit-title">アイコンを変更</h2>
                <button onclick="closeIconEditModal()" class="text-gray-400 hover:text-gray-600 transition p-2 -mr-2">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-6 h-6">
                      <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
            <div class="p-6">
                <form id="icon-edit-form" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" id="icon-edit-type-id" name="type_id">
                    <input type="hidden" id="icon-edit-type-kind" name="type_kind">
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">アイコン画像</label>
                            <input 
                                type="file" 
                                name="icon_image" 
                                id="icon-image-input"
                                accept="image/*"
                                class="w-full bg-gray-50 border border-gray-200 rounded-xl px-4 py-3 text-base focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                            >
                        </div>
                        <div class="flex gap-3">
                            <button type="button" onclick="closeIconEditModal()" class="flex-1 px-4 py-2 text-gray-600 hover:bg-gray-100 rounded-lg transition-colors text-sm font-medium">
                                キャンセル
                            </button>
                            <button type="submit" class="flex-1 px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors text-sm font-medium">
                                保存
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- カスタム通知タイプ作成モーダル --}}
    <div id="create-custom-type-modal" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm">
        <div class="bg-white w-full max-w-md mx-4 rounded-2xl shadow-2xl max-h-[90vh] overflow-hidden flex flex-col">
            <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between">
                <h2 class="font-bold text-lg text-gray-800">カスタム通知タイプを作成</h2>
                <button onclick="closeCreateCustomTypeModal()" class="text-gray-400 hover:text-gray-600 transition p-2 -mr-2">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-6 h-6">
                      <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
            <div class="flex-1 overflow-y-auto p-6">
                <form id="create-custom-type-form" enctype="multipart/form-data">
                    @csrf
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">通知タイプ名</label>
                            <input type="text" name="name" required class="w-full bg-gray-50 border border-gray-200 rounded-xl px-4 py-3 text-base focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">偽装アプリ名</label>
                            <input type="text" name="app_name" class="w-full bg-gray-50 border border-gray-200 rounded-xl px-4 py-3 text-base focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">アイコン（絵文字）</label>
                            <input type="text" name="icon" maxlength="10" placeholder="📱" class="w-full bg-gray-50 border border-gray-200 rounded-xl px-4 py-3 text-base focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">アイコン画像</label>
                            <input type="file" name="icon_image" accept="image/*" class="w-full bg-gray-50 border border-gray-200 rounded-xl px-4 py-3 text-base focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">カラーコード</label>
                            <input type="color" name="color" value="#6B7280" class="w-full h-12 rounded-xl border border-gray-200">
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">テーマタイプ</label>
                            <select name="theme_type" class="w-full bg-gray-50 border border-gray-200 rounded-xl px-4 py-3 text-base focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                                <option value="system">システム設定風</option>
                                <option value="weather">天気予報風</option>
                                <option value="ad">広告・クーポン風</option>
                                <option value="calendar">カレンダー・タスク風</option>
                                <option value="game">ゲーム・SNS風</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">説明</label>
                            <textarea name="description" rows="3" class="w-full bg-gray-50 border border-gray-200 rounded-xl px-4 py-3 text-base focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 resize-none"></textarea>
                        </div>
                        <div class="flex gap-3 pt-4">
                            <button type="button" onclick="closeCreateCustomTypeModal()" class="flex-1 px-4 py-2 text-gray-600 hover:bg-gray-100 rounded-lg transition-colors text-sm font-medium">
                                キャンセル
                            </button>
                            <button type="submit" class="flex-1 px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors text-sm font-medium">
                                作成
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

@php
use Illuminate\Support\Facades\Storage;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
@endphp

    <script>
        // 通知管理セクションの開閉
        function toggleNotificationManagement() {
            const content = document.getElementById('notification-management-content');
            const arrow = document.getElementById('notification-management-arrow');
            content.classList.toggle('hidden');
            arrow.classList.toggle('rotate-180');
        }

        // アイコン編集モーダル
        function openIconEditModal(typeId, kind, name) {
            document.getElementById('icon-edit-type-id').value = typeId;
            document.getElementById('icon-edit-type-kind').value = kind;
            document.getElementById('icon-edit-title').textContent = name + 'のアイコンを変更';
            document.getElementById('icon-edit-modal').classList.remove('hidden');
        }

        function closeIconEditModal() {
            document.getElementById('icon-edit-modal').classList.add('hidden');
            document.getElementById('icon-edit-form').reset();
        }

        document.getElementById('icon-edit-form').addEventListener('submit', async function(e) {
            e.preventDefault();
            const formData = new FormData(this);
            const typeId = formData.get('type_id');
            const kind = formData.get('type_kind');
            
            formData.append('notification_type_id', typeId);
            
            try {
                const response = await fetch('{{ route("notifications.updateIcon") }}', {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: formData
                });
                
                if (response.ok) {
                    alert('アイコンを更新しました！');
                    window.location.reload();
                } else {
                    const data = await response.json();
                    alert(data.error || 'アイコンの更新に失敗しました');
                }
            } catch (error) {
                console.error('Error:', error);
                alert('アイコンの更新に失敗しました');
            }
        });

        // カスタム通知タイプ作成モーダル
        function openCreateCustomTypeModal() {
            document.getElementById('create-custom-type-modal').classList.remove('hidden');
        }

        function closeCreateCustomTypeModal() {
            document.getElementById('create-custom-type-modal').classList.add('hidden');
            document.getElementById('create-custom-type-form').reset();
        }

        document.getElementById('create-custom-type-form').addEventListener('submit', async function(e) {
            e.preventDefault();
            const formData = new FormData(this);
            
            try {
                const response = await fetch('{{ route("notifications.createCustomType") }}', {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: formData
                });
                
                const data = await response.json();
                
                if (response.ok) {
                    alert('カスタム通知タイプを作成しました！');
                    window.location.reload();
                } else {
                    alert(data.error || 'カスタム通知タイプの作成に失敗しました');
                }
            } catch (error) {
                console.error('Error:', error);
                alert('カスタム通知タイプの作成に失敗しました');
            }
        });

        // カスタム通知タイプ削除
        async function deleteCustomType(typeId) {
            if (!confirm('このカスタム通知タイプを削除しますか？')) return;
            
            try {
                const response = await fetch(`/notifications/custom-type/${typeId}`, {
                    method: 'DELETE',
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    }
                });
                
                if (response.ok) {
                    alert('カスタム通知タイプを削除しました');
                    window.location.reload();
                } else {
                    alert('削除に失敗しました');
                }
            } catch (error) {
                console.error('Error:', error);
                alert('削除に失敗しました');
            }
        }
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

                setTimeout(() => {
                    notification.close();
                }, 5000);
            }
        }

        // ページ読み込み時に通知許可をリクエスト
        if ('Notification' in window) {
            requestNotificationPermission();
        }

        // 未読通知をチェックしてブラウザ通知を表示
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

        // 友達メニューの開閉
        function toggleFriendMenu(friendId) {
            const menu = document.getElementById(`friend-menu-${friendId}`);
            // 他のメニューを閉じる
            document.querySelectorAll('[id^="friend-menu-"]').forEach(m => {
                if (m.id !== `friend-menu-${friendId}`) {
                    m.classList.add('hidden');
                }
            });
            menu.classList.toggle('hidden');
        }

        // メニュー外をクリックで閉じる
        document.addEventListener('click', function(e) {
            if (!e.target.closest('[id^="friend-menu-"]') && !e.target.closest('button[onclick^="toggleFriendMenu"]')) {
                document.querySelectorAll('[id^="friend-menu-"]').forEach(m => {
                    m.classList.add('hidden');
                });
            }
        });
        
        // QRコード表示
        function showMyQrCode() {
            document.getElementById('qr-modal').classList.remove('hidden');
        }
        
        function closeQrModal() {
            document.getElementById('qr-modal').classList.add('hidden');
        }
        
        // カテゴリータブの切り替え
        function switchCategoryTab(categoryId) {
            // 全てのタブを非アクティブにする
            document.querySelectorAll('.friend-tab').forEach(tab => {
                tab.classList.remove('active', 'bg-indigo-600', 'text-white');
                tab.classList.add('bg-gray-100', 'text-gray-700');
            });
            
            // 全てのコンテンツを非表示にする
            document.querySelectorAll('.friends-tab-content').forEach(content => {
                content.classList.add('hidden');
            });
            
            // 選択されたタブをアクティブにする
            const selectedTab = document.getElementById('tab-' + categoryId);
            if (selectedTab) {
                selectedTab.classList.add('active', 'bg-indigo-600', 'text-white');
                selectedTab.classList.remove('bg-gray-100', 'text-gray-700');
            }
            
            // 選択されたコンテンツを表示する
            const selectedContent = document.getElementById('friends-' + categoryId);
            if (selectedContent) {
                selectedContent.classList.remove('hidden');
            }
        }
        
        // 友達の表示名を編集
        function editFriendDisplayName(friendId, currentName) {
            const newName = prompt('表示名を入力してください:', currentName);
            if (newName === null) {
                return; // キャンセル
            }
            
            const displayName = newName.trim() || null;
            
            fetch(`/friends/${friendId}/display-name`, {
                method: 'PATCH',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({
                    display_name: displayName
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload(); // ページを再読み込みして変更を反映
                } else {
                    alert(data.message || '表示名の更新に失敗しました');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('表示名の更新に失敗しました');
            });
        }
        
        // カテゴリー管理モーダルを開く
        function openCategoryManager() {
            document.getElementById('category-manager-modal').classList.remove('hidden');
            backToCategorySelection();
        }

        // カテゴリー管理モーダルを閉じる
        function closeCategoryManager() {
            document.getElementById('category-manager-modal').classList.add('hidden');
            backToCategorySelection();
        }

        // カテゴリー選択画面に戻る
        function backToCategorySelection() {
            document.getElementById('category-selection-step').classList.remove('hidden');
            document.getElementById('friend-selection-step').classList.add('hidden');
            selectedCategoryId = null;
            // チェックボックスをクリア
            document.querySelectorAll('#friend-selection-step input[type="checkbox"]').forEach(checkbox => {
                checkbox.checked = false;
            });
        }

        // 選択されたカテゴリーIDを保持
        let selectedCategoryId = null;

        // カテゴリーを選択
        function selectCategory(categoryId, categoryName) {
            selectedCategoryId = categoryId;
            document.getElementById('selected-category-name').textContent = categoryName;
            
            // 既にそのカテゴリーに属している友達のチェックボックスをチェック
            document.querySelectorAll('#friend-selection-step input[type="checkbox"]').forEach(checkbox => {
                const friendId = parseInt(checkbox.dataset.friendId);
                const label = checkbox.closest('label');
                const currentCategoryId = label ? parseInt(label.dataset.currentCategory) : null;
                checkbox.checked = (currentCategoryId === categoryId);
            });
            
            // ステップ2に切り替え
            document.getElementById('category-selection-step').classList.add('hidden');
            document.getElementById('friend-selection-step').classList.remove('hidden');
        }

        // 友達を一括追加（決定ボタン）
        async function confirmAddFriends() {
            if (!selectedCategoryId) {
                alert('カテゴリーが選択されていません');
                return;
            }

            const checkboxes = document.querySelectorAll('#friend-selection-step input[type="checkbox"]:checked');
            const friendIds = Array.from(checkboxes).map(checkbox => parseInt(checkbox.dataset.friendId));

            if (friendIds.length === 0) {
                alert('追加する友達を選択してください');
                return;
            }

            try {
                // 選択された友達を順番に追加
                for (const friendId of friendIds) {
                    await assignFriendToCategorySilent(friendId, selectedCategoryId);
                }

                // 全て完了したらページを再読み込み
                location.reload();
            } catch (error) {
                console.error('Error:', error);
                alert('友達の追加に失敗しました');
            }
        }

        // サイレントに友達をカテゴリーに割り当て（アラートなし）
        async function assignFriendToCategorySilent(friendId, categoryId) {
            const response = await fetch('{{ route("friend-categories.assign") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({
                    friend_id: friendId,
                    category_id: categoryId
                })
            });
            
            if (!response.ok) {
                const data = await response.json();
                throw new Error(data.message || 'カテゴリーへの割り当てに失敗しました');
            }
            
            return await response.json();
        }

        // 新しいカテゴリーを作成
        async function createCategory() {
            const nameInput = document.getElementById('new-category-name');
            const name = nameInput.value.trim();
            
            if (!name) {
                alert('カテゴリー名を入力してください');
                return;
            }
            
            try {
                const response = await fetch('{{ route("friend-categories.store") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({ name: name })
                });
                
                const data = await response.json();
                
                if (response.ok) {
                    nameInput.value = '';
                    location.reload(); // ページを再読み込みしてカテゴリーを反映
                } else {
                    alert(data.message || 'カテゴリーの作成に失敗しました');
                }
            } catch (error) {
                console.error('Error:', error);
                alert('カテゴリーの作成に失敗しました');
            }
        }

        // 友達をカテゴリーに割り当て
        async function assignFriendToCategory(friendId, categoryId) {
            try {
                const response = await fetch('{{ route("friend-categories.assign") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({
                        friend_id: friendId,
                        category_id: categoryId
                    })
                });
                
                const data = await response.json();
                
                if (response.ok) {
                    location.reload(); // ページを再読み込みして変更を反映
                } else {
                    alert(data.message || 'カテゴリーへの割り当てに失敗しました');
                }
            } catch (error) {
                console.error('Error:', error);
                alert('カテゴリーへの割り当てに失敗しました');
            }
        }

        // カテゴリーを削除
        async function deleteCategory(categoryId, categoryName) {
            if (!confirm(`カテゴリー「${categoryName}」を削除しますか？\nこのカテゴリーに属する友達は「未分類」に移動されます。`)) {
                return;
            }
            
            try {
                const response = await fetch(`/friend-categories/${categoryId}`, {
                    method: 'DELETE',
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    }
                });
                
                const data = await response.json();
                
                if (response.ok) {
                    location.reload(); // ページを再読み込みして変更を反映
                } else {
                    alert(data.message || 'カテゴリーの削除に失敗しました');
                }
            } catch (error) {
                console.error('Error:', error);
                alert('カテゴリーの削除に失敗しました');
            }
        }
    </script>

    {{-- QRコード表示モーダル --}}
    <div id="qr-modal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4 pb-16">
        <div class="bg-white rounded-2xl shadow-lg p-8 pb-10 max-w-md w-full relative max-h-[90vh] overflow-y-auto">
            <button onclick="closeQrModal()" class="absolute top-4 right-4 text-gray-400 hover:text-gray-600 transition">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-6 h-6">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
            
            <div class="text-center mb-6">
                <h2 class="text-2xl font-bold text-gray-800 mb-2">友達追加用QRコード</h2>
                <p class="text-sm text-gray-500">このQRコードをスキャンして友達に追加してもらえます</p>
            </div>
            
            <div class="flex justify-center mb-6">
                <div class="bg-white p-4 rounded-xl border-2 border-gray-200">
                    {!! QrCode::size(250)->generate(route('friends.add-by-qr', ['user_id' => auth()->user()->id])) !!}
                </div>
            </div>
            
            <div class="text-center mb-8">
                <div class="inline-block bg-gray-100 rounded-lg px-4 py-2">
                    <p class="text-xs text-gray-500 mb-1">ユーザー名</p>
                    <p class="text-sm font-semibold text-gray-800">{{ auth()->user()->name }}</p>
                    @if(auth()->user()->username)
                        <p class="text-xs text-gray-400 mt-1">{{ '@' . auth()->user()->username }}</p>
                    @endif
                </div>
            </div>
            
            <div class="flex gap-3 mt-6 mb-2">
                <button onclick="closeQrModal()" class="flex-1 bg-gray-100 text-gray-700 text-center py-3 rounded-xl hover:bg-gray-200 transition font-medium">
                    閉じる
                </button>
                <a href="{{ route('qr.scan') }}" class="flex-1 bg-indigo-600 text-white text-center py-3 rounded-xl hover:bg-indigo-700 transition font-medium">
                    QRコードを読み込む
                </a>
            </div>
        </div>
    </div>

    {{-- カテゴリー管理モーダル --}}
    <div id="category-manager-modal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl shadow-xl max-w-md w-full max-h-[90vh] overflow-y-auto">
            <div class="p-6">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-lg font-bold text-gray-800">カテゴリー管理</h2>
                    <button onclick="closeCategoryManager()" class="text-gray-400 hover:text-gray-600 transition">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-6 h-6">
                          <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                {{-- 新しいカテゴリー作成 --}}
                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-700 mb-2">新しいカテゴリー</label>
                    <div class="flex gap-2">
                        <input 
                            type="text" 
                            id="new-category-name" 
                            placeholder="カテゴリー名を入力"
                            class="flex-1 border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                            onkeypress="if(event.key === 'Enter') createCategory()"
                        >
                        <button onclick="createCategory()" class="bg-indigo-600 text-white px-4 py-2 rounded-lg hover:bg-indigo-700 transition text-sm font-medium">
                            作成
                        </button>
                    </div>
                </div>

                {{-- ステップ1: カテゴリー選択 --}}
                <div id="category-selection-step" class="space-y-4">
                    @if($categories->isEmpty())
                        <p class="text-sm text-gray-500 text-center py-4">まずはカテゴリーを作成してください</p>
                    @else
                        @foreach ($categories as $category)
                            @php
                                $categoryFriends = $friendsByCategory[$category->id] ?? collect();
                            @endphp
                            <div class="border border-gray-200 rounded-lg p-4 bg-white hover:bg-gray-50 transition cursor-pointer" onclick="selectCategory({{ $category->id }}, '{{ addslashes($category->name) }}')">
                                <div class="flex items-center justify-between">
                                    <div class="flex-1">
                                        <h3 class="text-sm font-semibold text-gray-800 mb-1">{{ $category->name }}</h3>
                                        <p class="text-xs text-gray-500">{{ $categoryFriends->count() }}人の友達</p>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <button 
                                            onclick="event.stopPropagation(); deleteCategory({{ $category->id }}, '{{ addslashes($category->name) }}')" 
                                            class="text-red-500 hover:text-red-700 hover:bg-red-50 px-3 py-1.5 rounded transition text-xs font-medium"
                                            title="カテゴリーを削除"
                                        >
                                            削除
                                        </button>
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5 text-gray-400">
                                          <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" />
                                        </svg>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    @endif
                </div>

                {{-- ステップ2: 友達選択（チェックボックス） --}}
                <div id="friend-selection-step" class="hidden">
                    <div class="mb-4 flex items-center gap-3">
                        <button onclick="backToCategorySelection()" class="text-gray-500 hover:text-gray-700 transition">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5">
                              <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5" />
                            </svg>
                        </button>
                        <h3 class="text-sm font-semibold text-gray-800">
                            <span id="selected-category-name"></span>に友達を追加
                        </h3>
                    </div>

                    <div class="mb-4 max-h-64 overflow-y-auto border border-gray-200 rounded-lg p-3 bg-gray-50">
                        @foreach ($friends as $friend)
                            @php
                                $currentCategoryId = isset($friendCategoryIds[$friend->id]) ? $friendCategoryIds[$friend->id] : null;
                            @endphp
                            <label class="flex items-center gap-3 p-2 hover:bg-white rounded-lg cursor-pointer transition" data-friend-id="{{ $friend->id }}" data-current-category="{{ $currentCategoryId }}">
                                <input 
                                    type="checkbox" 
                                    class="w-4 h-4 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500"
                                    data-friend-id="{{ $friend->id }}"
                                >
                                <div class="w-10 h-10 rounded-full bg-indigo-100 flex items-center justify-center text-indigo-600 font-bold text-sm overflow-hidden shrink-0">
                                    @if($friend->avatar)
                                        <img src="{{ Storage::url($friend->avatar) }}" alt="{{ $friend->name }}" class="w-full h-full object-cover">
                                    @else
                                        {{ substr($friend->name, 0, 1) }}
                                    @endif
                                </div>
                                <div class="flex-1">
                                    <div class="text-sm font-medium text-gray-800">{{ $friend->name }}</div>
                                    @if($friend->username)
                                        <div class="text-xs text-gray-400">{{ '@' . $friend->username }}</div>
                                    @endif
                                </div>
                                @if($currentCategoryId)
                                    @php
                                        $currentCategory = $categories->firstWhere('id', $currentCategoryId);
                                    @endphp
                                    @if($currentCategory)
                                        <span class="text-xs text-gray-500 bg-gray-200 px-2 py-1 rounded">現在: {{ $currentCategory->name }}</span>
                                    @endif
                                @endif
                            </label>
                        @endforeach
                    </div>

                    <div class="flex gap-2">
                        <button 
                            onclick="backToCategorySelection()" 
                            class="flex-1 px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition text-sm font-medium"
                        >
                            キャンセル
                        </button>
                        <button 
                            onclick="confirmAddFriends()" 
                            class="flex-1 px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition text-sm font-medium"
                        >
                            決定
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>