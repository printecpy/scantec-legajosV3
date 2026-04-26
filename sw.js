/**
 * Scantec DMS - Service Worker
 * Versión: 1.0.0
 * Estrategia:
 *   - Cache First para recursos estáticos (JS, CSS, imágenes, fonts)
 *   - Network First para páginas PHP (siempre fresca, fallback offline)
 */

const CACHE_VERSION = 'scantec-v3';
const STATIC_CACHE  = `${CACHE_VERSION}-static`;
const PAGES_CACHE   = `${CACHE_VERSION}-pages`;

// Recursos estáticos que se pre-cachean en la instalación
const STATIC_ASSETS = [
    'Assets/css/estilo.css',
    'Assets/css/select2.min.css',
    'Assets/js/jquery.min.js',
    'Assets/js/bootstrap.bundle.min.js',
    'Assets/js/select2.min.js',
    'Assets/js/jquery.dataTables.min.js',
    'Assets/js/dataTables.bootstrap4.min.js',
    'Assets/js/Funciones.js',
    'Assets/js/tables.js',
    'Assets/img/pwa-192.png',
    'Assets/img/pwa-512.png',
    'Assets/img/icoScantec-copia2.ico',
];

// Páginas de fallback offline
const OFFLINE_PAGE  = 'offline.html';

// ─── INSTALL ──────────────────────────────────────────────────────────────────
self.addEventListener('install', event => {
    event.waitUntil(
        caches.open(STATIC_CACHE)
            .then(cache => cache.addAll(STATIC_ASSETS))
            .then(() => self.skipWaiting())
            .catch(err => {
                console.warn('[SW] Error pre-cacheando estáticos:', err);
                return self.skipWaiting();
            })
    );
});

// ─── ACTIVATE ─────────────────────────────────────────────────────────────────
self.addEventListener('activate', event => {
    event.waitUntil(
        caches.keys().then(keys =>
            Promise.all(
                keys
                    .filter(key => key.startsWith('scantec-') && key !== STATIC_CACHE && key !== PAGES_CACHE)
                    .map(key => caches.delete(key))
            )
        ).then(() => self.clients.claim())
    );
});

// ─── FETCH ────────────────────────────────────────────────────────────────────
self.addEventListener('fetch', event => {
    const { request } = event;
    const url = new URL(request.url);

    // Solo interceptamos peticiones del mismo origen
    if (url.origin !== self.location.origin) return;

    // Ignorar peticiones que no son GET
    if (request.method !== 'GET') return;

    // Ignorar rutas de API
    if (url.pathname.includes('/api/')) return;

    const isStatic = /\.(css|js|png|jpg|jpeg|gif|ico|svg|woff|woff2|ttf|eot|webp)(\?.*)?$/i.test(url.pathname);

    if (isStatic) {
        // Cache First para estáticos
        event.respondWith(cacheFirst(request));
    } else {
        // Network First para páginas PHP
        event.respondWith(networkFirst(request));
    }
});

// ─── ESTRATEGIAS ──────────────────────────────────────────────────────────────

async function cacheFirst(request) {
    const cached = await caches.match(request);
    if (cached) return cached;

    try {
        const response = await fetch(request);
        if (response && response.status === 200) {
            const cache = await caches.open(STATIC_CACHE);
            cache.put(request, response.clone());
        }
        return response;
    } catch (err) {
        // Si es imagen, retorna respuesta vacía
        if (/\.(png|jpg|jpeg|gif|ico|svg|webp)$/i.test(new URL(request.url).pathname)) {
            return new Response('', { status: 404 });
        }
        return new Response('', { status: 408 });
    }
}

async function networkFirst(request) {
    try {
        const response = await fetch(request);
        if (response && response.status === 200) {
            const cache = await caches.open(PAGES_CACHE);
            cache.put(request, response.clone());
        }
        return response;
    } catch (err) {
        // Intentar servir desde caché si hay falla de red
        const cached = await caches.match(request);
        if (cached) return cached;

        // Página offline de fallback
        const offlinePage = await caches.match(OFFLINE_PAGE);
        if (offlinePage) return offlinePage;

        return new Response(
            `<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Sin conexión - Scantec DMS</title>
<style>
  * { margin:0; padding:0; box-sizing:border-box; }
  body { font-family: 'Segoe UI', sans-serif; background: #182541; color: #fff; display:flex; align-items:center; justify-content:center; min-height:100vh; text-align:center; padding:2rem; }
  .card { background: rgba(255,255,255,0.08); border-radius:1.5rem; padding:3rem 2rem; max-width:480px; border:1px solid rgba(255,255,255,0.15); backdrop-filter:blur(10px); }
  .icon { font-size:4rem; margin-bottom:1.5rem; }
  h1 { font-size:1.75rem; font-weight:800; margin-bottom:0.75rem; }
  p { color:rgba(255,255,255,0.7); font-size:0.95rem; line-height:1.6; }
  .btn { display:inline-block; margin-top:2rem; padding:0.75rem 2rem; background:#e53e3e; color:#fff; border-radius:0.75rem; font-weight:700; text-decoration:none; cursor:pointer; border:none; font-size:1rem; }
  .btn:hover { background:#c53030; }
</style>
</head>
<body>
  <div class="card">
    <div class="icon">📡</div>
    <h1>Sin conexión</h1>
    <p>No hay conexión a internet disponible.<br>Verifique su red e intente nuevamente.</p>
    <button class="btn" onclick="window.location.reload()">Reintentar</button>
  </div>
</body>
</html>`,
            {
                status: 200,
                headers: { 'Content-Type': 'text/html; charset=utf-8' }
            }
        );
    }
}

// ─── MENSAJES DESDE LA PÁGINA ─────────────────────────────────────────────────
self.addEventListener('message', event => {
    if (event.data && event.data.type === 'SKIP_WAITING') {
        self.skipWaiting();
    }
    if (event.data && event.data.type === 'CLEAR_CACHE') {
        caches.keys().then(keys => Promise.all(keys.map(k => caches.delete(k))));
    }
});
