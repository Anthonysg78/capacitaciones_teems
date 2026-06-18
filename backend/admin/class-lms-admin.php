
<?php
/**
 * Lógica del panel de administración (wp-admin).
 *
 * Registra el menú "LMS Empresarial" y muestra el dashboard.
 *
 * @package TeammsLMS
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class LMS_Admin {

	/**
	 * Conectamos nuestros métodos a los "hooks" de WordPress.
	 * - admin_menu: para agregar el menú.
	 * - admin_enqueue_scripts: para cargar nuestro CSS solo en nuestra página.
	 */
	public function __construct() {
		add_action( 'admin_menu', array( $this, 'register_menu' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_assets' ) );
	}

	/**
	 * Agrega el menú "LMS Empresarial" en la barra lateral de wp-admin.
	 */
	public function register_menu() {
		add_menu_page(
			'LMS Empresarial',                 // Título de la pestaña del navegador.
			'LMS Empresarial',                 // Texto que aparece en el menú lateral.
			'manage_options',                  // Solo administradores pueden verlo.
			'teamms-lms',                       // Identificador único (slug) de la página.
			array( $this, 'render_dashboard' ),// Función que dibuja el contenido.
			'dashicons-welcome-learn-more',    // Iconito del menú.
			3                                  // Posición en el menú lateral.
		);
	}

	/**
	 * Carga nuestro CSS SOLO en la página del LMS (no en todo wp-admin).
	 */
	public function enqueue_assets( $hook ) {
		if ( 'toplevel_page_teamms-lms' !== $hook ) {
			return;
		}
		wp_enqueue_style(
			'teamms-lms-admin',
			TEAMMS_LMS_URL . 'diseno/css/lms-admin.css',
			array(),
			TEAMMS_LMS_VERSION
		);
	}

	/**
	 * Dibuja el dashboard. Lee estadísticas reales de la base de datos
	 * y luego carga la "vista" (el HTML) pasándole esos datos.
	 */
	public function render_dashboard() {
		global $wpdb;
		$prefix = $wpdb->prefix . 'lms_';

		// Contamos registros de varias tablas. Por ahora darán 0 (están vacías),
		// pero esto demuestra que el plugin SÍ se conecta a la base de datos.
		$stats = array(
			'cursos'       => (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$prefix}courses" ),
			'modulos'      => (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$prefix}modules" ),
			'inscripciones'=> (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$prefix}enrollments" ),
			'certificados' => (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$prefix}certificates" ),
		);

		// Verificamos cuántas tablas wp_lms_ existen (deberían ser 15).
		$tablas_existentes = (int) $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM information_schema.tables
				 WHERE table_schema = %s AND table_name LIKE %s",
				DB_NAME,
				$wpdb->esc_like( $prefix ) . '%'
			)
		);

		// Cargamos la vista (el HTML). Las variables $stats y $tablas_existentes
		// quedan disponibles dentro de ese archivo.
		require TEAMMS_LMS_PATH . 'backend/admin/views/dashboard.php';
	}
}

