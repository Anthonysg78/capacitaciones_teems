<?php
/**
 * Modelo: Contenido de un subtema.
 *
 * Acceso a la tabla wp_lms_contents. Un contenido pertenece a un subtema
 * (subtopic_id) y tiene un tipo: texto, video, pdf o recurso.
 *
 *   - texto   → se guarda en content_text (el cuerpo de la información).
 *   - video   → content_url (enlace de YouTube/Vimeo).
 *   - pdf     → content_url (enlace al PDF de apoyo).
 *   - recurso → content_url (enlace a un recurso externo).
 *
 * @package TeemsLMS
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
	 * Todos los contenidos de un subtema, ordenados.
	 */
	public static function all_by_subtopic( $subtopic_id ) {
		global $wpdb;
		$tabla = self::table();
		return $wpdb->get_results(
			$wpdb->prepare( "SELECT * FROM {$tabla} WHERE subtopic_id = %d ORDER BY order_index ASC, id ASC", absint( $subtopic_id ) )
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
	 * Siguiente orden disponible para un subtema (max + 1).
	 */
	public static function next_order( $subtopic_id ) {
		global $wpdb;
		$tabla = self::table();
		$max   = (int) $wpdb->get_var(
			$wpdb->prepare( "SELECT MAX(order_index) FROM {$tabla} WHERE subtopic_id = %d", absint( $subtopic_id ) )
		);
		return $max + 1;
	}

	/**
	 * Crea un contenido.
	 * $data = [ subtopic_id, type, title, content_text, content_url, order_index ].
	 */
	public static function create( $data ) {
		global $wpdb;
		$ok = $wpdb->insert(
			self::table(),
			array(
				'subtopic_id'  => absint( $data['subtopic_id'] ),
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
