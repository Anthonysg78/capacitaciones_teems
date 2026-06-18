<?php
/**
 * Acciones de Empresas (guardar / borrar).
 *
 * Mismo patrón que cursos: hook 'template_redirect' + nonce (CSRF) +
 * sanitización de cada campo. Si algo falla, redirige con un mensaje.
 *
 * @package TeammsLMS
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class LMS_Company_Actions {

	public function __construct() {
		add_action( 'template_redirect', array( $this, 'handle' ) );
	}

	/**
	 * Detecta si llega una acción de empresas y la procesa. Si no, no hace nada.
	 */
	public function handle() {
		if ( is_admin() ) {
			return;
		}
		if ( isset( $_POST['lms_action'] ) && 'save_company' === $_POST['lms_action'] ) {
			$this->save();
			return;
		}
		if ( isset( $_GET['lms_action'] ) && 'delete_company' === $_GET['lms_action'] ) {
			$this->delete();
		}
	}

	/**
	 * Guardar (crear o editar) una empresa.
	 */
	private function save() {
		$nonce = isset( $_POST['_wpnonce'] ) ? sanitize_text_field( wp_unslash( $_POST['_wpnonce'] ) ) : '';
		if ( ! wp_verify_nonce( $nonce, 'lms_save_company' ) ) {
			$this->redirigir( 'expired' );
		}

		$id   = isset( $_POST['company_id'] ) ? absint( $_POST['company_id'] ) : 0;
		$data = array(
			'name'          => sanitize_text_field( wp_unslash( $_POST['name'] ?? '' ) ),
			'contact_email' => sanitize_email( wp_unslash( $_POST['contact_email'] ?? '' ) ),
			'active'        => isset( $_POST['active'] ) ? 1 : 0,
		);

		// El nombre es obligatorio.
		if ( '' === $data['name'] ) {
			$this->redirigir( 'error' );
		}

		// Logo: por defecto conservamos el que ya tenía (al editar). Si se sube
		// una imagen nueva, la guardamos en la Biblioteca de Medios y usamos su URL.
		$logo = esc_url_raw( wp_unslash( $_POST['current_logo'] ?? '' ) );
		if ( ! empty( $_FILES['logo_file']['name'] ) ) {
			require_once ABSPATH . 'wp-admin/includes/file.php';
			require_once ABSPATH . 'wp-admin/includes/media.php';
			require_once ABSPATH . 'wp-admin/includes/image.php';

			$attachment_id = media_handle_upload( 'logo_file', 0 );
			if ( is_wp_error( $attachment_id ) ) {
				$this->redirigir( 'error' );
			}
			$logo = wp_get_attachment_url( $attachment_id );
		}
		$data['logo_url'] = $logo;

		if ( $id ) {
			LMS_Company::update( $id, $data );
		} else {
			LMS_Company::create( $data );
		}
		$this->redirigir( 'saved' );
	}

	/**
	 * Borrar una empresa.
	 */
	private function delete() {
		$id    = isset( $_GET['id'] ) ? absint( $_GET['id'] ) : 0;
		$nonce = isset( $_GET['_wpnonce'] ) ? sanitize_text_field( wp_unslash( $_GET['_wpnonce'] ) ) : '';
		if ( ! wp_verify_nonce( $nonce, 'lms_delete_company_' . $id ) ) {
			$this->redirigir( 'expired' );
		}
		if ( $id ) {
			LMS_Company::delete( $id );
		}
		$this->redirigir( 'deleted' );
	}

	/**
	 * URL a la que volvemos (viene en el campo 'redirect').
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
