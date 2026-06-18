<?php
/**
 * Modelo: Contenido de un módulo.
 *
 * Acceso a la tabla wp_lms_contents. Un contenido pertenece a un MÓDULO
 * (module_id) y tiene un tipo: texto, video, pdf o recurso.
 *
 *   - texto   → se guarda en content_text (el cuerpo de la información).
 *   - video   → content_url (enlace de YouTube/Vimeo).
 *   - pdf     → content_url (archivo subido a la Biblioteca de Medios o enlace).
 *   - recurso → content_url (archivo subido o enlace externo).
 *
 * @package TeammsLMS
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class LMS_Content {

	/**
	 * Tipos válidos de contenido => etiqueta legible.
	 * Fuente única de verdad: la usan el formulario, la lista y la validación.
	 */
	public static function tipos() {
		return array(
			'texto'   => 'Texto / Información',
			'video'   => 'Video',
			'pdf'     => 'PDF de apoyo',
			'recurso' => 'Recurso / Enlace',
		);
	}

	public static function table() {
		global $wpdb;
		return $wpdb->prefix . 'lms_contents';
	}

	/**
	 * Todos los contenidos de un módulo, ordenados.
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
	 * Cuántos contenidos tiene un curso (sumando todos sus módulos).
	 */
	public static function count_by_course( $course_id ) {
		global $wpdb;
		$p = $wpdb->prefix . 'lms_';
		return (int) $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*)
				 FROM {$p}contents c
				 INNER JOIN {$p}modules m ON c.module_id = m.id
				 WHERE m.course_id = %d",
				absint( $course_id )
			)
		);
	}

	/**
	 * Crea un contenido.
	 * $data = [ module_id, type, title, content_text, content_url, order_index ].
	 */
	public static function create( $data ) {
		global $wpdb;
		$ok = $wpdb->insert(
			self::table(),
			array(
				'module_id'    => absint( $data['module_id'] ),
				'type'         => $data['type'],
				'title'        => $data['title'],
				'content_text' => $data['content_text'],
				'content_url'  => $data['content_url'],
				'order_index'  => absint( $data['order_index'] ),
			),
			array( '%d', '%s', '%s', '%s', '%s', '%d' )
		);
		return $ok ? (int) $wpdb->insert_id : 0;
	}

	/**
	 * Actualiza un contenido.
	 * $data = [ type, title, content_text, content_url, order_index ].
	 */
	public static function update( $id, $data ) {
		global $wpdb;
		$res = $wpdb->update(
			self::table(),
			array(
				'type'         => $data['type'],
				'title'        => $data['title'],
				'content_text' => $data['content_text'],
				'content_url'  => $data['content_url'],
				'order_index'  => absint( $data['order_index'] ),
			),
			array( 'id' => absint( $id ) ),
			array( '%s', '%s', '%s', '%s', '%d' ),
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
