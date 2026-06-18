<?php
/**
 * Acciones de Contenidos (guardar / borrar).
 *
 * Mismo patrón que cursos, módulos y subtemas: 'template_redirect' + nonce
 * (protección CSRF) + sanitización de cada campo.
 *
 * @package TeammsLMS
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class LMS_Content_Actions {

	public function __construct() {
		add_action( 'template_redirect', array( $this, 'handle' ) );
	}

	public function handle() {
		if ( is_admin() ) {
			return;
		}
		if ( isset( $_POST['lms_action'] ) && 'save_content' === $_POST['lms_action'] ) {
			$this->save();
			return;
		}
		if ( isset( $_GET['lms_action'] ) && 'delete_content' === $_GET['lms_action'] ) {
			$this->delete();
		}
	}

	private function save() {
		$nonce = isset( $_POST['_wpnonce'] ) ? sanitize_text_field( wp_unslash( $_POST['_wpnonce'] ) ) : '';
		if ( ! wp_verify_nonce( $nonce, 'lms_save_content' ) ) {
			$this->redirigir( 'expired' );
		}

		$id          = isset( $_POST['content_id'] ) ? absint( $_POST['content_id'] ) : 0;
		$module_id   = isset( $_POST['module_id'] ) ? absint( $_POST['module_id'] ) : 0;
		$type        = sanitize_key( wp_unslash( $_POST['type'] ?? '' ) );
		$title       = sanitize_text_field( wp_unslash( $_POST['title'] ?? '' ) );
		$text        = wp_kses_post( wp_unslash( $_POST['content_text'] ?? '' ) );
		$url         = esc_url_raw( wp_unslash( $_POST['content_url'] ?? '' ) );
		$order       = isset( $_POST['order_index'] ) ? absint( $_POST['order_index'] ) : 0;

		// El tipo debe ser uno de los válidos; el título es obligatorio.
		if ( '' === $title || ! array_key_exists( $type, LMS_Content::tipos() ) ) {
			$this->redirigir( 'error' );
		}

		// Si el admin subió un archivo (PDF/documento), lo guardamos en la
		// biblioteca de medios de WordPress y usamos su URL.
		if ( ! empty( $_FILES['content_file']['name'] ) ) {
			require_once ABSPATH . 'wp-admin/includes/file.php';
			require_once ABSPATH . 'wp-admin/includes/media.php';
			require_once ABSPATH . 'wp-admin/includes/image.php';

			$attachment_id = media_handle_upload( 'content_file', 0 );
			if ( is_wp_error( $attachment_id ) ) {
				$this->redirigir( 'error' );
			}
			$url = wp_get_attachment_url( $attachment_id );
		}

		// Si no se escribió enlace ni se subió archivo nuevo, conservamos el
		// archivo/enlace anterior (útil al editar sin querer cambiarlo).
		if ( '' === $url ) {
			$url = esc_url_raw( wp_unslash( $_POST['current_url'] ?? '' ) );
		}

		$data = array(
			'type'         => $type,
			'title'        => $title,
			'content_text' => $text,
			'content_url'  => $url,
			'order_index'  => $order,
		);

		if ( $id ) {
			LMS_Content::update( $id, $data );
		} else {
			$data['module_id'] = $module_id;
			LMS_Content::create( $data );
		}
		$this->redirigir( 'saved' );
	}

	private function delete() {
		$id    = isset( $_GET['id'] ) ? absint( $_GET['id'] ) : 0;
		$nonce = isset( $_GET['_wpnonce'] ) ? sanitize_text_field( wp_unslash( $_GET['_wpnonce'] ) ) : '';
		if ( ! wp_verify_nonce( $nonce, 'lms_delete_content_' . $id ) ) {
			$this->redirigir( 'expired' );
		}
		if ( $id ) {
			LMS_Content::delete( $id );
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
