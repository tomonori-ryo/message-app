@php
use Illuminate\Support\Facades\Storage;
@endphp
@foreach ($messages as $message)
    @if ($message->user_id == auth()->id())
        {{-- ■ 自分（右側） --}}
        <div class="flex justify-end items-end gap-2">
            <div class="max-w-[70%]">
                <div class="bg-green-200 text-gray-900 px-4 py-2 rounded-2xl rounded-tr-none shadow-sm message-bubble" data-message-id="{{ $message->id }}" data-message-body="{{ $message->body }}">
                    <p class="text-sm break-words">{{ $message->body }}</p>
                </div>
                <p class="text-[10px] text-gray-500 text-right mt-1 mr-1">
                    {{ $message->created_at->timezone('Asia/Tokyo')->format('H:i') }}
                </p>
            </div>
        </div>
    @else
        {{-- ■ 相手（左側） --}}
        <div class="flex justify-start items-end gap-2">
            {{-- アイコン --}}
            <div class="w-8 h-8 rounded-full bg-gray-400 flex items-center justify-center text-white text-xs font-bold shrink-0 overflow-hidden">
                @if($message->user->avatar)
                    <img src="{{ Storage::url($message->user->avatar) }}" alt="{{ $message->user->name }}" class="w-full h-full object-cover">
                @else
                    {{ substr($message->user->name, 0, 1) }}
                @endif
            </div>

            <div class="max-w-[70%]">
                <div class="bg-white text-gray-900 px-4 py-2 rounded-2xl rounded-tl-none shadow-sm border border-gray-100 message-bubble" data-message-id="{{ $message->id }}" data-message-body="{{ $message->body }}">
                    <p class="text-sm break-words">{{ $message->body }}</p>
                </div>
                <p class="text-[10px] text-gray-500 text-left mt-1 ml-1">
                    {{ $message->created_at->timezone('Asia/Tokyo')->format('H:i') }}
                </p>
            </div>
        </div>
    @endif
@endforeach

@if ($messages->isEmpty())
    <div class="text-center text-gray-400 py-20">
        <p class="text-sm">メッセージはまだありません</p>
    </div>
@endif