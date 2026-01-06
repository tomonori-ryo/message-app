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
            {{-- 通知設定 --}}
            <a href="{{ route('notifications.settings') }}" class="p-2 bg-purple-100 rounded-full hover:bg-purple-200 transition" title="通知設定">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6 text-purple-600">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 005.454-1.31A8.967 8.967 0 0118 9.75v-.7V9A6 6 0 006 9v.75a8.967 8.967 0 01-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 01-5.714 0m5.714 0a3 3 0 11-5.714 0" />
                </svg>
            </a>
            
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

        {{-- ① 友達リスト --}}
        <div class="mb-8">
            <h3 class="text-sm font-bold text-gray-500 mb-2 pl-1">友達リスト</h3>
            <div class="flex flex-col gap-2">
                @foreach ($friends as $friend)
                    {{-- カード全体枠 --}}
                    <div class="bg-white p-3 pr-4 rounded-2xl shadow-sm border border-gray-100 flex items-center justify-between gap-3 transition active:scale-[0.99]">
                        
                        {{-- 左側（タップでチャットへ）：flex-1で余白を埋める --}}
                        <a href="{{ route('chat', ['user' => $friend->id]) }}" class="flex-1 flex items-center gap-3 py-1">
                            <div class="w-12 h-12 rounded-full bg-indigo-100 flex items-center justify-center text-indigo-600 font-bold text-lg overflow-hidden shrink-0">
                                @if($friend->avatar)
                                    <img src="{{ Storage::url($friend->avatar) }}" alt="{{ $friend->name }}" class="w-full h-full object-cover">
                                @else
                                    {{ substr($friend->name, 0, 1) }}
                                @endif
                            </div>
                            <div>
                                <div class="font-bold text-gray-800">{{ $friend->name }}</div>
                                @if($friend->username)
                                    <div class="text-xs text-gray-400">{{ '@' . $friend->username }}</div>
                                @else
                                    <div class="text-xs text-gray-400">タップしてチャット</div>
                                @endif
                            </div>
                        </a>

                        {{-- 右側（メモ一覧ボタン）：独立したリンク --}}
                        <a href="{{ route('memos.by_user', $friend->id) }}" class="flex flex-col items-center justify-center w-14 h-12 bg-yellow-50 hover:bg-yellow-100 text-yellow-600 rounded-xl border border-yellow-100 transition shrink-0">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5 mb-0.5">
                              <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0115.75 21H5.25A2.25 2.25 0 013 18.75V8.25A2.25 2.25 0 015.25 6H10" />
                            </svg>
                            <span class="text-[10px] font-bold">メモ</span>
                        </a>

                    </div>
                @endforeach

                @if ($friends->isEmpty())
                    <div class="bg-white p-6 rounded-2xl text-center border border-dashed border-gray-300">
                        <p class="text-gray-400 text-sm">まだ友達がいません</p>
                    </div>
                @endif
            </div>
        </div>

        {{-- ② ユーザー検索 --}}
        <div class="mb-8">
            <h3 class="text-sm font-bold text-gray-500 mb-2 pl-1">ユーザー検索</h3>
            <form method="GET" action="{{ route('dashboard') }}" class="mb-4">
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
                                
                                @if($isFriend)
                                    <span class="text-xs text-gray-400 px-3 py-1 bg-gray-100 rounded-full">友達</span>
                                @else
                                    <form method="POST" action="{{ route('friends.add', $result->id) }}">
                                        @csrf
                                        <button type="submit" class="bg-slate-800 text-white text-xs font-bold px-4 py-2 rounded-full hover:bg-black transition">
                                            追加
                                        </button>
                                    </form>
                                @endif
                            </div>
                        @endforeach
                    @else
                        <div class="bg-white p-6 rounded-2xl text-center border border-dashed border-gray-300">
                            <p class="text-gray-400 text-sm">検索結果が見つかりませんでした</p>
                        </div>
                    @endif
                </div>
            @else
                {{-- 知り合いかも？セクション（検索していない時のみ表示） --}}
                <div class="flex flex-col gap-2">
                    @foreach ($others as $other)
                        <div class="bg-white p-3 rounded-2xl shadow-sm border border-gray-100 flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-full bg-gray-200 flex items-center justify-center text-gray-500 font-bold text-sm overflow-hidden">
                                    @if($other->avatar)
                                        <img src="{{ Storage::url($other->avatar) }}" alt="{{ $other->name }}" class="w-full h-full object-cover">
                                    @else
                                        {{ substr($other->name, 0, 1) }}
                                    @endif
                                </div>
                                <div>
                                    <div class="font-medium text-gray-700 text-sm">{{ $other->name }}</div>
                                    @if($other->username)
                                        <div class="text-xs text-gray-400">{{ '@' . $other->username }}</div>
                                    @else
                                        <div class="text-xs text-gray-400">{{ $other->email }}</div>
                                    @endif
                                </div>
                            </div>
                            
                            <form method="POST" action="{{ route('friends.add', $other->id) }}">
                                @csrf
                                <button type="submit" class="bg-slate-800 text-white text-xs font-bold px-4 py-2 rounded-full hover:bg-black transition">
                                    追加
                                </button>
                            </form>
                        </div>
                    @endforeach
                    @if($others->isEmpty())
                        <div class="bg-white p-6 rounded-2xl text-center border border-dashed border-gray-300">
                            <p class="text-gray-400 text-sm">まだ友達がいません</p>
                        </div>
                    @endif
                </div>
            @endif
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
    </script>
</body>
</html>