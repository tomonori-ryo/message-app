@php
use Illuminate\Support\Facades\Storage;
@endphp
<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Profile') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                <div class="max-w-xl">
                    @include('profile.partials.update-profile-information-form')
                </div>
            </div>

            <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                <div class="max-w-xl">
                    @include('profile.partials.update-password-form')
                </div>
            </div>

            {{-- ブロックしたユーザー一覧 --}}
            <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                <div class="max-w-xl">
                    <section>
                        <header>
                            <h2 class="text-lg font-medium text-gray-900">
                                ブロックしたユーザー
                            </h2>
                            <p class="mt-1 text-sm text-gray-600">
                                ブロックしたユーザー一覧です。ブロックを解除できます。
                            </p>
                        </header>

                        <div class="mt-6 space-y-3">
                            @if($blockedUsers->isEmpty())
                                <p class="text-sm text-gray-500">ブロックしたユーザーはいません</p>
                            @else
                                @foreach($blockedUsers as $blockedUser)
                                    <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg border border-gray-200">
                                        <div class="flex items-center gap-3">
                                            <div class="w-10 h-10 rounded-full bg-gray-200 flex items-center justify-center text-gray-500 font-bold text-sm overflow-hidden shrink-0">
                                                @if($blockedUser->avatar)
                                                    <img src="{{ Storage::url($blockedUser->avatar) }}" alt="{{ $blockedUser->name }}" class="w-full h-full object-cover">
                                                @else
                                                    {{ substr($blockedUser->name, 0, 1) }}
                                                @endif
                                            </div>
                                            <div>
                                                <div class="font-medium text-gray-800">{{ $blockedUser->name }}</div>
                                                @if($blockedUser->username)
                                                    <div class="text-xs text-gray-500">{{ '@' . $blockedUser->username }}</div>
                                                @else
                                                    <div class="text-xs text-gray-500">{{ $blockedUser->email }}</div>
                                                @endif
                                            </div>
                                        </div>
                                        <form method="POST" action="{{ route('blocks.remove', $blockedUser->id) }}" onsubmit="return confirm('ブロックを解除しますか？')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="px-4 py-2 text-sm text-indigo-600 hover:text-indigo-800 hover:bg-indigo-50 rounded-lg transition">
                                                ブロック解除
                                            </button>
                                        </form>
                                    </div>
                                @endforeach
                            @endif
                        </div>
                    </section>
                </div>
            </div>

            <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                <div class="max-w-xl">
                    @include('profile.partials.delete-user-form')
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
