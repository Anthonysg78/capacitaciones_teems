<?php
/**
 * Acciones de Contenidos (guardar / borrar).
 *
 * Mismo patrón que cursos, módulos y subtemas: 'template_redirect' + nonce
 * (protección CSRF) + sanitización de cada campo.
 *
 * @package TeemsLMS
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
		$subtopic_id = isset( $_POST['subtopic_id'] ) ? absint( $_POST['subtopic_id'] ) : 0;
		$type        = sanitize_key( wp_unslash( $_POST['type'] ?? '' ) );
		$title       = sanitize_text_field( wp_unslash( $_POST['title'] ?? '' ) );
		$text        = wp_kses_post( wp_unslash( $_POST['content_text'] ?? '' ) );
		$url         = esc_url_raw( wp_unslash( $_POST['content_url'] ?? '' ) );
		$order       = isset( $_POST['order_index'] ) ? absint( $_POST['order_index'] ) : 0;

		// El tipo debe ser uno de los válidos; el título es obligatorio.
		if ( '' === $title || ! array_key_exists( $type, LMS_Content::tipos() ) ) {
			$this->redirigir( 'error' );
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
			$data['subtopic_id'] = $subtopic_id;
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
