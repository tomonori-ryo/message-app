// Service Worker for Push Notifications
self.addEventListener('install', function(event) {
    self.skipWaiting();
});

self.addEventListener('activate', function(event) {
    event.waitUntil(self.clients.claim());
});

// Push通知を受信した時の処理
self.addEventListener('push', function(event) {
    let data = {};
    if (event.data) {
        data = event.data.json();
    }

    const title = data.title || '通知';
    const options = {
        body: data.body || '',
        icon: data.icon || '/favicon.ico',
        badge: data.icon || '/favicon.ico',
        tag: data.tag || 'notification',
        requireInteraction: false,
        silent: false,
        vibrate: [200, 100, 200],
        data: data.data || {}
    };

    event.waitUntil(
        self.registration.showNotification(title, options)
    );
});

// 通知をクリックした時の処理
self.addEventListener('notificationclick', function(event) {
    event.notification.close();

    event.waitUntil(
        clients.matchAll({ type: 'window', includeUncontrolled: true }).then(function(clientList) {
            // 既に開いているウィンドウがあればフォーカス
            for (let i = 0; i < clientList.length; i++) {
                const client = clientList[i];
                if (client.url === '/' && 'focus' in client) {
                    return client.focus();
                }
            }
            // ウィンドウが開いていなければ新しく開く
            if (clients.openWindow) {
                return clients.openWindow('/dashboard');
            }
        })
    );
});

