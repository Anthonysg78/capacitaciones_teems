<?php
/**
 * Modelo: Pregunta del banco de evaluación (con sus opciones).
 *
 * Una pregunta pertenece a un módulo (module_id) y tiene varias opciones
 * (tabla question_options), de las cuales una está marcada como correcta.
 *
 * @package TeemsLMS
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class LMS_Question {

	public static function table() {
		global $wpdb;
		return $wpdb->prefix . 'lms_questions';
	}

	public static function options_table() {
		global $wpdb;
		return $wpdb->prefix . 'lms_question_options';
	}

	/**
	 * Opciones de una pregunta, en orden.
	 */
	public static function options( $question_id ) {
		global $wpdb;
		$tabla = self::options_table();
		return $wpdb->get_results(
			$wpdb->prepare( "SELECT * FROM {$tabla} WHERE question_id = %d ORDER BY id ASC", absint( $question_id ) )
		);
	}

	/**
	 * Todas las preguntas de un módulo, cada una con su propiedad ->options.
	 */
	public static function all_by_module( $module_id ) {
		global $wpdb;
		$tabla = self::table();
		$rows  = $wpdb->get_results(
			$wpdb->prepare( "SELECT * FROM {$tabla} WHERE module_id = %d ORDER BY id ASC", absint( $module_id ) )
		);
		foreach ( $rows as $q ) {
			$q->options = self::options( (int) $q->id );
		}
		return $rows;
	}

	public static function count_by_module( $module_id ) {
		global $wpdb;
		$tabla = self::table();
		return (int) $wpdb->get_var(
			$wpdb->prepare( "SELECT COUNT(*) FROM {$tabla} WHERE module_id = %d", absint( $module_id ) )
		);
	}

	/**
	 * Una pregunta por id, con su propiedad ->options. Null si no existe.
	 */
	public static function find( $id ) {
		global $wpdb;
		$tabla = self::table();
		$q     = $wpdb->get_row(
			$wpdb->prepare( "SELECT * FROM {$tabla} WHERE id = %d", absint( $id ) )
		);
		if ( $q ) {
			$q->options = self::options( (int) $q->id );
		}
		return $q;
	}

	/**
	 * Crea una pregunta con sus opciones.
	 *
	 * @param int    $module_id
	 * @param string $text     enunciado.
	 * @param array  $options  [ [ 'text'=>str, 'correct'=>bool ], ... ]
	 * @return int   id de la pregunta creada (0 si falla).
	 */
	public static function create( $module_id, $text, $options ) {
		global $wpdb;
		$ok = $wpdb->insert(
			self::table(),
			array( 'module_id' => absint( $module_id ), 'question_text' => $text ),
			array( '%d', '%s' )
		);
		if ( ! $ok ) {
			return 0;
		}
		$qid = (int) $wpdb->insert_id;
		self::save_options( $qid, $options );
		return $qid;
	}

	/**
	 * Actualiza el enunciado y reemplaza las opciones de una pregunta.
	 */
	public static function update( $id, $text, $options ) {
		global $wpdb;
		$wpdb->update(
			self::table(),
			array( 'question_text' => $text ),
			array( 'id' => absint( $id ) ),
			array( '%s' ),
			array( '%d' )
		);
		// Reemplazo simple: borrar las opciones viejas y volver a insertarlas.
		$wpdb->delete( self::options_table(), array( 'question_id' => absint( $id ) ), array( '%d' ) );
		self::save_options( (int) $id, $options );
		return true;
	}

	public static function delete( $id ) {
		global $wpdb;
		$wpdb->delete( self::options_table(), array( 'question_id' => absint( $id ) ), array( '%d' ) );
		$wpdb->delete( self::table(), array( 'id' => absint( $id ) ), array( '%d' ) );
		return true;
	}

	/**
	 * Inserta el array de opciones para una pregunta.
	 */
	private static function save_options( $question_id, $options ) {
		global $wpdb;
		foreach ( $options as $o ) {
			$wpdb->insert(
				self::options_table(),
				array(
					'question_id' => absint( $question_id ),
					'option_text' => $o['text'],
					'is_correct'  => ! empty( $o['correct'] ) ? 1 : 0,
				),
				array( '%d', '%s', '%d' )
			);
		}
	}
}
