<?php
/**
 * Acciones de Usuarios (crear / borrar) desde el panel del administrador.
 *
 * Se procesan en 'template_redirect' (antes de imprimir HTML), igual que el
 * resto de acciones del LMS. Por ser sensibles (tocan cuentas), se exige la
 * capacidad 'lms_manage' además del nonce.
 *
 * Seguridad: nonce + permiso (lms_manage) + sanitización.
 *
 * @package TeammsLMS
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class LMS_User_Actions {

	public function __construct() {
		add_action( 'template_redirect', array( $this, 'handle' ) );
	}

	public function handle() {
		if ( is_admin() ) {
			return;
		}
		// Crear (formulario POST).
		if ( isset( $_POST['lms_action'] ) && 'save_user' === $_POST['lms_action'] ) {
			$this->save();
			return;
		}
		// Borrar (enlace GET con nonce).
		if ( isset( $_GET['lms_action'] ) && 'delete_user' === $_GET['lms_action'] ) {
			$this->delete();
		}
	}

	/**
	 * Crear una cuenta (estudiante o administrador).
	 */
	private function save() {
		if ( ! current_user_can( 'lms_manage' ) ) {
			$this->redirigir( 'denied' );
		}
		$nonce = isset( $_POST['_wpnonce'] ) ? sanitize_text_field( wp_unslash( $_POST['_wpnonce'] ) ) : '';
		if ( ! wp_verify_nonce( $nonce, 'lms_save_user' ) ) {
			$this->redirigir( 'expired' );
		}

		$nombre = sanitize_text_field( wp_unslash( $_POST['name'] ?? '' ) );
		$email  = sanitize_email( wp_unslash( $_POST['email'] ?? '' ) );
		$pass   = (string) wp_unslash( $_POST['password'] ?? '' );
		$role   = ( isset( $_POST['role'] ) && 'lms_admin' === $_POST['role'] ) ? 'lms_admin' : 'lms_student';

		// Validaciones básicas (mismas reglas que el registro de estudiantes).
		if ( '' === $nombre || ! is_email( $email ) || strlen( $pass ) < 6 ) {
			$this->redirigir( 'datos' );
		}
		if ( email_exists( $email ) || username_exists( $email ) ) {
			$this->redirigir( 'existe' );
		}

		$user_id = LMS_User::create( $nombre, $email, $pass, $role );
		if ( is_wp_error( $user_id ) ) {
			$this->redirigir( 'datos' );
		}
		$this->redirigir( 'saved' );
	}

	/**
	 * Borrar una cuenta (y limpiar sus datos del LMS). No se permite borrarse
	 * a uno mismo.
	 */
	private function delete() {
		if ( ! current_user_can( 'lms_manage' ) ) {
			$this->redirigir( 'denied' );
		}
		$id    = isset( $_GET['id'] ) ? absint( $_GET['id'] ) : 0;
		$nonce = isset( $_GET['_wpnonce'] ) ? sanitize_text_field( wp_unslash( $_GET['_wpnonce'] ) ) : '';
		if ( ! $id || ! wp_verify_nonce( $nonce, 'lms_delete_user_' . $id ) ) {
			$this->redirigir( 'expired' );
		}
		if ( $id === get_current_user_id() ) {
			$this->redirigir( 'self' );
		}
		LMS_User::delete( $id );
		$this->redirigir( 'deleted' );
	}

	/** URL de la lista a la que volvemos (viene en el campo 'redirect'). */
	private function target() {
		$campo = $_REQUEST['redirect'] ?? '';
		return $campo ? esc_url_raw( wp_unslash( $campo ) ) : home_url( '/' );
	}

	/** Redirige con un mensaje y termina. */
	private function redirigir( $msg ) {
		wp_safe_redirect( add_query_arg( 'msg', $msg, $this->target() ) );
		exit;
	}
}
