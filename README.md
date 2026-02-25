# UX Tools from PECESAMA at IHCLAB

## Plataforma Académica para Card Sorting y Tree Testing

![Organization](https://img.shields.io/badge/IHCLab-Universidad%20de%20Colima-darkgreen)

------------------------------------------------------------------------

🇬🇧 English version available here → [README.md](README_EN.md)

------------------------------------------------------------------------

## 🎓 Descripción

UX Tools MVP es una plataforma académica para la enseñanza práctica de:

-   Card Sorting (Open / Closed / Hybrid)\
-   Tree Testing (tipo Treejack)

Diseñada para ejecutarse en hosting compartido (LAMP):

-   PHP 8.x (framework ligero)
-   MySQL
-   JavaScript moderno (vanilla)
-   Tailwind CSS (CDN)

------------------------------------------------------------------------

## 🎯 Propósito Académico

El sistema permite:

-   Simular procesos reales de investigación UX
-   Organizar trabajo colaborativo por equipos
-   Ejecutar estudios con usuarios externos
-   Analizar métricas de consenso y navegación
-   Exportar resultados para análisis estadístico

------------------------------------------------------------------------

## 🧠 Contexto de Investigación

Este sistema fue desarrollado como herramienta docente y entorno
experimental para:

-   Estudiar modelos mentales y categorización
-   Evaluar encontrabilidad y arquitectura
-   Analizar consenso y co-ocurrencia
-   Comparar resultados inter-equipos

## 📊 Métricas Implementadas

### Card Sorting

* Tasa de finalización
* Tiempo promedio
* Categoría dominante por tarjeta
* % de consenso
* Matriz de similitud (co-ocurrencia)
* Export:

  * `card_assignments.csv`
  * `card_similarity.csv`

### Tree Testing

* Success rate por tarea
* Tiempo medio / mediana
* Rutas más frecuentes
* Export:

  * `tree_testing.csv`

Cita sugerida:

Santana-Mancilla, P. C. (2026). *UX Tools MVP: A lightweight academic
platform for teaching Card Sorting and Tree Testing*. Facultad de Telemática,
Universidad de Colima. https://github.com/Human-Computer-Interaction-Lab-IHCLab/ux_tools

------------------------------------------------------------------------

## ⚠️ Disclaimer -- Vibe Coding + IA Asistida

Este proyecto fue desarrollado bajo un enfoque de **Vibe Coding con depuración asistida por IA y supervisión humana**.

Herramientas utilizadas:

* 🧠 **ChatGPT 5.2 Thinking**
  Para estructuración de requerimientos y generación del prompt maestro para Codex.

* 🤖 **CODEX (Febrero 2026 – Vibe Coding)**
  Para generación estructural del repositorio y código base.

* 🔎 **Gemini 3 Thinking**
  Para depuración, identificación de bugs y refinamiento técnico.

El diseño arquitectónico, decisiones pedagógicas y validación funcional fueron realizados manualmente por el autor.

------------------------------------------------------------------------

## 🚀 Instalación

### Crear Base de Datos

Desde el panel:

* Crear DB MySQL
* Crear usuario
* Asignar privilegios

### Subir el repositorio

Subir al hosting por FTP/SFTP.

### Configurar `app/config.php`

Variables necesarias:

```
DB_HOST
DB_NAME
DB_USER
DB_PASS
BASE_URL
```

### Configuración de BASE_URL

| Entorno                                          | Valor recomendado  |
| ------------------------------------------------ | ------------------ |
| Local raíz (`localhost:8000`)                    | vacío              |
| Local subcarpeta                                 | `/ux-tools/public` |
| Servidor con Document Root apuntando a `/public` | vacío              |
| Servidor sin cambiar Document Root               | `/ux-tools`        |

Regla práctica:
BASE_URL debe coincidir con el prefijo visible antes de `/login`, `/teacher`, `/p/{token}`.

---

### Importar esquema

```
mysql -uUSER -p DB_NAME < db/schema.sql
mysql -uUSER -p DB_NAME < db/seed.sql
```

### Acceso inicial

```
admin@local.test
Admin123!
```

---

## 🌐 Despliegue y Document Root

### ✅ Opción recomendada

Apuntar Document Root a `/public`.

Reduce exposición accidental de archivos.

### ⚙ Opción alternativa

Usar `.htaccess` en raíz para redirigir a `/public`.

---

## 🧩 Funcionalidades Principales

### Profesor

* CRUD grupos y equipos
* Importación masiva de estudiantes
* Activación por token + QR fallback
* Creación de plantillas
* Asignación automática a equipos
* Resultados globales comparativos

### Equipos

* Configuración de instancia
* Publicación con token único
* Exportaciones CSV
* Visualización de métricas

### Participantes externos

* Acceso sin login (`/p/{token}`)
* Alias opcional
* Validaciones server-side