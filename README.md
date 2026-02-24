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
3. Configurar Document Root a `/public`.
4. Crear variables de entorno (o editar `app/config.php`):
   - `DB_HOST`, `DB_NAME`, `DB_USER`, `DB_PASS`, `BASE_URL`.
5. Importar SQL:
   - `mysql -uUSER -p DB_NAME < db/schema.sql`
   - `mysql -uUSER -p DB_NAME < db/seed.sql`
6. Acceder a `/login` con:
   - `admin@local.test` / `Admin123!`

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

