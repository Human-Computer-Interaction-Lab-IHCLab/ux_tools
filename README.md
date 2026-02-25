# UX Tools MVP (Card Sorting + Tree Testing)

Aplicación web en **PHP 8 + MySQL + JS vanilla + Tailwind CDN**, pensada para DreamHost compartido (LAMP).

## Estructura

- `public/index.php`: front controller y router.
- `app/config.php`, `app/db.php`, `app/auth.php`, `app/helpers.php`.
- `app/controllers/*`: Auth, Teacher, Team, Participant.
- `app/views/*`: vistas PHP.
- `public/assets/app.js`: UI drag&drop y tree testing.
- `db/schema.sql`: esquema completo.
- `db/seed.sql`: profesor inicial.

## Instalación (DreamHost)

1. Crear DB MySQL y usuario desde panel DreamHost.
2. Subir repositorio al hosting.
3. Configurar variables de entorno (o editar `app/config.php`):
   - `DB_HOST`, `DB_NAME`, `DB_USER`, `DB_PASS`, `BASE_URL`.

### `BASE_URL` en local vs servidor compartido

En `app/config.php` el fallback actual es:

```php
'base_url' => getenv('BASE_URL') ?: '/ux-tools',
```

Guía rápida:

- **Local con URL raíz** (`http://localhost:8000`): usar `BASE_URL=` (vacío) o `'base_url' => ''`.
- **Local en subcarpeta** (`http://localhost/ux-tools/public`): usar `BASE_URL=/ux-tools/public`.
- **Servidor con Document Root apuntando a `/public`** (recomendado): usar `BASE_URL=` (vacío).
- **Servidor sin cambiar Document Root y fallback con `/.htaccess`**: usar `BASE_URL=/ux-tools`.

Regla práctica: `BASE_URL` debe coincidir con el prefijo visible antes de tus rutas (`/login`, `/teacher`, `/p/{token}`) en el navegador.
4. Importar SQL:
   - `mysql -uUSER -p DB_NAME < db/schema.sql`
   - `mysql -uUSER -p DB_NAME < db/seed.sql`
5. Acceder a `/login` con:
   - `admin@local.test` / `Admin123!`

---

## ¿Cómo desplegarlo en servidor compartido? (Document Root y `.htaccess`)

### Opción A (recomendada): apuntar el Document Root a `/public`

En DreamHost Panel:

1. **Websites → Manage Websites → Edit** tu dominio.
2. En **Web Directory**, apunta al directorio `.../ux_tools/public`.
3. Guarda cambios.

Con eso, Apache servirá directamente `public/index.php` y los assets de `public/assets/*`.

### Opción B (si no puedes cambiar Document Root): usar `.htaccess` en la raíz

Este repo incluye:

- `/.htaccess` (raíz): reescribe todo hacia `/public`.
- `/public/.htaccess`: reescribe rutas limpias hacia `public/index.php`.

Así puedes dejar el dominio apuntando al directorio raíz del proyecto y aun así enrutar correctamente.

> Importante: si puedes elegir, usa la **Opción A** porque reduce exposición accidental de archivos fuera de `/public`.

## Verificación rápida de Apache mod_rewrite

Si las rutas limpias no funcionan (`/login`, `/teacher`, etc.):

- Confirma que `.htaccess` está permitido en tu hosting (DreamHost normalmente sí).
- Revisa que no haya un `.htaccess` de nivel superior sobrescribiendo reglas.
- Prueba temporalmente abrir `/index.php` para confirmar que PHP sí ejecuta.

## Funcionalidades clave

- Activación de estudiantes por token (`/activate/{token}`) y QR fallback.
- Profesor: CRUD grupos/equipos, importación CSV, plantillas, asignación masiva por equipo.
- Equipos: configuran y publican instancia, comparten edición, ven resultados y export CSV.
- Participantes externos: `/p/{token}` sin login.
- Card Sorting: open/closed/hybrid, validación server-side de tarjetas obligatorias.
- Tree Testing: captura selección, path_text y tiempo por tarea.
- Seguridad mínima: `password_hash`, CSRF en panel interno, prepared statements, escaping.

## Exportes CSV

- Card Sorting asignaciones: `card_assignments.csv`
- Card Sorting similitud: `card_similarity.csv`
- Tree Testing respuestas: `tree_testing.csv`
