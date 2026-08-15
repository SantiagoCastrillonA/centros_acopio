# Centros de acopio

Catálogo público de necesidades de centros de acopio y albergues, para la emergencia por el
sismo del 10 de agosto de 2026 en Chocó, Valle del Cauca, Risaralda, Caldas y Quindío.

El problema que resuelve: hoy los coordinadores llevan el inventario de necesidades en cuadernos
y grupos de WhatsApp. Llega mercado que no se necesita, un acopio se satura mientras otro está
vacío, y el ciudadano que quiere ayudar no sabe qué llevar ni a dónde.

**Esta plataforma no recauda dinero.** No procesa pagos ni muestra cuentas bancarias.

## En producción

| | |
|---|---|
| Vista pública | http://18.216.6.154/ |
| Panel de coordinadores | http://18.216.6.154/admin |
| Servidor | AWS EC2 `t3.micro`, Ubuntu 24.04 LTS, región `us-east-2` |

## Stack

| Capa | Elección |
|---|---|
| Backend | Laravel 12 |
| Panel admin | Filament v5 |
| Base de datos | MySQL 8 |
| Vista pública | Blade con CSS embebido, sin build de JS |
| Mapa | Leaflet 1.9.4 auto-hospedado, teselas de OpenStreetMap |
| Despliegue | VPS + Nginx, sin Docker ni Redis |

Decisiones cerradas: **no** se introduce React, Vue, Inertia ni una API REST, y no se agregan
dependencias fuera de esta tabla sin justificarlo primero.

## Reglas del proyecto

1. **Cero dinero.** Si se enlaza a donación monetaria, es enlace externo a organizaciones
   oficiales, marcado como externo.
2. **Datos personales con cuidado.** Desde la Entrega 3 se guardan nombre y celular de
   voluntarios: dato personal bajo la Ley 1581 de 2012. Requiere aviso de privacidad,
   autorización explícita, y los celulares nunca se muestran en vistas públicas.
3. **Catálogo de insumos cerrado.** El coordinador escoge de una lista, nunca escribe texto
   libre. Si cada uno escribe "colchoneta" a su manera, los datos dejan de ser agregables.
4. **La vista pública abre en conexión mala.** Sin build de JS, sin webfonts externas, sin CDN.
   El mapa es mejora progresiva: si no carga, la página queda completa.
5. **Sin login para el público.** Cualquier fricción del lado del donante mata el uso.

## Modelo de datos

```
centros      id, nombre, slug, tipo(acopio|albergue), direccion, ciudad, departamento,
             latitud, longitud, contacto_nombre, contacto_telefono, horario, notas, activo

items        catalogo cerrado de insumos: id, nombre, unidad, categoria, activo

necesidades  id, centro_id, item_id, cantidad_requerida, cantidad_cubierta,
             prioridad(alta|media|baja), nota      unique(centro_id, item_id)
```

## Desarrollo local

Requiere PHP 8.3, Composer y MySQL 8.

```bash
composer install
cp .env.example .env
php artisan key:generate
```

Cree la base y ajuste `DB_DATABASE`, `DB_USERNAME` y `DB_PASSWORD` en `.env`. Luego:

```bash
php artisan migrate
php artisan db:seed
php artisan make:filament-user
php artisan serve
```

El seeder carga el catálogo de insumos. Usa `updateOrCreate`, así que repetirlo no duplica nada.

## Despliegue

Guía completa en [deploy/LEEME.md](deploy/LEEME.md): creación de la máquina en AWS EC2 o Google
Cloud, preparación del servidor y publicación con Nginx.

Para desplegar cambios en un servidor ya montado:

```bash
cd /var/www/centros_acopio && ./deploy/desplegar.sh master
```

No es automático: hay que ejecutarlo después de subir cambios a `master`.

## Convenciones

- **Idioma:** todo en español, salvo las columnas estándar de Laravel.
- **Sin acentos ni ñ** en identificadores de código y datos semilla. En la interfaz sí van.
- **Modelos:** `casts()` como método, `$fillable` explícito, nunca `$guarded = []`.
- **Textos de interfaz:** voz activa y frase concreta. "Publicar necesidad", no "Enviar".
- **Estados vacíos:** una invitación a actuar, no un mensaje de error.
- **Migraciones:** una tabla por archivo, con `down()` real.

## Entregas

| | Alcance | Estado |
|---|---|---|
| 1 | Catálogo público de necesidades y panel de coordinadores | Desplegada |
| 2 | Actualización rápida en móvil (`/rapido/{centro}`, Livewire) | Pendiente |
| 3 | Voluntarios: turnos e inscripciones | Pendiente |
| 4 | Filtros por ciudad y orden por cercanía | Pendiente |
| 5 | PWA con caché de lectura y exportación a Excel | Pendiente |

Antes de dar por cerrada la Entrega 1 falta lo más importante: hablar con un coordinador real de
un punto de acopio y preguntarle cómo lleva la cuenta hoy. Ninguna cantidad de código reemplaza
esa validación.
