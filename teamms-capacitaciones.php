<?php
/**
 * Plugin Name:       Capacitaciones Teamms
 * Plugin URI:        https://teamms.local
 * Description:       Plataforma de capacitación privada (LMS) para WordPress. Cursos, evaluaciones, certificados con QR e insignias. Acceso solo por invitación.
 * Version:           0.14.0
 * Requires at least: 6.0
 * Requires PHP:      8.0
 * Author:            Accons
 * License:           GPL-2.0-or-later
 * Text Domain:       teamms-lms
 */

/*
 * --------------------------------------------------------------------------
 * 1) GUARDA DE SEGURIDAD
 * --------------------------------------------------------------------------
 * ABSPATH es una constante que SOLO existe cuando WordPress está cargado.
 * Si alguien intenta abrir este archivo directamente desde el navegador
 * (sin WordPress), ABSPATH no estará definido y cortamos la ejecución.
 * Esto evita que se filtre código o se ejecute fuera de contexto.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Salir en silencio.
}

/*
 * --------------------------------------------------------------------------
 * 2) CONSTANTES DEL PLUGIN
 * --------------------------------------------------------------------------
 * Definimos "atajos" para no repetir rutas largas en todo el proyecto.
 * - VERSION: versión actual (sirve para refrescar CSS/JS en el navegador).
 * - PATH:    ruta de archivos en el disco (para require/include de PHP).
 * - URL:     dirección web pública (para cargar CSS, JS, imágenes).
 * - BASENAME: identificador del plugin que usa WordPress internamente.
 */
define( 'TEAMMS_LMS_VERSION', '0.14.0' );
define( 'TEAMMS_LMS_PATH', plugin_dir_path( __FILE__ ) );   // termina en \
define( 'TEAMMS_LMS_URL', plugin_dir_url( __FILE__ ) );     // termina en /
define( 'TEAMMS_LMS_BASENAME', plugin_basename( __FILE__ ) );

/*
 * --------------------------------------------------------------------------
 * 3) CARGA DE CLASES BASE
 * --------------------------------------------------------------------------
 * Traemos los archivos que contienen la lógica de activar y desactivar.
 * Aún no ejecutamos nada: solo dejamos las clases disponibles.
 */
require_once TEAMMS_LMS_PATH . 'backend/core/class-lms-roles.php';
require_once TEAMMS_LMS_PATH . 'backend/core/class-lms-activator.php';
require_once TEAMMS_LMS_PATH . 'backend/core/class-lms-deactivator.php';

/*
 * --------------------------------------------------------------------------
 * 4) HOOKS DE ACTIVACIÓN Y DESACTIVACIÓN
 * --------------------------------------------------------------------------
 * register_activation_hook se dispara UNA sola vez, justo cuando das clic
 * en "Activar" en wp-admin. Ahí creamos las tablas de la base de datos.
 *
 * register_deactivation_hook se dispara cuando das clic en "Desactivar".
 * NO borramos datos (eso sería desinstalar), solo limpiamos lo temporal.
 */
register_activation_hook( __FILE__, array( 'LMS_Activator', 'activate' ) );
register_deactivation_hook( __FILE__, array( 'LMS_Deactivator', 'deactivate' ) );

/*
 * --------------------------------------------------------------------------
 * 5) ARRANQUE DEL PLUGIN
 * --------------------------------------------------------------------------
 * El hook 'plugins_loaded' se ejecuta cuando WordPress terminó de cargar
 * todos los plugins. Es el lugar correcto para iniciar nuestra lógica.
 * Por ahora solo dejamos la función lista; iremos llenándola semana a semana.
 */
function teamms_lms_run() {
	// Roles propios + control de acceso (bloquea wp-admin a usuarios externos).
	new LMS_Roles();

	// Autenticación propia del LMS (login/logout en el frontend).
	require_once TEAMMS_LMS_PATH . 'backend/actions/class-lms-auth-actions.php';
	new LMS_Auth_Actions();
	require_once TEAMMS_LMS_PATH . 'backend/actions/class-lms-enroll-actions.php';
	new LMS_Enroll_Actions();

	// Modelos (acceso a datos) — disponibles tanto en frontend como en backend.
	require_once TEAMMS_LMS_PATH . 'backend/models/class-lms-course.php';
	require_once TEAMMS_LMS_PATH . 'backend/models/class-lms-enrollment.php';
	require_once TEAMMS_LMS_PATH . 'backend/models/class-lms-module.php';
	require_once TEAMMS_LMS_PATH . 'backend/models/class-lms-content.php';
	require_once TEAMMS_LMS_PATH . 'backend/models/class-lms-progress.php';
	require_once TEAMMS_LMS_PATH . 'backend/models/class-lms-question.php';
	require_once TEAMMS_LMS_PATH . 'backend/models/class-lms-evaluation.php';
	require_once TEAMMS_LMS_PATH . 'backend/models/class-lms-certificate.php';

	// Acciones de formularios: guardar/borrar cursos, módulos, contenidos, progreso, preguntas y evaluación.
	require_once TEAMMS_LMS_PATH . 'backend/actions/class-lms-course-actions.php';
	require_once TEAMMS_LMS_PATH . 'backend/actions/class-lms-module-actions.php';
	require_once TEAMMS_LMS_PATH . 'backend/actions/class-lms-content-actions.php';
	require_once TEAMMS_LMS_PATH . 'backend/actions/class-lms-progress-actions.php';
	require_once TEAMMS_LMS_PATH . 'backend/actions/class-lms-question-actions.php';
	require_once TEAMMS_LMS_PATH . 'backend/actions/class-lms-evaluation-actions.php';
	new LMS_Course_Actions();
	new LMS_Module_Actions();
	new LMS_Content_Actions();
	new LMS_Progress_Actions();
	new LMS_Question_Actions();
	new LMS_Evaluation_Actions();

	// Si estamos dentro de wp-admin, cargamos el panel de administración.
	if ( is_admin() ) {
		require_once TEAMMS_LMS_PATH . 'backend/admin/class-lms-admin.php';
		new LMS_Admin();
	}

	// Cargamos el frontend (registra los shortcodes de las páginas del LMS).
	require_once TEAMMS_LMS_PATH . 'frontend/class-lms-public.php';
	new LMS_Public();

	// Aquí, en próximas semanas, iniciaremos roles, etc.
}
add_action( 'plugins_loaded', 'teamms_lms_run' );
