# Capacitaciones Teamms — Plugin LMS Empresarial para WordPress

Plataforma de capacitación empresarial privada para WordPress. Las empresas inscriben
colaboradores en cursos; los colaboradores avanzan por el contenido, rinden evaluaciones
y obtienen certificados con QR verificable. **Acceso solo por invitación. Sin registro público.**

- **Stack:** PHP 8 puro · WordPress 6.x · MySQL/MariaDB · Bootstrap 5 (CDN) · jQuery · WordPress AJAX
- **Sin** React, Node, Composer en servidor, REST API ni TypeScript.
- **Instalación:** se distribuye como ZIP instalable desde wp-admin → Plugins → Subir plugin.
- **Versión actual:** `0.12.x`

---

## ⚙️ Entorno de desarrollo (local)

| Dato | Valor |
|---|---|
| WordPress | `C:\xampp\htdocs\wordpress-7.0\wordpress` |
| Carpeta del plugin (se edita aquí) | `C:\Users\sagba\OneDrive\Escritorio\teamms_capacitaciones` |
| Enlace a WP | junction → `wp-content\plugins\teamms_capacitaciones` |
| URL admin | http://localhost/wordpress-7.0/wordpress/wp-admin |
| Sitio del LMS | `http://localhost/teamms` (WordPress dedicado, separado de Accons) |
| Base de datos | `teamms_capacitaciones` · user `root` · sin pass · `127.0.0.1:3307` |
| Prefijo de tablas | `wp_lms_` |
| Shortcode de la app | `[teamms_capacitaciones]` (en cualquier página → carga el LMS a pantalla completa) |

> **Nota de marca:** el producto se llama **Teamms** (texto visible). Algunos identificadores
> internos de código (carpeta `teamms_capacitaciones`, constantes `TEAMMS_LMS_*`, text-domain
> `teamms-lms`) conservan el nombre antiguo para no romper rutas/handles existentes.

---

## 📁 Estructura de carpetas

> Tres mundos separados: **backend** (lógica), **frontend** (lo que se ve), **diseno** (estilos).
> Cada pantalla del frontend vive en su propio archivo de vista (modular, sin HTML mezclado
> en los controladores).

```
teamms_capacitaciones/
├── teamms-capacitaciones.php          ← archivo principal (cabecera + arranque)
│
├── backend/                          LÓGICA / SERVIDOR (no se ve)
│   ├── core/                         ← activador (crea las 17 tablas), desactivador
│   ├── models/                       ← acceso a datos (1 clase por tabla):
│   │                                    company · course · module · content ·
│   │                                    progress · question · evaluation ·
│   │                                    certificate
│   ├── actions/                      ← guardar/borrar desde formularios:
│   │                                    company · course · module · content ·
│   │                                    progress · question · evaluation
│   └── admin/                        ← panel wp-admin (class-lms-admin.php)
│       └── views/                    ← dashboard.php (estadísticas reales)
│
├── frontend/                         👀 LO QUE VEN LOS USUARIOS
│   ├── class-lms-public.php          ← CONTROLADOR (rutea por rol y vista)
│   ├── templates/                    ← app-fullscreen.php (pantalla completa)
│   └── views/                        ← vistas separadas por área:
│       ├── layout/                   ← sidebar.php, topbar.php
│       ├── auth/                     ← login.php (selector de rol DEMO)
│       ├── admin/                    ← courses, course-form, structure,
│       │                               module-form, content-form,
│       │                               companies, panel, seccion
│       ├── company/                  ← panel.php (placeholder)
│       ├── student/                  ← courses, course, evaluation,
│       │                               evaluation-status, certificates,
│       │                               certificate, empty
│       └── public/                   ← verify.php (verificación pública del certificado)
│
└── diseno/                           🎨 DISEÑO
    └── css/                          ← lms-admin.css, lms-public.css
```

> Pendientes de crear cuando toquen: `backend/services/` (invitaciones, email),
> `backend/templates/` (pdf), `diseno/js/`, `diseno/images/`, `libs/` (dompdf, phpqrcode si
> se generan PDF/QR del lado servidor).

---

## ✅ Estado actual (qué funciona hoy)

> El acceso todavía es **DEMO**: se elige rol con el selector de login o con `?perfil=admin|empresa|estudiante`.
> Aún **no** hay login real ni invitaciones (es lo siguiente).

### 🧱 Base e infraestructura
- [x] Plugin instalable, activador con las **15 tablas** y desactivador seguro.
- [x] App a **pantalla completa** vía shortcode `[teamms_capacitaciones]`, con aislamiento de estilos/JS
      del tema y otros plugins (no se descuadra el layout).
- [x] Diseño responsive (sidebar + topbar; en móvil, menú tipo cajón).
- [x] Tres roles enrutados (admin / empresa / estudiante) — por ahora con selector demo.

### 🛠️ Administrador
- [x] **Dashboard** con estadísticas reales (empresas, cursos, módulos, inscripciones, certificados).
- [x] **Cursos:** crear, editar, publicar/borrador y borrar.
- [x] **Estructura del curso** en una sola pantalla (árbol Módulo → Contenido),
      con modales para crear/editar sin cambiar de página.
- [x] **Contenidos:** texto, video (enlace), **PDF/recurso por subida de archivo** a la
      Biblioteca de Medios (o enlace externo).
- [x] **Banco de preguntas** por módulo (varias opciones, una o varias correctas).
- [x] **Empresas:** crear, editar, activar/desactivar y borrar (lista + modal).

### 🎓 Estudiante
- [x] Catálogo de cursos publicados con % de avance.
- [x] **Visor del curso** (módulos → contenidos) con marcado de progreso.
- [x] **Evaluación** del módulo: 2 intentos máx., nota mínima 7/10, el intento 2 no repite
      preguntas del intento 1, resultado/estado del último intento.
- [x] **Certificados:** se emiten al **completar el curso** (aprobar todos sus módulos);
      vista imprimible con **QR** que enlaza a la verificación pública.

### 🌐 Público
- [x] Página de **verificación de certificado** por código (sin login), a la que apunta el QR.

---

## 🔜 Pendiente (roadmap)

- [x] **Login real + roles propios** (Admin LMS / Empresa / Estudiante). Login propio en el
      frontend; los usuarios externos no ven WordPress y se les bloquea `/wp-admin`.
- [x] **Invitación por link + inscripciones:** cada curso tiene un link de invitación; el
      estudiante crea su cuenta o inicia sesión al abrirlo y queda inscrito. Solo ve sus cursos.
- [ ] **Panel de empresa:** avance, notas y certificados de *sus* colaboradores; que la empresa
      también pueda invitar/gestionar a sus colaboradores.
- [ ] **Panel de empresa:** avance, notas y certificados de *sus* colaboradores.
- [ ] **Reportes** y exportación (Excel / PDF).
- [ ] **Insignias** automáticas (completar módulo / aprobar / completar curso).
- [ ] **Logo de empresa** y vínculo empresa ↔ colaboradores.
- [ ] (Opcional) Generar el **PDF del certificado** del lado servidor (DomPDF) en vez de imprimir desde el navegador.
- [ ] Empaquetado en ZIP y documentación de entrega para el cliente.

---

## 📜 Reglas de negocio inamovibles
1. La **cuenta** y el **acceso a un curso** son separados: el estudiante puede crear su cuenta, pero a un curso solo entra con su **link de invitación** (un link general por curso). Sin link no accede a ningún curso.
2. Autenticación = la de WordPress (sin sistema aparte).
3. Nota mínima para aprobar: **7 / 10**.
4. Máximo **2 intentos** por evaluación por módulo.
5. El intento 2 **nunca** repite preguntas del intento 1.
6. El certificado se emite al **completar el curso** (aprobar la evaluación de todos sus módulos). Las evaluaciones son por módulo; el certificado es uno por curso.
7. Todo certificado lleva QR con URL pública de validación.
8. Módulo bloqueado hasta que el anterior esté completado y aprobado.
9. El estudiante solo ve cursos en los que fue inscrito.
10. La empresa solo ve datos de sus propios colaboradores.
11. El plugin **no** modifica el tema ni otros plugins.
12. Todas las tablas usan el prefijo `wp_lms_`.

---

## 🗄️ Tablas de la base de datos (17)
`companies` · `user_company` · `invitations` · `courses` · `modules` · `contents` ·
`enrollments` · `content_progress` · `questions` · `question_options` ·
`evaluation_attempts` · `attempt_answers` · `certificates` · `badges` · `user_badges` ·
`activity_log` — todas con prefijo `wp_lms_`.
