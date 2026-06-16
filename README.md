# Capacitaciones Teems — Plugin LMS Empresarial para WordPress

Plataforma de capacitación empresarial privada para WordPress. Las empresas inscriben
colaboradores en cursos; los colaboradores avanzan por el contenido, rinden evaluaciones
y obtienen certificados con QR verificable. **Acceso solo por invitación. Sin registro público.**

- **Stack:** PHP 8 puro · WordPress 6.x · MySQL/MariaDB · Bootstrap 5 (CDN) · jQuery · WordPress AJAX
- **Sin** React, Node, Composer en servidor, REST API ni TypeScript.
- **Instalación:** se distribuye como ZIP instalable desde wp-admin → Plugins → Subir plugin.

---

## ⚙️ Entorno de desarrollo (local)

| Dato | Valor |
|---|---|
| WordPress | `C:\xampp\htdocs\wordpress-7.0\wordpress` |
| Carpeta del plugin (se edita aquí) | `C:\Users\sagba\OneDrive\Escritorio\teems_capacitaciones` |
| Enlace a WP | junction → `wp-content\plugins\teems_capacitaciones` |
| URL admin | http://localhost/wordpress-7.0/wordpress/wp-admin |
| Base de datos | `wordpress` · user `root` · sin pass · `127.0.0.1:3307` |
| Prefijo de tablas | `wp_lms_` |

---

## 📁 Estructura de carpetas

> Tres mundos separados: **backend** (lógica), **frontend** (lo que se ve), **diseno** (estilos).
> **Convención:** las carpetas se crean **cuando se necesitan** (no se dejan vacías por adelantado).
> Sin `index.php` de relleno: solo archivos de código real. (La protección de listado de
> directorios, si hace falta, se añadirá al empaquetar para producción.)

Estructura **actual** (crece a medida que avanzamos):

```
teems_capacitaciones/
├── teems-capacitaciones.php      ← archivo principal (cabecera + arranque)
│
├── backend/                      🧠 LÓGICA / SERVIDOR (no se ve)
│   ├── core/                     ← activador, desactivador
│   └── admin/                    ← panel wp-admin (class-lms-admin.php)
│       └── views/                ← dashboard.php
│
├── frontend/                     👀 LO QUE VEN LOS USUARIOS
│   ├── class-lms-public.php      ← CONTROLADOR (rutea por rol y vista)
│   ├── templates/                ← app-fullscreen.php (pantalla completa)
│   └── views/                    ← vistas separadas:
│       ├── layout/               ← sidebar.php, topbar.php
│       ├── auth/                 ← login.php (selector de rol)
│       ├── admin/                ← panel.php, seccion.php
│       ├── company/              ← panel.php
│       └── student/              ← courses.php, empty.php
│
└── diseno/                       🎨 DISEÑO
    └── css/                      ← lms-admin.css, lms-public.css
```

> Pendientes de crear cuando toquen: `backend/models/`, `backend/services/`,
> `backend/templates/` (pdf, emails), `frontend/views/verify/`, `diseno/js/`,
> `diseno/images/`, `libs/` (dompdf, phpqrcode, phpspreadsheet).

---

## ✅ Progreso por semanas

> Marca `[x]` cada vez que terminemos algo.

### Semana 1 — Base del plugin ✅ COMPLETADA
- [x] Entorno local (XAMPP + WordPress)
- [x] Estructura de carpetas + `index.php` de seguridad
- [x] Archivo principal `teems-capacitaciones.php`
- [x] Activador con las 17 tablas (`class-lms-activator.php`)
- [x] Desactivador seguro (`class-lms-deactivator.php`)
- [x] Plugin activado y 17 tablas verificadas en la BD

### Semana 2 — Roles, invitaciones, activación y login 🔜 EN CURSO
- [ ] Roles custom: Admin LMS, Empresa, Estudiante (`class-lms-roles.php`)
- [ ] Loader que registra todos los hooks (`class-lms-loader.php`)
- [ ] Servicio de invitaciones (crear usuario + token + email)
- [ ] Página `/lms-activar-cuenta/` (crear contraseña y activar)
- [ ] Login del LMS y redirección por rol

### Semana 3 — Admin: empresas y usuarios
- [ ] Menú de wp-admin "LMS Empresarial"
- [ ] CRUD de empresas (listar, crear, editar)
- [ ] CRUD de usuarios + envío de invitación

### Semana 4 — Admin: cursos, módulos y subtemas
- [ ] CRUD de cursos
- [ ] Módulos y subtemas
- [ ] Constructor visual del curso

### Semana 5 — Contenidos por subtema
- [ ] Contenidos: texto, video, PDF, recurso
- [ ] Subida de archivos a uploads

### Semana 6 — Inscripciones y dashboard del estudiante
- [ ] Inscribir estudiantes a cursos
- [ ] Dashboard del estudiante con % de avance

### Semana 7 — Visor de contenido
- [ ] Ver texto, video embebido, PDF, recursos
- [ ] Marcar contenido como completado

### Semana 8 — Banco de preguntas y lógica de evaluación
- [ ] CRUD del banco de preguntas (4 opciones, 1 correcta)
- [ ] Selección aleatoria + anti-repetición intento 2

### Semana 9 — Evaluación del estudiante
- [ ] Pantalla de evaluación
- [ ] Autosave cada 30s (jQuery AJAX)
- [ ] Pantalla de resultados con retroalimentación

### Semana 10 — Certificados PDF + QR
- [ ] Generar PDF con DomPDF
- [ ] Código QR + UUID
- [ ] Página pública `/lms-verificar/` sin login

### Semana 11 — Insignias automáticas
- [ ] Por completar módulo / aprobar evaluación / completar curso

### Semana 12 — Dashboard de empresa + reportes
- [ ] Dashboard de empresa y detalle de colaborador
- [ ] Exportar Excel (PhpSpreadsheet) y PDF

### Semana 13 — Dashboard admin + log
- [ ] Métricas globales
- [ ] Log de actividad

### Semana 14 — QA de flujos críticos
- [ ] Probar todos los flujos y corregir bugs

### Semana 15 — Seguridad y compatibilidad
- [ ] Repaso de seguridad y compatibilidad con el tema

### Semana 16 — Empaquetado y entrega
- [ ] Comprimir en ZIP
- [ ] Instalar en producción + documentación para el cliente

---

## 📜 Reglas de negocio inamovibles
1. Sin invitación no hay acceso. Registro público **nunca**.
2. Autenticación = la de WordPress (sin sistema aparte).
3. Nota mínima para aprobar: **7 / 10**.
4. Máximo **2 intentos** por evaluación por módulo.
5. El intento 2 **nunca** repite preguntas del intento 1.
6. Certificado solo si módulo 100% completo **Y** evaluación aprobada.
7. Todo certificado lleva QR con URL pública de validación.
8. Módulo bloqueado hasta que el anterior esté completado y aprobado.
9. El estudiante solo ve cursos en los que fue inscrito.
10. La empresa solo ve datos de sus propios colaboradores.
11. El plugin **no** modifica el tema ni otros plugins.
12. Todas las tablas usan el prefijo `wp_lms_`.

---

## 🗄️ Tablas de la base de datos (17)
`companies` · `user_company` · `invitations` · `courses` · `modules` · `subtopics` ·
`contents` · `enrollments` · `content_progress` · `questions` · `question_options` ·
`evaluation_attempts` · `attempt_answers` · `certificates` · `badges` · `user_badges` ·
`activity_log` — todas con prefijo `wp_lms_`.
