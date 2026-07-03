const CACHE = "olab004-local-v1";
const ASSETS = [
  "./",
  "./index.html",
  "./style.css",
  "./app.js",
  "./manifest.webmanifest",
  "./assets/olab-loyalty-logo.svg",
  "./assets/diapo-gouts-exception.jpg",
  "./assets/fast-delivery.png",
  "https://cdn.jsdelivr.net/npm/chart.js",
  "https://cdn.jsdelivr.net/npm/html5-qrcode/html5-qrcode.min.js",
  "https://cdn.jsdelivr.net/npm/qrcodejs@1.0.0/qrcode.min.js",
  "https://unpkg.com/lucide@latest"
];

self.addEventListener("install", (event) => {
  event.waitUntil(caches.open(CACHE).then((cache) => cache.addAll(ASSETS)));
});

self.addEventListener("activate", (event) => {
  event.waitUntil(caches.keys().then((keys) => Promise.all(keys.filter((key) => key !== CACHE).map((key) => caches.delete(key)))));
});

self.addEventListener("fetch", (event) => {
  event.respondWith(caches.match(event.request).then((cached) => cached || fetch(event.request)));
});
