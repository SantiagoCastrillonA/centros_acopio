/*
 * Service worker de la vista publica.
 *
 * Objetivo unico: que un donante pueda abrir la pagina y ver que necesita
 * un centro aunque en ese momento no tenga senal. No pretende ser una app
 * offline completa.
 *
 * Reglas duras:
 * - Solo GET y solo del mismo origen.
 * - El panel, la pantalla rapida y Livewire NUNCA se cachean: son datos
 *   que cambian y pantallas autenticadas. Servir una version vieja ahi
 *   seria peor que no responder.
 * - Primero red, y si falla, cache. Al reves un coordinador actualizaria
 *   el inventario y el donante seguiria viendo el numero viejo.
 *
 * Al cambiar CACHE se descarta todo lo guardado antes.
 */

// v2: cambio el color de la marca, y con el los iconos y el manifiesto.
const CACHE = 'centros-acopio-v2';

const PRECARGA = [
    '/',
    '/sin-conexion',
    '/iconos/icono-192.png',
];

// Rutas que nunca pasan por el cache.
const EXCLUIDAS = [
    '/admin',
    '/rapido',
    '/livewire',
    '/turno',
];

self.addEventListener('install', (evento) => {
    evento.waitUntil(
        caches.open(CACHE)
            // addAll falla entera si un recurso falla; se agregan de a uno
            // para que un 404 no deje al service worker sin instalar.
            .then((cache) => Promise.allSettled(PRECARGA.map((ruta) => cache.add(ruta))))
            .then(() => self.skipWaiting())
    );
});

self.addEventListener('activate', (evento) => {
    evento.waitUntil(
        caches.keys()
            .then((claves) => Promise.all(
                claves.filter((clave) => clave !== CACHE).map((clave) => caches.delete(clave))
            ))
            .then(() => self.clients.claim())
    );
});

self.addEventListener('fetch', (evento) => {
    const peticion = evento.request;

    if (peticion.method !== 'GET') {
        return;
    }

    const url = new URL(peticion.url);

    if (url.origin !== self.location.origin) {
        return;
    }

    if (EXCLUIDAS.some((ruta) => url.pathname === ruta || url.pathname.startsWith(ruta + '/'))) {
        return;
    }

    evento.respondWith(
        fetch(peticion)
            .then((respuesta) => {
                // Solo se guarda lo que sirvio bien. Guardar un 404 o un 500
                // significa mostrarselo al usuario cuando se quede sin senal.
                if (respuesta && respuesta.status === 200 && respuesta.type === 'basic') {
                    const copia = respuesta.clone();
                    caches.open(CACHE).then((cache) => cache.put(peticion, copia));
                }

                return respuesta;
            })
            .catch(() => caches.match(peticion).then((guardada) => {
                if (guardada) {
                    return guardada;
                }

                if (peticion.mode === 'navigate') {
                    return caches.match('/sin-conexion');
                }

                return Response.error();
            }))
    );
});
