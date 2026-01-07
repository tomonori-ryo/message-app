<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>QRコードスキャン</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>
</head>
<body class="bg-slate-50 font-sans text-gray-900 antialiased">
    <div class="min-h-screen flex items-center justify-center px-4 py-20">
        <div class="bg-white rounded-2xl shadow-lg p-8 max-w-md w-full">
            <div class="text-center mb-6">
                <h1 class="text-2xl font-bold text-gray-800 mb-2">QRコードをスキャン</h1>
                <p class="text-sm text-gray-500">友達のQRコードをスキャンして追加</p>
            </div>
            
            <div id="reader" class="mb-6 rounded-xl overflow-hidden border-2 border-gray-200"></div>
            
            <div id="result" class="hidden mb-6 p-4 bg-indigo-50 border border-indigo-200 rounded-xl">
                <p class="text-sm text-indigo-800 font-medium">スキャン中...</p>
            </div>
            
            <div class="flex gap-3">
                <a href="{{ route('dashboard') }}" class="flex-1 bg-gray-100 text-gray-700 text-center py-3 rounded-xl hover:bg-gray-200 transition font-medium">
                    戻る
                </a>
                <button onclick="startScan()" id="start-btn" class="flex-1 bg-indigo-600 text-white py-3 rounded-xl hover:bg-indigo-700 transition font-medium">
                    スキャン開始
                </button>
                <button onclick="stopScan()" id="stop-btn" class="hidden flex-1 bg-red-600 text-white py-3 rounded-xl hover:bg-red-700 transition font-medium">
                    停止
                </button>
            </div>
            
            <div class="mt-4 text-center">
                <a href="{{ route('qr.show') }}" class="text-sm text-indigo-600 hover:text-indigo-700">
                    自分のQRコードを表示
                </a>
            </div>
        </div>
    </div>

    <script>
        let html5QrcodeScanner = null;
        let isScanning = false;

        function startScan() {
            if (isScanning) return;
            
            const readerElement = document.getElementById('reader');
            const resultElement = document.getElementById('result');
            const startBtn = document.getElementById('start-btn');
            const stopBtn = document.getElementById('stop-btn');
            
            html5QrcodeScanner = new Html5Qrcode("reader");
            
            html5QrcodeScanner.start(
                { facingMode: "environment" },
                {
                    fps: 10,
                    qrbox: { width: 250, height: 250 }
                },
                (decodedText, decodedResult) => {
                    // QRコードが読み取られた
                    handleScanResult(decodedText);
                },
                (errorMessage) => {
                    // エラーは無視（継続的にスキャンするため）
                }
            ).then(() => {
                isScanning = true;
                startBtn.classList.add('hidden');
                stopBtn.classList.remove('hidden');
                resultElement.classList.remove('hidden');
                resultElement.innerHTML = '<p class="text-sm text-indigo-800 font-medium">QRコードをスキャン中...</p>';
            }).catch((err) => {
                console.error('Unable to start scanning:', err);
                alert('カメラへのアクセスに失敗しました。ブラウザの設定を確認してください。');
            });
        }

        function stopScan() {
            if (!isScanning || !html5QrcodeScanner) return;
            
            html5QrcodeScanner.stop().then(() => {
                isScanning = false;
                document.getElementById('start-btn').classList.remove('hidden');
                document.getElementById('stop-btn').classList.add('hidden');
                document.getElementById('result').classList.add('hidden');
            }).catch((err) => {
                console.error('Unable to stop scanning:', err);
            });
        }

        function handleScanResult(decodedText) {
            // URLからuser_idを抽出
            const match = decodedText.match(/\/friends\/add-by-qr\/(\d+)/);
            if (match) {
                const userId = match[1];
                // 友達追加ページにリダイレクト
                window.location.href = decodedText;
            } else {
                alert('無効なQRコードです');
                stopScan();
            }
        }

        // ページを離れるときにスキャンを停止
        window.addEventListener('beforeunload', () => {
            if (isScanning && html5QrcodeScanner) {
                html5QrcodeScanner.stop();
            }
        });
    </script>
</body>
</html>

