<?php
/**
 * Modelo: Subtema.
 *
 * Acceso a la tabla wp_lms_subtopics. Un subtema pertenece a un módulo
 * (module_id) y tiene un orden dentro de él.
 *
 * @package TeemsLMS
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class LMS_Subtopic {

	public static function table() {
		global $wpdb;
		return $wpdb->prefix . 'lms_subtopics';
	}

	/**
	 * Todos los subtemas de un módulo, ordenados.
	 */
	public static function all_by_module( $module_id ) {
		global $wpdb;
		$tabla = self::table();
		return $wpdb->get_results(
			$wpdb->prepare( "SELECT * FROM {$tabla} WHERE module_id = %d ORDER BY order_index ASC, id ASC", absint( $module_id ) )
		);
	}

	public static function find( $id ) {
		global $wpdb;
		$tabla = self::table();
		return $wpdb->get_row(
			$wpdb->prepare( "SELECT * FROM {$tabla} WHERE id = %d", absint( $id ) )
		);
	}

	/**
	 * Cuántos subtemas tiene un curso (sumando los de todos sus módulos).
	 */
	public static function count_by_course( $course_id ) {
		global $wpdb;
		$subt = self::table();
		$mods = $wpdb->prefix . 'lms_modules';
		return (int) $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM {$subt} s INNER JOIN {$mods} m ON s.module_id = m.id WHERE m.course_id = %d",
				absint( $course_id )
			)
		);
	}

	/**
	 * Siguiente orden disponible para un módulo (max + 1).
	 */
	public static function next_order( $module_id ) {
		global $wpdb;
		$tabla = self::table();
		$max   = (int) $wpdb->get_var(
			$wpdb->prepare( "SELECT MAX(order_index) FROM {$tabla} WHERE module_id = %d", absint( $module_id ) )
		);
		return $max + 1;
	}

	/**
	 * Crea un subtema. $data = [ module_id, title, description, order_index ].
	 */
	public static function create( $data ) {
		global $wpdb;
		$ok = $wpdb->insert(
			self::table(),
			array(
				'module_id'   => absint( $data['module_id'] ),
				'title'       => $data['title'],
				'description' => $data['description'],
				'order_index' => absint( $data['order_index'] ),
			),
			array( '%d', '%s', '%s', '%d' )
		);
		return $ok ? (int) $wpdb->insert_id : 0;
	}

	/**
	 * Actualiza un subtema. $data = [ title, description, order_index ].
	 */
	public static function update( $id, $data ) {
		global $wpdb;
		$res = $wpdb->update(
			self::table(),
			array(
				'title'       => $data['title'],
				'description' => $data['description'],
				'order_index' => absint( $data['order_index'] ),
			),
			array( 'id' => absint( $id ) ),
			array( '%s', '%s', '%d' ),
			array( '%d' )
		);
		return false !== $res;
	}

	public static function delete( $id ) {
		global $wpdb;
		$res = $wpdb->delete( self::table(), array( 'id' => absint( $id ) ), array( '%d' ) );
		return false !== $res;
	}
}
