<x-guest-layout>
    <div class="mb-4 text-center">
        <h2 class="text-2xl font-bold text-gray-900">アカウント名を設定</h2>
        <p class="mt-2 text-sm text-gray-600">アカウント名を設定してください。この名前で検索されます。</p>
    </div>

    <form method="POST" action="{{ route('username.setup.store') }}">
        @csrf

        <!-- Username -->
        <div>
            <x-input-label for="username" :value="__('Username')" />
            <x-text-input id="username" class="block mt-1 w-full" type="text" name="username" :value="old('username')" required autofocus autocomplete="username" placeholder="例: user123" />
            <p class="mt-1 text-sm text-gray-500">英数字とアンダースコア（_）のみ使用可能です</p>
            <x-input-error :messages="$errors->get('username')" class="mt-2" />
        </div>

        <div class="flex items-center justify-end mt-6">
            <x-primary-button>
                設定する
            </x-primary-button>
        </div>
    </form>
</x-guest-layout>

