<?php
/**
 * Acción: el estudiante envía la evaluación del módulo.
 *
 * Califica comparando lo elegido contra las opciones correctas, guarda el
 * intento y sus respuestas, y vuelve a la pantalla de resultado.
 *
 * Una pregunta cuenta como correcta solo si el conjunto elegido coincide
 * EXACTAMENTE con el conjunto de opciones correctas.
 *
 * @package TeemsLMS
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class LMS_Evaluation_Actions {

	public function __construct() {
		add_action( 'template_redirect', array( $this, 'handle' ) );
	}

	public function handle() {
		if ( is_admin() ) {
			return;
		}
		if ( isset( $_POST['lms_action'] ) && 'submit_evaluation' === $_POST['lms_action'] ) {
			$this->submit();
		}
	}

	private function submit() {
		$nonce = isset( $_POST['_wpnonce'] ) ? sanitize_text_field( wp_unslash( $_POST['_wpnonce'] ) ) : '';
		if ( ! wp_verify_nonce( $nonce, 'lms_submit_evaluation' ) ) {
			$this->redirigir();
		}

		$module_id = isset( $_POST['module_id'] ) ? absint( $_POST['module_id'] ) : 0;
		$uid       = get_current_user_id();

		// Seguridad: no calificar si ya aprobó o si gastó los intentos.
		if ( ! $module_id || ! LMS_Evaluation::can_take( $uid, $module_id ) ) {
			$this->redirigir();
		}

		$preguntas = LMS_Question::all_by_module( $module_id );
		if ( empty( $preguntas ) ) {
			$this->redirigir();
		}

		// Respuestas enviadas: [ question_id => option_id | [option_id, ...] ].
		$enviadas = isset( $_POST['respuesta'] ) ? (array) wp_unslash( $_POST['respuesta'] ) : array();

		$total     = count( $preguntas );
		$correctas = 0;
		$answers   = array();

		foreach ( $preguntas as $q ) {
			// IDs de las opciones correctas de esta pregunta.
			$correct_ids = array();
			foreach ( $q->options as $o ) {
				if ( (int) $o->is_correct === 1 ) {
					$correct_ids[] = (int) $o->id;
				}
			}

			// IDs que eligió el estudiante (normalizamos a array de enteros).
			$sel = isset( $enviadas[ $q->id ] ) ? $enviadas[ $q->id ] : array();
			$sel = is_array( $sel ) ? $sel : array( $sel );
			$sel_ids = array();
			foreach ( $sel as $oid ) {
				$oid = absint( $oid );
				if ( $oid ) {
					$sel_ids[] = $oid;
					$answers[] = array( 'question_id' => (int) $q->id, 'option_id' => $oid );
				}
			}

			// Correcta solo si los conjuntos coinciden exactamente.
			sort( $correct_ids );
			sort( $sel_ids );
			if ( ! empty( $correct_ids ) && $correct_ids === $sel_ids ) {
				$correctas++;
			}
		}

		$score          = $total ? round( $correctas / $total * 10, 2 ) : 0;
		$passed         = $score >= LMS_Evaluation::NOTA_MINIMA;
		$attempt_number = LMS_Evaluation::count_attempts( $uid, $module_id ) + 1;

		LMS_Evaluation::record( $uid, $module_id, $attempt_number, $score, $passed, $answers );

		// El certificado se emite SOLO al completar el CURSO completo (aprobar la
		// evaluación de todos los módulos), no por módulo. Idempotente: no duplica.
		if ( $passed ) {
			$modulo = LMS_Module::find( $module_id );
			if ( $modulo && LMS_Evaluation::course_passed( $uid, (int) $modulo->course_id ) ) {
				LMS_Certificate::issue( $uid, (int) $modulo->course_id );
			}
		}

		$this->redirigir();
	}

	private function redirigir() {
		$destino = isset( $_REQUEST['redirect'] ) ? esc_url_raw( wp_unslash( $_REQUEST['redirect'] ) ) : home_url( '/' );
		wp_safe_redirect( $destino );
		exit;
	}
}
