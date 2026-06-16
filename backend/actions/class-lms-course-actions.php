<?php
/**
 * Acciones de Cursos (guardar / borrar).
 *
 * Se procesan en la propia página vía el hook 'template_redirect', que corre
 * ANTES de dibujar el HTML (por eso podemos redirigir sin "headers already
 * sent" ni pantallas en blanco). Si algo falla, redirige con un mensaje.
 *
 * Seguridad: nonce + permisos + sanitización (reglas del proyecto).
 *
 * @package TeemsLMS
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class LMS_Course_Actions {

	public function __construct() {
		add_action( 'template_redirect', array( $this, 'handle' ) );
	}

	/**
	 * Detecta si llega una acción de cursos y la procesa. Si no, no hace nada.
	 */
	public function handle() {
		if ( is_admin() ) {
			return;
		}
		// Guardar (formulario POST).
		if ( isset( $_POST['lms_action'] ) && 'save_course' === $_POST['lms_action'] ) {
			$this->save();
			return;
		}
		// Borrar (enlace GET con nonce).
		if ( isset( $_GET['lms_action'] ) && 'delete_course' === $_GET['lms_action'] ) {
			$this->delete();
		}
	}

	/**
	 * Guardar (crear o editar) un curso.
	 */
	private function save() {
		// NOTA: durante esta etapa NO atamos los permisos al login de WordPress
		// (la plataforma es para empresas externas sin WP). El control de acceso
		// real (roles propios + invitaciones) llegará en la Semana 2.
		// Nonce (protección CSRF, funciona también sin login).
		$nonce = isset( $_POST['_wpnonce'] ) ? sanitize_text_field( wp_unslash( $_POST['_wpnonce'] ) ) : '';
		if ( ! wp_verify_nonce( $nonce, 'lms_save_course' ) ) {
			$this->redirigir( 'expired' );
		}

		// Sanitizar.
		$id   = isset( $_POST['course_id'] ) ? absint( $_POST['course_id'] ) : 0;
		$data = array(
			'title'       => sanitize_text_field( wp_unslash( $_POST['title'] ?? '' ) ),
			'description' => wp_kses_post( wp_unslash( $_POST['description'] ?? '' ) ),
			'published'   => isset( $_POST['published'] ) ? 1 : 0,
		);

		if ( '' === $data['title'] ) {
			$this->redirigir( 'error' );
		}

		if ( $id ) {
			LMS_Course::update( $id, $data );
		} else {
			LMS_Course::create( $data );
		}
		$this->redirigir( 'saved' );
	}

	/**
	 * Borrar un curso.
	 */
	private function delete() {
		// Sin control por login de WordPress en esta etapa (ver nota en save()).
		$id    = isset( $_GET['id'] ) ? absint( $_GET['id'] ) : 0;
		$nonce = isset( $_GET['_wpnonce'] ) ? sanitize_text_field( wp_unslash( $_GET['_wpnonce'] ) ) : '';
		if ( ! wp_verify_nonce( $nonce, 'lms_delete_course_' . $id ) ) {
			$this->redirigir( 'expired' );
		}
		if ( $id ) {
			LMS_Course::delete( $id );
		}
		$this->redirigir( 'deleted' );
	}

	/**
	 * URL de la lista a la que volvemos (viene en el campo 'redirect').
	 */
	private function target() {
		$campo = $_REQUEST['redirect'] ?? '';
		return $campo ? esc_url_raw( wp_unslash( $campo ) ) : home_url( '/' );
	}

	/**
	 * Redirige con un mensaje y termina.
	 */
	private function redirigir( $msg ) {
		wp_safe_redirect( add_query_arg( 'msg', $msg, $this->target() ) );
		exit;
	}
}
