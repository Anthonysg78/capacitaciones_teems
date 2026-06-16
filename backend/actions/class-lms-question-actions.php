<?php
/**
 * Acciones del banco de preguntas (guardar / borrar).
 *
 * Mismo patrón: 'template_redirect' + nonce + sanitización. Una pregunta llega
 * con su enunciado, 4 opciones (option_text[]) y cuál es la correcta (correct).
 *
 * @package TeemsLMS
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class LMS_Question_Actions {

	public function __construct() {
		add_action( 'template_redirect', array( $this, 'handle' ) );
	}

	public function handle() {
		if ( is_admin() ) {
			return;
		}
		if ( isset( $_POST['lms_action'] ) && 'save_question' === $_POST['lms_action'] ) {
			$this->save();
			return;
		}
		if ( isset( $_GET['lms_action'] ) && 'delete_question' === $_GET['lms_action'] ) {
			$this->delete();
		}
	}

	private function save() {
		$nonce = isset( $_POST['_wpnonce'] ) ? sanitize_text_field( wp_unslash( $_POST['_wpnonce'] ) ) : '';
		if ( ! wp_verify_nonce( $nonce, 'lms_save_question' ) ) {
			$this->redirigir( 'expired' );
		}

		$id        = isset( $_POST['question_id'] ) ? absint( $_POST['question_id'] ) : 0;
		$module_id = isset( $_POST['module_id'] ) ? absint( $_POST['module_id'] ) : 0;
		$text      = sanitize_textarea_field( wp_unslash( $_POST['question_text'] ?? '' ) );
		// 'correct' llega como ARRAY de índices (puede haber varias correctas).
		$correct   = isset( $_POST['correct'] ) ? array_map( 'intval', (array) wp_unslash( $_POST['correct'] ) ) : array();
		$textos    = isset( $_POST['option_text'] ) ? (array) wp_unslash( $_POST['option_text'] ) : array();

		// Construimos las opciones: descartamos las vacías y marcamos como
		// correctas las que estén en el array de índices elegidos.
		$options    = array();
		$correct_ok = false;
		foreach ( $textos as $i => $t ) {
			$t = sanitize_text_field( $t );
			if ( '' === trim( $t ) ) {
				continue;
			}
			$es_correcta = in_array( (int) $i, $correct, true );
			if ( $es_correcta ) {
				$correct_ok = true;
			}
			$options[] = array( 'text' => $t, 'correct' => $es_correcta );
		}

		// Validación: enunciado, al menos 2 opciones y al menos una correcta.
		if ( '' === $text || count( $options ) < 2 || ! $correct_ok ) {
			$this->redirigir( 'error' );
		}

		if ( $id ) {
			LMS_Question::update( $id, $text, $options );
		} else {
			LMS_Question::create( $module_id, $text, $options );
		}
		$this->redirigir( 'saved' );
	}

	private function delete() {
		$id    = isset( $_GET['id'] ) ? absint( $_GET['id'] ) : 0;
		$nonce = isset( $_GET['_wpnonce'] ) ? sanitize_text_field( wp_unslash( $_GET['_wpnonce'] ) ) : '';
		if ( ! wp_verify_nonce( $nonce, 'lms_delete_question_' . $id ) ) {
			$this->redirigir( 'expired' );
		}
		if ( $id ) {
			LMS_Question::delete( $id );
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
