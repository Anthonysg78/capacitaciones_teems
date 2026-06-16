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
├── teems-capacitaciones.php      ← archivo principal (arranque + carga de clases)
│
├── backend/                      🧠 LÓGICA / SERVIDOR (no se ve)
│   ├── core/                     ← class-lms-activator.php, class-lms-deactivator.php
│   ├── admin/                    ← class-lms-admin.php (+ views/dashboard.php)
│   ├── models/                   ← acceso a datos (uno por entidad):
│   │   ├── class-lms-course.php       (cursos)
│   │   ├── class-lms-module.php       (módulos)
│   │   ├── class-lms-subtopic.php     (subtemas)
│   │   ├── class-lms-content.php      (contenidos: texto/video/pdf/recurso)
│   │   ├── class-lms-progress.php     (avance del estudiante + %)
│   │   ├── class-lms-question.php     (preguntas + opciones)
│   │   └── class-lms-evaluation.php   (intentos, notas, reglas)
│   └── actions/                  ← guardar/borrar (template_redirect + nonce):
│       ├── class-lms-course-actions.php
│       ├── class-lms-module-actions.php
│       ├── class-lms-subtopic-actions.php
│       ├── class-lms-content-actions.php
│       ├── class-lms-progress-actions.php   (marcar contenido completado)
│       ├── class-lms-question-actions.php
│       └── class-lms-evaluation-actions.php (calificar examen)
│
├── frontend/                     👀 LO QUE VEN LOS USUARIOS
│   ├── class-lms-public.php      ← CONTROLADOR (rutea por rol/vista + aísla la app)
│   ├── templates/                ← app-fullscreen.php (pantalla completa)
│   └── views/
│       ├── layout/               ← sidebar.php, topbar.php
│       ├── auth/                 ← login.php (selector de rol demo)
│       ├── admin/                ← courses.php, course-form.php,
│       │                            structure.php  ← EDITOR EN ÁRBOL (módulos→
│       │                            subtemas→contenidos + evaluación, todo con modales),
│       │                            panel.php, seccion.php
│       ├── company/              ← panel.php
│       └── student/              ← courses.php (lista real), course.php (visor),
│                                    evaluation.php (examen), evaluation-status.php
│                                    (resultado + retroalimentación), empty.php
│
└── diseno/                       🎨 DISEÑO
    └── css/                      ← lms-admin.css, lms-public.css
```

> Pendientes de crear cuando toquen: `backend/services/`,
> `backend/templates/` (pdf, emails), `frontend/views/verify/`, `diseno/js/`,
> `diseno/images/`, `libs/` (dompdf, phpqrcode, phpspreadsheet).

---

## ✅ Progreso por semanas

> Marca `[x]` cada vez que terminemos algo.
>
> **Estado al día (v0.10.x):** NO se siguió el orden estricto de semanas. Se priorizó el
> flujo de aprendizaje completo: **el admin construye cursos → el estudiante los toma,
> avanza y rinde evaluaciones**. Hechas: Semana 1, 4, 5, 6-7 (parcial) y 8-9 (parcial).
> Pendientes clave: **Semana 2 (login real por invitación)** y **Semana 3 (empresas/usuarios)**.
>
> **Decisiones de arquitectura aplicadas:**
> - Todo el panel (incluido el admin) vive en el **frontend** con `[teems_lms]`; el rol se
>   elige con un **selector demo** (`?perfil=`) hasta que llegue el login real.
> - La **evaluación es parte del módulo**: vive dentro del editor en árbol, no en página aparte.
> - La app es **autosuficiente**: en su página se suprimen CSS/JS de otros temas/plugins.

### Semana 1 — Base del plugin ✅ COMPLETADA
- [x] Entorno local (XAMPP + WordPress)
- [x] Estructura de carpetas
- [x] Archivo principal `teems-capacitaciones.php`
- [x] Activador con las 17 tablas (`class-lms-activator.php`)
- [x] Desactivador seguro (`class-lms-deactivator.php`)
- [x] Plugin activado y 17 tablas verificadas en la BD

### Semana 2 — Roles, invitaciones, activación y login 🔜 PENDIENTE
- [ ] Roles custom: Admin LMS, Empresa, Estudiante
- [ ] Servicio de invitaciones (crear usuario + token + email)
- [ ] Página de activar cuenta (crear contraseña)
- [ ] Login del LMS y redirección por rol (reemplaza el selector demo `?perfil=`)

### Semana 3 — Admin: empresas y usuarios 🔜 PENDIENTE
- [ ] CRUD de empresas (listar, crear, editar)
- [ ] CRUD de usuarios + envío de invitación

### Semana 4 — Admin: cursos, módulos y subtemas ✅ COMPLETADA
- [x] CRUD de cursos (lista en tarjetas + formulario)
- [x] Módulos y subtemas
- [x] Constructor visual del curso: **editor en árbol** (`structure.php`) colapsable,
      con modales para crear/editar sin salir de la pantalla

### Semana 5 — Contenidos por subtema ✅ (parcial)
- [x] Contenidos: texto, video, PDF, recurso (modal con tipo dinámico)
- [ ] Subida de archivos a uploads (por ahora se pega la **URL** del PDF/video)

### Semana 6 — Inscripciones y dashboard del estudiante ✅ (parcial)
- [x] Dashboard del estudiante con **% de avance real**
- [ ] Inscribir estudiantes a cursos (por ahora el estudiante ve **todos** los publicados)

### Semana 7 — Visor de contenido ✅ (parcial)
- [x] Ver texto en línea; video/PDF/recurso se **abren en pestaña nueva**
- [x] Marcar contenido como completado (mueve la barra de %)
- [ ] Video **embebido** dentro de la página

### Semana 8 — Banco de preguntas y lógica de evaluación ✅ (parcial)
- [x] CRUD del banco de preguntas, **dentro de cada módulo** (bloque "Evaluación del módulo")
- [x] Opciones **dinámicas** (empieza en 3, se agregan/quitan) + **una o varias correctas** (switch)
- [x] Selección aleatoria de preguntas y opciones (barajado)
- [ ] Anti-repetición de preguntas entre el intento 1 y el 2

### Semana 9 — Evaluación del estudiante ✅ (parcial)
- [x] Pantalla de evaluación (radio si 1 correcta, checkbox si varias)
- [x] Calificación: nota /10, **2 intentos**, mínimo **7** para aprobar
- [x] Pantalla de resultados con **retroalimentación** (aciertos/errores + correcta)
- [ ] Autosave cada 30s (jQuery AJAX)

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
