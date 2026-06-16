<?php
/**
 * Modelo: Módulo.
 *
 * Acceso a la tabla wp_lms_modules. Un módulo pertenece a un curso
 * (course_id) y tiene un orden (order_index) dentro de ese curso.
 *
 * @package TeemsLMS
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class LMS_Module {

	public static function table() {
		global $wpdb;
		return $wpdb->prefix . 'lms_modules';
	}

	/**
	 * Todos los módulos de un curso, ordenados por su order_index.
	 */
	public static function all_by_course( $course_id ) {
		global $wpdb;
		$tabla = self::table();
		return $wpdb->get_results(
			$wpdb->prepare( "SELECT * FROM {$tabla} WHERE course_id = %d ORDER BY order_index ASC, id ASC", absint( $course_id ) )
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
	 * Cuántos módulos tiene un curso.
	 */
	public static function count_by_course( $course_id ) {
		global $wpdb;
		$tabla = self::table();
		return (int) $wpdb->get_var(
			$wpdb->prepare( "SELECT COUNT(*) FROM {$tabla} WHERE course_id = %d", absint( $course_id ) )
		);
	}

	/**
	 * Siguiente número de orden disponible para un curso (max + 1).
	 */
	public static function next_order( $course_id ) {
		global $wpdb;
		$tabla = self::table();
		$max   = (int) $wpdb->get_var(
			$wpdb->prepare( "SELECT MAX(order_index) FROM {$tabla} WHERE course_id = %d", absint( $course_id ) )
		);
		return $max + 1;
	}

	/**
	 * Crea un módulo. $data = [ course_id, title, order_index ].
	 */
	public static function create( $data ) {
		global $wpdb;
		$ok = $wpdb->insert(
			self::table(),
			array(
				'course_id'   => absint( $data['course_id'] ),
				'title'       => $data['title'],
				'order_index' => absint( $data['order_index'] ),
			),
			array( '%d', '%s', '%d' )
		);
		return $ok ? (int) $wpdb->insert_id : 0;
	}

	/**
	 * Actualiza un módulo. $data = [ title, order_index ].
	 */
	public static function update( $id, $data ) {
		global $wpdb;
		$res = $wpdb->update(
			self::table(),
			array(
				'title'       => $data['title'],
				'order_index' => absint( $data['order_index'] ),
			),
			array( 'id' => absint( $id ) ),
			array( '%s', '%d' ),
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
