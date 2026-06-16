<?php
/**
 * Acción: marcar / desmarcar un contenido como completado.
 *
 * Mismo patrón que el resto: 'template_redirect' + nonce (CSRF). Guarda el
 * progreso del usuario ACTUAL de WordPress y vuelve al visor del curso.
 *
 * @package TeemsLMS
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class LMS_Progress_Actions {

	public function __construct() {
		add_action( 'template_redirect', array( $this, 'handle' ) );
	}

	public function handle() {
		if ( is_admin() ) {
			return;
		}
		if ( isset( $_POST['lms_action'] ) && 'toggle_progress' === $_POST['lms_action'] ) {
			$this->toggle();
		}
	}

	private function toggle() {
		$nonce = isset( $_POST['_wpnonce'] ) ? sanitize_text_field( wp_unslash( $_POST['_wpnonce'] ) ) : '';
		if ( ! wp_verify_nonce( $nonce, 'lms_toggle_progress' ) ) {
			$this->redirigir();
		}

		$content_id = isset( $_POST['content_id'] ) ? absint( $_POST['content_id'] ) : 0;
		$user_id    = get_current_user_id();

		if ( $content_id && $user_id ) {
			LMS_Progress::toggle( $user_id, $content_id );
		}
		$this->redirigir();
	}

	private function redirigir() {
		$destino = isset( $_REQUEST['redirect'] ) ? esc_url_raw( wp_unslash( $_REQUEST['redirect'] ) ) : home_url( '/' );
		wp_safe_redirect( $destino );
		exit;
	}
}
