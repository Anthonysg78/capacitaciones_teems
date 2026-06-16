<?php
/**
 * Acciones de Módulos (guardar / borrar).
 *
 * Mismo patrón que los cursos: se procesa en 'template_redirect' (antes de
 * pintar HTML), con nonce y sanitización, y redirige con un mensaje.
 *
 * @package TeemsLMS
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class LMS_Module_Actions {

	public function __construct() {
		add_action( 'template_redirect', array( $this, 'handle' ) );
	}

	public function handle() {
		if ( is_admin() ) {
			return;
		}
		if ( isset( $_POST['lms_action'] ) && 'save_module' === $_POST['lms_action'] ) {
			$this->save();
			return;
		}
		if ( isset( $_GET['lms_action'] ) && 'delete_module' === $_GET['lms_action'] ) {
			$this->delete();
		}
	}

	private function save() {
		$nonce = isset( $_POST['_wpnonce'] ) ? sanitize_text_field( wp_unslash( $_POST['_wpnonce'] ) ) : '';
		if ( ! wp_verify_nonce( $nonce, 'lms_save_module' ) ) {
			$this->redirigir( 'expired' );
		}

		$id        = isset( $_POST['module_id'] ) ? absint( $_POST['module_id'] ) : 0;
		$course_id = isset( $_POST['course_id'] ) ? absint( $_POST['course_id'] ) : 0;
		$title     = sanitize_text_field( wp_unslash( $_POST['title'] ?? '' ) );
		$order     = isset( $_POST['order_index'] ) ? absint( $_POST['order_index'] ) : 0;

		if ( '' === $title ) {
			$this->redirigir( 'error' );
		}

		if ( $id ) {
			LMS_Module::update( $id, array( 'title' => $title, 'order_index' => $order ) );
		} else {
			LMS_Module::create( array( 'course_id' => $course_id, 'title' => $title, 'order_index' => $order ) );
		}
		$this->redirigir( 'saved' );
	}

	private function delete() {
		$id    = isset( $_GET['id'] ) ? absint( $_GET['id'] ) : 0;
		$nonce = isset( $_GET['_wpnonce'] ) ? sanitize_text_field( wp_unslash( $_GET['_wpnonce'] ) ) : '';
		if ( ! wp_verify_nonce( $nonce, 'lms_delete_module_' . $id ) ) {
			$this->redirigir( 'expired' );
		}
		if ( $id ) {
			LMS_Module::delete( $id );
		}
		$this->redirigir( 'deleted' );
	}

	private function target() {
		$campo = $_REQUEST['redirect'] ?? '';
		return $campo ? esc_url_raw( wp_unslash( $campo ) ) : home_url( '/' );
	}

	private function redirigir( $msg ) {
		wp_safe_redirect( add_query_arg( 'msg', $msg, $this->target() ) );
		exit;
	}
}
