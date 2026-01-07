<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>QRコード</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-slate-50 font-sans text-gray-900 antialiased">
    <div class="min-h-screen flex items-center justify-center px-4 py-20">
        <div class="bg-white rounded-2xl shadow-lg p-8 max-w-md w-full">
            <div class="text-center mb-6">
                <h1 class="text-2xl font-bold text-gray-800 mb-2">友達追加用QRコード</h1>
                <p class="text-sm text-gray-500">このQRコードをスキャンして友達に追加してもらえます</p>
            </div>
            
            <div class="flex justify-center mb-6">
                <div class="bg-white p-4 rounded-xl border-2 border-gray-200">
                    {!! QrCode::size(250)->generate($qrData) !!}
                </div>
            </div>
            
            <div class="text-center mb-6">
                <div class="inline-block bg-gray-100 rounded-lg px-4 py-2">
                    <p class="text-xs text-gray-500 mb-1">ユーザー名</p>
                    <p class="text-sm font-semibold text-gray-800">{{ $user->name }}</p>
                    @if($user->username)
                        <p class="text-xs text-gray-400 mt-1">{{ '@' . $user->username }}</p>
                    @endif
                </div>
            </div>
            
            <div class="flex gap-3">
                <a href="{{ route('dashboard') }}" class="flex-1 bg-gray-100 text-gray-700 text-center py-3 rounded-xl hover:bg-gray-200 transition font-medium">
                    戻る
                </a>
                <a href="{{ route('qr.scan') }}" class="flex-1 bg-indigo-600 text-white text-center py-3 rounded-xl hover:bg-indigo-700 transition font-medium">
                    QRコードをスキャン
                </a>
            </div>
        </div>
    </div>
</body>
</html>

