@php
use Illuminate\Support\Facades\Storage;
@endphp
<section>
    <header>
        <h2 class="text-lg font-medium text-gray-900">
            プロフィール情報
        </h2>

        <p class="mt-1 text-sm text-gray-600">
            アカウントのプロフィール情報とメールアドレスを更新できます。
        </p>
    </header>

    <form id="send-verification" method="post" action="{{ route('verification.send') }}">
        @csrf
    </form>

    {{-- アバター設定セクション --}}
    <div class="mb-6 pb-6 border-b border-gray-200">
        <h3 class="text-sm font-semibold text-gray-700 mb-4">プロフィール画像</h3>
        <div class="flex items-center gap-4">
            <div class="w-20 h-20 rounded-full bg-gray-200 flex items-center justify-center border-2 border-gray-300 overflow-hidden shrink-0">
                @if($user->avatar)
                    <img src="{{ Storage::url($user->avatar) }}" alt="アバター" class="w-full h-full object-cover">
                @else
                    <span class="text-2xl font-bold text-gray-500">{{ substr($user->name, 0, 1) }}</span>
                @endif
            </div>
            <div class="flex-1">
                <form method="post" action="{{ route('profile.avatar.update') }}" enctype="multipart/form-data" class="space-y-2">
                    @csrf
                    <input type="file" name="avatar" id="avatar-input" accept="image/*" class="hidden" onchange="this.form.submit()">
                    <label for="avatar-input" class="inline-block px-4 py-2 bg-indigo-600 text-white text-sm rounded-lg hover:bg-indigo-700 cursor-pointer transition-colors">
                        画像を選択
                    </label>
                    @if($user->avatar)
                        <form method="post" action="{{ route('profile.avatar.delete') }}" class="inline">
                            @csrf
                            @method('delete')
                            <button type="submit" class="text-xs text-red-500 hover:text-red-700 mt-2 block">
                                画像を削除
                            </button>
                        </form>
                    @endif
                </form>
            </div>
        </div>
    </div>

    <form method="post" action="{{ route('profile.update') }}" class="mt-6 space-y-6">
        @csrf
        @method('patch')

        <div>
            <x-input-label for="name" value="名前" />
            <x-text-input id="name" name="name" type="text" class="mt-1 block w-full" :value="old('name', $user->name)" required autofocus autocomplete="name" />
            <p class="mt-1 text-sm text-gray-500">表示名（変更可能）</p>
            <x-input-error class="mt-2" :messages="$errors->get('name')" />
        </div>

        <div class="mt-4">
            <x-input-label for="username" value="アカウント名" />
            <x-text-input id="username" name="username" type="text" class="mt-1 block w-full" :value="old('username', $user->username)" required autocomplete="username" />
            <p class="mt-1 text-sm text-gray-500">アカウント名（検索で使用されます。英数字とアンダースコアのみ）</p>
            <x-input-error class="mt-2" :messages="$errors->get('username')" />
        </div>

        <div class="mt-4">
            <x-input-label for="email" value="メールアドレス" />
            <x-text-input id="email" name="email" type="email" class="mt-1 block w-full" :value="old('email', $user->email)" required autocomplete="username" />
            <x-input-error class="mt-2" :messages="$errors->get('email')" />

            @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
                <div>
                    <p class="text-sm mt-2 text-gray-800">
                        メールアドレスが認証されていません。

                        <button form="send-verification" class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                            こちらをクリックして認証メールを再送信してください。
                        </button>
                    </p>

                    @if (session('status') === 'verification-link-sent')
                        <p class="mt-2 font-medium text-sm text-green-600">
                            新しい認証リンクをメールアドレスに送信しました。
                        </p>
                    @endif
                </div>
            @endif
        </div>

        <div class="flex items-center gap-4">
            <x-primary-button>保存</x-primary-button>

            @if (session('status') === 'profile-updated')
                <p
                    x-data="{ show: true }"
                    x-show="show"
                    x-transition
                    x-init="setTimeout(() => show = false, 2000)"
                    class="text-sm text-gray-600"
                >保存しました</p>
            @endif
            @if (session('status') === 'avatar-updated')
                <p
                    x-data="{ show: true }"
                    x-show="show"
                    x-transition
                    x-init="setTimeout(() => show = false, 2000)"
                    class="text-sm text-green-600"
                >プロフィール画像を更新しました</p>
            @endif
            @if (session('status') === 'avatar-deleted')
                <p
                    x-data="{ show: true }"
                    x-show="show"
                    x-transition
                    x-init="setTimeout(() => show = false, 2000)"
                    class="text-sm text-gray-600"
                >プロフィール画像を削除しました</p>
            @endif
        </div>
    </form>
</section>
