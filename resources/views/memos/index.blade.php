<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>メモ一覧</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-50 font-sans text-gray-900 antialiased">

    {{-- ■ 上部ヘッダー（すりガラス風） --}}
    <header class="fixed top-0 left-0 right-0 bg-white/90 backdrop-blur-md z-50 h-16 flex items-center px-4 border-b border-gray-200/80 shadow-sm">
        <div class="max-w-3xl mx-auto w-full flex items-center gap-3">
            {{-- 戻るボタン --}}
            <a href="{{ route('dashboard') }}" class="p-2 -ml-2 text-gray-500 hover:text-indigo-600 hover:bg-gray-100 rounded-full transition-colors duration-200">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-5 h-5">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5" />
                </svg>
            </a>
            
            {{-- タイトル --}}
            <h1 class="font-bold text-lg text-gray-800 tracking-tight truncate flex items-center gap-2">
                @if(isset($user))
                    <div class="w-8 h-8 rounded-full bg-indigo-100 flex items-center justify-center text-indigo-600 text-xs font-bold shrink-0">
                        {{ substr($user->name, 0, 1) }}
                    </div>
                    <span><span class="text-indigo-600">{{ $user->name }}</span> <span class="text-sm font-normal text-gray-500">とのメモ</span></span>
                @else
                    <span>すべてのメモ</span>
                @endif
            </h1>
        </div>
    </header>

    {{-- ■ メインコンテンツ --}}
    <main class="pt-20 pb-24 px-4 max-w-3xl mx-auto min-h-screen">
        
        <div class="space-y-4 pt-4">
            
            {{-- メモがない場合のデザイン --}}
            @if($memos->isEmpty())
                <div class="mt-12 text-center">
                    <div class="inline-flex items-center justify-center w-20 h-20 bg-gray-100 rounded-full mb-4">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-9 h-9 text-gray-400">
                          <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0115.75 21H5.25A2.25 2.25 0 013 18.75V8.25A2.25 2.25 0 015.25 6H10" />
                        </svg>
                    </div>
                    <h3 class="text-lg font-bold text-gray-700 mb-2">まだメモがありません</h3>
                    <p class="text-gray-500 text-sm mb-8 max-w-xs mx-auto">
                        @if(isset($user))
                            {{ $user->name }} さんとのやり取りで忘れたくないことをメモしておきましょう。
                        @else
                            チャット画面から重要な情報をメモに追加できます。
                        @endif
                    </p>
                    <a href="{{ route('chat') }}" class="inline-flex items-center gap-2 px-6 py-3 bg-indigo-600 text-white font-bold text-sm rounded-full hover:bg-indigo-700 transition-all shadow-md hover:shadow-lg active:scale-95">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4"><path stroke-linecap="round" stroke-linejoin="round" d="M8.625 9.75a.375.375 0 11-.75 0 .375.375 0 01.75 0zm0 0H8.25m4.125 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zm0 0H12m4.125 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zm0 0h-.375m-13.5 3.01c0 1.6 1.123 2.994 2.707 3.227 1.087.16 2.185.283 3.293.369V21l4.184-4.183a1.14 1.14 0 01.778-.332 48.294 48.294 0 005.83-.498c1.585-.233 2.708-1.626 2.708-3.228V6.741c0-1.602-1.123-2.995-2.707-3.228A48.394 48.394 0 0012 3c-2.392 0-4.744.175-7.043.513C3.373 3.746 2.25 5.14 2.25 6.741v6.018z" /></svg>
                        チャットへ戻る
                    </a>
                </div>
            @endif

            {{-- メモ一覧ループ --}}
            @foreach($memos as $memo)
                <div class="group bg-white p-5 rounded-2xl shadow-sm hover:shadow-md border border-gray-100 transition-all duration-200" id="memo-{{ $memo->id }}">
                    {{-- 表示モード --}}
                    <div class="memo-display" id="display-{{ $memo->id }}">
                        <div class="flex items-start justify-between gap-4">
                            {{-- メモ本文 --}}
                            <div class="flex-1 min-w-0">
                                <p class="text-gray-800 whitespace-pre-wrap text-[15px] leading-relaxed break-words">{{ $memo->content }}</p>
                                
                                <div class="mt-3 flex items-center gap-2">
                                    <span class="text-[11px] font-medium text-gray-400 bg-gray-50 px-2 py-1 rounded-md border border-gray-100">
                                        {{ $memo->created_at->format('Y/m/d H:i') }}
                                    </span>
                                </div>
                            </div>
                            
                            {{-- ボタン群 --}}
                            <div class="flex items-center gap-1 shrink-0">
                                {{-- 編集ボタン --}}
                                <button onclick="editMemo({{ $memo->id }}, '{{ addslashes($memo->content) }}')" class="p-2 text-gray-300 hover:text-indigo-500 hover:bg-indigo-50 rounded-xl transition-all" title="編集">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                                      <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0115.75 21H5.25A2.25 2.25 0 013 18.75V8.25A2.25 2.25 0 015.25 6H10" />
                                    </svg>
                                </button>
                                
                                {{-- 削除ボタン --}}
                                <form action="{{ route('memos.destroy', $memo) }}" method="POST" onsubmit="return confirm('削除してもよろしいですか？');" class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="p-2 text-gray-300 hover:text-red-500 hover:bg-red-50 rounded-xl transition-all" title="削除">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                                          <path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0" />
                                        </svg>
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                    
                    {{-- 編集モード --}}
                    <div class="memo-edit hidden" id="edit-{{ $memo->id }}">
                        <form onsubmit="saveMemo(event, {{ $memo->id }})" class="space-y-3">
                            @csrf
                            @method('PATCH')
                            <textarea 
                                id="content-{{ $memo->id }}" 
                                name="content" 
                                class="w-full bg-gray-50 border border-gray-200 rounded-xl px-4 py-3 text-[15px] leading-relaxed resize-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500" 
                                rows="6" 
                                required>{{ $memo->content }}</textarea>
                            
                            <div class="flex items-center justify-end gap-2">
                                <button 
                                    type="button" 
                                    onclick="cancelEdit({{ $memo->id }}, '{{ addslashes($memo->content) }}')" 
                                    class="px-4 py-2 text-gray-600 hover:bg-gray-100 rounded-lg transition-colors text-sm font-medium">
                                    キャンセル
                                </button>
                                <button 
                                    type="submit" 
                                    class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors text-sm font-medium">
                                    保存
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            @endforeach

        </div>
    </main>

    <script>
        function editMemo(memoId, originalContent) {
            // 表示モードを非表示
            document.getElementById('display-' + memoId).classList.add('hidden');
            // 編集モードを表示
            document.getElementById('edit-' + memoId).classList.remove('hidden');
            // テキストエリアにフォーカス
            const textarea = document.getElementById('content-' + memoId);
            textarea.focus();
            textarea.setSelectionRange(textarea.value.length, textarea.value.length);
        }

        function cancelEdit(memoId, originalContent) {
            // 編集モードを非表示
            document.getElementById('edit-' + memoId).classList.add('hidden');
            // 表示モードを表示
            document.getElementById('display-' + memoId).classList.remove('hidden');
            // テキストエリアの内容を元に戻す
            document.getElementById('content-' + memoId).value = originalContent;
        }

        function saveMemo(event, memoId) {
            event.preventDefault();
            
            const form = event.target;
            const formData = new FormData(form);
            const content = formData.get('content');
            
            // 保存中表示
            const submitButton = form.querySelector('button[type="submit"]');
            const originalText = submitButton.textContent;
            submitButton.textContent = '保存中...';
            submitButton.disabled = true;
            
            fetch(`/memos/${memoId}`, {
                method: 'PATCH',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value
                },
                body: JSON.stringify({ content: content })
            })
            .then(response => {
                if (response.ok) {
                    return response.json();
                }
                throw new Error('保存に失敗しました');
            })
            .then(data => {
                // ページをリロードして最新の状態を表示
                window.location.reload();
            })
            .catch(error => {
                alert(error.message || '保存に失敗しました');
                submitButton.textContent = originalText;
                submitButton.disabled = false;
            });
        }
    </script>
</body>
</html>