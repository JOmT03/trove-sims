const CACHE = 'trove-v1';
self.addEventListener('install', function (e) { self.skipWaiting(); });
self.addEventListener('activate', function (e) { e.waitUntil(self.clients.claim()); });
self.addEventListener('fetch', function (e) {
  if (e.request.method !== 'GET') return;
  e.respondWith(
    fetch(e.request).then(function (res) {
      try { var copy = res.clone(); caches.open(CACHE).then(function (c) { c.put(e.request, copy); }); } catch (x) {}
      return res;
    }).catch(function () { return caches.match(e.request); })
  );
});