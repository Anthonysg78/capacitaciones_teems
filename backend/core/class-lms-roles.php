<?php
/**
 * Roles y control de acceso del LMS.
 *
 * Define dos roles propios (todos son usuarios de WordPress por dentro, pero
 * eso es invisible para el usuario final):
 *   - lms_admin    → administra la plataforma (capacidad 'lms_manage').
 *   - lms_student  → estudiante que toma cursos ('lms_student').
 *
 * Además, BLOQUEA el escritorio de WordPress (/wp-admin) a los estudiantes:
 * ellos solo deben ver el LMS, nunca WordPress.
 *
 * @package TeammsLMS
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class LMS_Roles {

	/**
	 * Crea los roles. Se llama al ACTIVAR el plugin. Idempotente: add_role()
	 * no hace nada si el rol ya existe.
	 */
	public static function register() {
		add_role( 'lms_admin',   'Administrador LMS', array( 'read' => true, 'lms_manage'  => true ) );
		add_role( 'lms_student', 'Estudiante',        array( 'read' => true, 'lms_student' => true ) );

		// El administrador de WordPress también puede gestionar el LMS.
		$admin = get_role( 'administrator' );
		if ( $admin && ! $admin->has_cap( 'lms_manage' ) ) {
			$admin->add_cap( 'lms_manage' );
		}
	}

	/**
	 * Garantiza que los roles existan aunque el plugin se haya activado antes de
	 * que esta clase existiera (no obliga a reactivar).
	 */
	public static function ensure() {
		if ( ! get_role( 'lms_student' ) ) {
			self::register();
		}
	}

	public function __construct() {
		add_action( 'init', array( __CLASS__, 'ensure' ) );
		add_action( 'admin_init', array( $this, 'block_backend' ) );
		add_action( 'after_setup_theme', array( $this, 'maybe_hide_admin_bar' ) );
	}

	/**
	 * ¿El usuario actual es "externo" (estudiante, sin poderes de admin)?
	 * Los administradores de WordPress y los lms_admin NO son externos.
	 */
	public function is_external_user() {
		if ( ! is_user_logged_in() ) {
			return false;
		}
		if ( current_user_can( 'manage_options' ) || current_user_can( 'lms_manage' ) ) {
			return false;
		}
		return current_user_can( 'lms_student' );
	}

	/**
	 * Si un usuario externo intenta entrar a /wp-admin, lo mandamos al inicio
	 * del sitio (donde vive el LMS). Se permite el AJAX (admin-ajax.php).
	 */
	public function block_backend() {
		if ( wp_doing_ajax() ) {
			return;
		}
		if ( $this->is_external_user() ) {
			wp_safe_redirect( home_url( '/' ) );
			exit;
		}
	}

	/**
	 * Oculta la barra de administración de WordPress a los usuarios externos.
	 */
	public function maybe_hide_admin_bar() {
		if ( $this->is_external_user() ) {
			show_admin_bar( false );
		}
	}
}
