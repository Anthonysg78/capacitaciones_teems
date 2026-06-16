<?php
/**
 * Modelo: Evaluación del módulo (intentos y respuestas).
 *
 * Usa dos tablas:
 *   - evaluation_attempts: un intento de un usuario en un módulo (nota, aprobado).
 *   - attempt_answers:      cada opción elegida en ese intento.
 *
 * Reglas: máximo 2 intentos por módulo, nota mínima 7 para aprobar.
 *
 * @package TeemsLMS
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class LMS_Evaluation {

	const MAX_INTENTOS = 2;
	const NOTA_MINIMA  = 7;

	public static function table() {
		global $wpdb;
		return $wpdb->prefix . 'lms_evaluation_attempts';
	}

	public static function answers_table() {
		global $wpdb;
		return $wpdb->prefix . 'lms_attempt_answers';
	}

	/**
	 * Intentos de un usuario en un módulo, del más nuevo al más viejo.
	 */
	public static function attempts( $user_id, $module_id ) {
		global $wpdb;
		$tabla = self::table();
		return $wpdb->get_results(
			$wpdb->prepare(
				"SELECT * FROM {$tabla} WHERE user_id = %d AND module_id = %d ORDER BY attempt_number DESC",
				absint( $user_id ),
				absint( $module_id )
			)
		);
	}

	public static function count_attempts( $user_id, $module_id ) {
		global $wpdb;
		$tabla = self::table();
		return (int) $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM {$tabla} WHERE user_id = %d AND module_id = %d",
				absint( $user_id ),
				absint( $module_id )
			)
		);
	}

	/**
	 * ¿El usuario ya aprobó la evaluación de este módulo?
	 */
	public static function passed( $user_id, $module_id ) {
		global $wpdb;
		$tabla = self::table();
		$row   = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT id FROM {$tabla} WHERE user_id = %d AND module_id = %d AND passed = 1 LIMIT 1",
				absint( $user_id ),
				absint( $module_id )
			)
		);
		return ! empty( $row );
	}

	/**
	 * ¿Puede el usuario rendir (o reintentar) la evaluación?
	 * No si ya aprobó o si ya gastó los 2 intentos.
	 */
	public static function can_take( $user_id, $module_id ) {
		if ( self::passed( $user_id, $module_id ) ) {
			return false;
		}
		return self::count_attempts( $user_id, $module_id ) < self::MAX_INTENTOS;
	}

	/**
	 * Guarda un intento con su nota y las opciones elegidas.
	 *
	 * @param array $answers  [ [ 'question_id'=>int, 'option_id'=>int ], ... ]
	 * @return int  id del intento creado.
	 */
	public static function record( $user_id, $module_id, $attempt_number, $score, $passed, $answers ) {
		global $wpdb;
		$wpdb->insert(
			self::table(),
			array(
				'user_id'        => absint( $user_id ),
				'module_id'      => absint( $module_id ),
				'attempt_number' => absint( $attempt_number ),
				'score'          => $score,
				'passed'         => $passed ? 1 : 0,
				'finished_at'    => current_time( 'mysql' ),
			),
			array( '%d', '%d', '%d', '%f', '%d', '%s' )
		);
		$attempt_id = (int) $wpdb->insert_id;

		foreach ( $answers as $a ) {
			$wpdb->insert(
				self::answers_table(),
				array(
					'attempt_id'         => $attempt_id,
					'question_id'        => absint( $a['question_id'] ),
					'selected_option_id' => absint( $a['option_id'] ),
				),
				array( '%d', '%d', '%d' )
			);
		}
		return $attempt_id;
	}

	/**
	 * Opciones elegidas en un intento, agrupadas por pregunta.
	 * Devuelve [ question_id => [ option_id, option_id, ... ] ].
	 */
	public static function selected_map( $attempt_id ) {
		global $wpdb;
		$tabla = self::answers_table();
		$rows  = $wpdb->get_results(
			$wpdb->prepare( "SELECT question_id, selected_option_id FROM {$tabla} WHERE attempt_id = %d", absint( $attempt_id ) )
		);
		$map = array();
		foreach ( $rows as $r ) {
			$map[ (int) $r->question_id ][] = (int) $r->selected_option_id;
		}
		return $map;
	}
}
