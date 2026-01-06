<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>通知設定</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
@php
use Illuminate\Support\Facades\Storage;
@endphp
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
            <h1 class="font-bold text-lg text-gray-800 tracking-tight">通知設定</h1>
        </div>
    </header>

    {{-- ■ メインコンテンツ --}}
    <main class="pt-20 pb-24 px-4 max-w-3xl mx-auto min-h-screen">
        
        @if(session('success'))
            <div class="mb-4 p-4 bg-green-50 border border-green-200 rounded-xl text-green-800 text-sm">
                {{ session('success') }}
            </div>
        @endif

        <div class="space-y-4 pt-4">
            
            <form method="POST" action="{{ route('notifications.update') }}" class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                @csrf
                @method('PATCH')
                
                <h2 class="text-lg font-bold text-gray-800 mb-6">通知タイプを選択</h2>
                <p class="text-sm text-gray-500 mb-6">受け取りたい通知の種類を1つ選択してください</p>
                
                <div class="space-y-3">
                    @php
                        $selectedTypeId = !empty($userNotificationTypes) ? $userNotificationTypes[0] : null;
                    @endphp
                    @foreach($notificationTypes as $type)
                        @php
                            $userNotificationType = $user->notificationTypes()
                                ->where('notification_type_id', $type->id)
                                ->first();
                            $customIcon = $userNotificationType?->pivot->icon_image ?? null;
                            $isSelected = $selectedTypeId == $type->id;
                        @endphp
                        <label class="flex items-center justify-between p-4 border-2 rounded-xl cursor-pointer transition-all hover:shadow-md {{ $isSelected ? 'border-indigo-500 bg-indigo-50' : 'border-gray-200 hover:bg-gray-50' }}" 
                               style="border-left-width: 4px; border-left-color: {{ $type->color ?? '#6B7280' }};">
                            <div class="flex items-center gap-3 flex-1">
                                <div class="w-12 h-12 rounded-lg flex items-center justify-center text-2xl shrink-0 overflow-hidden transition-all {{ $isSelected ? 'scale-110' : '' }}" 
                                     style="background-color: {{ $type->color ?? '#6B7280' }}20;">
                                    @if($customIcon)
                                        <img src="{{ Storage::url($customIcon) }}" alt="カスタムアイコン" class="w-full h-full object-cover">
                                    @elseif($type->icon)
                                        {{ $type->icon }}
                                    @else
                                        <div class="w-full h-full flex items-center justify-center text-white font-bold text-sm" style="background-color: {{ $type->color ?? '#6B7280' }};">
                                            {{ substr($type->name, 0, 1) }}
                                        </div>
                                    @endif
                                </div>
                                <div class="flex-1">
                                    <h3 class="font-semibold text-gray-800 mb-1 {{ $isSelected ? 'text-indigo-900' : '' }}">{{ $type->name }}</h3>
                                    <p class="text-xs text-gray-400 mb-1">偽装アプリ: {{ $type->app_name ?? $type->name }}</p>
                                    @if($type->description)
                                        <p class="text-sm text-gray-500">{{ $type->description }}</p>
                                    @endif
                                </div>
                            </div>
                            <div class="relative inline-flex items-center ml-4 shrink-0">
                                <input 
                                    type="radio" 
                                    name="notification_type" 
                                    value="{{ $type->id }}"
                                    {{ $isSelected ? 'checked' : '' }}
                                    class="sr-only peer"
                                    onchange="updateRadioState(this)"
                                >
                                <div class="w-6 h-6 border-2 rounded-full flex items-center justify-center shrink-0 transition-all peer-checked:border-indigo-600 peer-checked:bg-indigo-600" 
                                     style="border-color: {{ $type->color ?? '#6B7280' }}60;">
                                    <svg class="w-4 h-4 text-white hidden peer-checked:block" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                    </svg>
                                </div>
                            </div>
                        </label>
                    @endforeach
                    
                    @if($notificationTypes->isEmpty())
                        <div class="text-center py-8 text-gray-500">
                            <p>通知タイプが登録されていません</p>
                        </div>
                    @endif
                </div>
                
                <div class="mt-8 pt-6 border-t border-gray-200">
                    <button type="submit" class="w-full bg-indigo-600 text-white font-bold py-3 rounded-xl hover:bg-indigo-700 transition-colors shadow-md hover:shadow-lg active:scale-[0.98]">
                        設定を保存
                    </button>
                </div>
            </form>

            {{-- カスタムアイコン設定 --}}
            @if($selectedTypeId)
            @foreach($notificationTypes as $type)
                @if($type->id == $selectedTypeId)
                    @php
                        $userNotificationType = $user->notificationTypes()
                            ->where('notification_type_id', $type->id)
                            ->first();
                        $customIcon = $userNotificationType?->pivot->icon_image ?? null;
                    @endphp
                    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-4 sm:p-6 mt-4" data-type-id="{{ $type->id }}">
                        <h3 class="font-semibold text-gray-800 mb-4 flex items-center gap-2 text-sm sm:text-base">
                            <span style="color: {{ $type->color ?? '#6B7280' }};">{{ $type->icon ?? '📢' }}</span>
                            {{ $type->name }}のアイコン設定
                        </h3>
                        
                        <div class="space-y-4">
                            {{-- 現在のアイコン表示 --}}
                            <div class="flex items-center gap-3 sm:gap-4">
                                <div class="w-12 h-12 sm:w-16 sm:h-16 rounded-lg flex items-center justify-center text-2xl sm:text-3xl shrink-0 overflow-hidden border-2 border-gray-200" 
                                     style="background-color: {{ $type->color ?? '#6B7280' }}20;">
                                    @if($customIcon)
                                        <img src="{{ Storage::url($customIcon) }}" alt="カスタムアイコン" class="w-full h-full object-cover">
                                    @else
                                        {{ $type->icon ?? '📢' }}
                                    @endif
                                </div>
                                <div class="flex-1">
                                    <p class="text-sm text-gray-600">現在のアイコン</p>
                                    @if($customIcon)
                                        <button onclick="removeCustomIcon({{ $type->id }})" class="mt-2 text-xs text-red-500 hover:text-red-700">
                                            アイコンを削除
                                        </button>
                                    @endif
                                </div>
                            </div>
                            
                            {{-- アイコン画像アップロード --}}
                            <form id="icon-form-{{ $type->id }}" enctype="multipart/form-data" class="space-y-3">
                                @csrf
                                <input 
                                    type="file" 
                                    name="icon_image" 
                                    id="icon-input-{{ $type->id }}"
                                    accept="image/*"
                                    class="hidden"
                                    onchange="uploadIcon({{ $type->id }}, this.files[0])"
                                >
                                <label 
                                    for="icon-input-{{ $type->id }}" 
                                    class="block w-full px-4 py-3 bg-gray-50 border-2 border-dashed border-gray-300 rounded-xl cursor-pointer hover:bg-gray-100 transition-colors text-center text-sm text-gray-600"
                                >
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6 mx-auto mb-1">
                                      <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5m-13.5-9L12 3m0 0l4.5 4.5M12 3v13.5" />
                                    </svg>
                                    アイコン画像を選択
                                </label>
                            </form>
                        </div>
                    </div>
                @endif
            @endforeach
            @endif
            
        </div>
    </main>

    <script>
        // ラジオボタンの状態に応じてカードのスタイルを更新
        function updateRadioState(radio) {
            // 全てのカードから選択状態のスタイルをリセット
            document.querySelectorAll('input[name="notification_type"]').forEach(r => {
                const card = r.closest('label');
                card.classList.remove('border-indigo-500', 'bg-indigo-50');
                card.classList.add('border-gray-200', 'hover:bg-gray-50');
                const title = card.querySelector('h3');
                if (title) {
                    title.classList.remove('text-indigo-900');
                }
                const icon = card.querySelector('.w-12');
                if (icon) {
                    icon.classList.remove('scale-110');
                }
            });
            
            // 選択されたカードにスタイルを適用
            if (radio.checked) {
                const card = radio.closest('label');
                card.classList.add('border-indigo-500', 'bg-indigo-50');
                card.classList.remove('border-gray-200', 'hover:bg-gray-50');
                const title = card.querySelector('h3');
                if (title) {
                    title.classList.add('text-indigo-900');
                }
                const icon = card.querySelector('.w-12');
                if (icon) {
                    icon.classList.add('scale-110');
                }
            }
        }

        // ページ読み込み時に選択状態を反映
        document.addEventListener('DOMContentLoaded', function() {
            const checkedRadio = document.querySelector('input[name="notification_type"]:checked');
            if (checkedRadio) {
                updateRadioState(checkedRadio);
            }
        });

        // アイコン画像をアップロード
        async function uploadIcon(typeId, file) {
            if (!file) {
                alert('ファイルが選択されていません');
                return;
            }
            
            // ファイルサイズチェック（2MB）
            if (file.size > 2 * 1024 * 1024) {
                alert('ファイルサイズが大きすぎます（最大2MB）');
                return;
            }
            
            // アップロード中のフィードバック
            const input = document.getElementById(`icon-input-${typeId}`);
            const label = input.nextElementSibling;
            const originalText = label.innerHTML;
            label.innerHTML = '<span class="text-indigo-600">アップロード中...</span>';
            label.style.pointerEvents = 'none';
            
            const formData = new FormData();
            formData.append('icon_image', file);
            formData.append('notification_type_id', typeId);
            formData.append('type_kind', 'system');
            
            try {
                const response = await fetch('{{ route("notifications.updateIcon") }}', {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: formData
                });
                
                let data;
                try {
                    data = await response.json();
                } catch (e) {
                    throw new Error('レスポンスの解析に失敗しました');
                }
                
                if (response.ok) {
                    label.innerHTML = '<span class="text-green-600">✓ アップロード完了</span>';
                    setTimeout(() => {
                        // ページをリロードして新しいアイコンを表示
                        window.location.reload();
                    }, 500);
                } else {
                    label.innerHTML = originalText;
                    label.style.pointerEvents = 'auto';
                    let errorMessage = 'アイコンのアップロードに失敗しました';
                    
                    if (data) {
                        if (data.errors && data.errors.icon_image) {
                            errorMessage = data.errors.icon_image[0];
                        } else if (data.message) {
                            errorMessage = data.message;
                        } else if (data.error) {
                            errorMessage = data.error;
                        }
                    }
                    
                    alert(errorMessage);
                    console.error('Upload error:', data);
                }
            } catch (error) {
                label.innerHTML = originalText;
                label.style.pointerEvents = 'auto';
                console.error('Error:', error);
                alert('アイコンのアップロードに失敗しました: ' + error.message);
            }
        }
        
        // カスタムアイコンを削除
        async function removeCustomIcon(typeId) {
            if (!confirm('カスタムアイコンを削除しますか？')) return;
            
            try {
                const response = await fetch(`/notifications/icon/${typeId}`, {
                    method: 'DELETE',
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    }
                });
                
                if (response.ok) {
                    window.location.reload();
                } else {
                    alert('アイコンの削除に失敗しました');
                }
            } catch (error) {
                console.error('Error:', error);
                alert('アイコンの削除に失敗しました');
            }
        }
    </script>
</body>
</html>

