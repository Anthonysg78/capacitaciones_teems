<?php
/**
 * Modelo: Progreso del estudiante por contenido.
 *
 * Tabla wp_lms_content_progress: una fila = "este usuario completó este
 * contenido". Si no hay fila, el contenido está pendiente.
 *
 * Centraliza también el cálculo del porcentaje de avance de un curso.
 *
 * @package TeemsLMS
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class LMS_Progress {

	public static function table() {
		global $wpdb;
		return $wpdb->prefix . 'lms_content_progress';
	}

	/**
	 * ¿El usuario ya completó este contenido?
	 */
	public static function is_completed( $user_id, $content_id ) {
		global $wpdb;
		$tabla = self::table();
		$row   = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT id FROM {$tabla} WHERE user_id = %d AND content_id = %d LIMIT 1",
				absint( $user_id ),
				absint( $content_id )
			)
		);
		return ! empty( $row );
	}

	/**
	 * Marca/desmarca un contenido como completado (alterna el estado).
	 * Devuelve el nuevo estado: true = completado, false = pendiente.
	 */
	public static function toggle( $user_id, $content_id ) {
		global $wpdb;
		$user_id    = absint( $user_id );
		$content_id = absint( $content_id );

		if ( self::is_completed( $user_id, $content_id ) ) {
			$wpdb->delete( self::table(), array( 'user_id' => $user_id, 'content_id' => $content_id ), array( '%d', '%d' ) );
			return false;
		}
		$wpdb->insert( self::table(), array( 'user_id' => $user_id, 'content_id' => $content_id ), array( '%d', '%d' ) );
		return true;
	}

	/**
	 * IDs de los contenidos de un curso que el usuario ya completó.
	 * Devuelve un array de enteros (puede estar vacío).
	 */
	public static function completed_ids_for_course( $user_id, $course_id ) {
		global $wpdb;
		$p   = $wpdb->prefix . 'lms_';
		$ids = $wpdb->get_col(
			$wpdb->prepare(
				"SELECT cp.content_id
				 FROM {$p}content_progress cp
				 INNER JOIN {$p}contents  c ON cp.content_id = c.id
				 INNER JOIN {$p}subtopics s ON c.subtopic_id = s.id
				 INNER JOIN {$p}modules   m ON s.module_id   = m.id
				 WHERE m.course_id = %d AND cp.user_id = %d",
				absint( $course_id ),
				absint( $user_id )
			)
		);
		return array_map( 'intval', (array) $ids );
	}

	/**
	 * Total de contenidos que tiene un curso (sumando todos sus subtemas).
	 */
	public static function total_contents_in_course( $course_id ) {
		global $wpdb;
		$p = $wpdb->prefix . 'lms_';
		return (int) $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*)
				 FROM {$p}contents  c
				 INNER JOIN {$p}subtopics s ON c.subtopic_id = s.id
				 INNER JOIN {$p}modules   m ON s.module_id   = m.id
				 WHERE m.course_id = %d",
				absint( $course_id )
			)
		);
	}

	/**
	 * Porcentaje de avance (0-100) de un usuario en un curso.
	 */
	public static function course_percent( $user_id, $course_id ) {
		$total = self::total_contents_in_course( $course_id );
		if ( 0 === $total ) {
			return 0;
		}
		$hechos = count( self::completed_ids_for_course( $user_id, $course_id ) );
		return (int) round( $hechos / $total * 100 );
	}
}
