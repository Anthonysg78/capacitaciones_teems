<?php
/**
 * Modelo: Curso.
 *
 * Única puerta de entrada a la tabla wp_lms_courses. Centraliza todas las
 * consultas (listar, buscar, crear, actualizar, borrar) usando $wpdb->prepare
 * para que sean seguras.
 *
 * @package TeammsLMS
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class LMS_Course {

	/**
	 * Nombre de la tabla con el prefijo de WordPress (ej. wp_lms_courses).
	 */
	public static function table() {
		global $wpdb;
		return $wpdb->prefix . 'lms_courses';
	}

	/**
	 * Devuelve todos los cursos, del más nuevo al más viejo.
	 */
	public static function all() {
		global $wpdb;
		$tabla = self::table();
		return $wpdb->get_results( "SELECT * FROM {$tabla} ORDER BY created_at DESC" );
	}

	/**
	 * Devuelve solo los cursos PUBLICADOS (los que ve el estudiante).
	 */
	public static function all_published() {
		global $wpdb;
		$tabla = self::table();
		return $wpdb->get_results( "SELECT * FROM {$tabla} WHERE published = 1 ORDER BY created_at DESC" );
	}

	/**
	 * Busca un curso por su id. Devuelve el objeto o null.
	 */
	public static function find( $id ) {
		global $wpdb;
		$tabla = self::table();
		return $wpdb->get_row(
			$wpdb->prepare( "SELECT * FROM {$tabla} WHERE id = %d", absint( $id ) )
		);
	}

	/**
	 * Cuenta cuántos cursos hay.
	 */
	public static function count() {
		global $wpdb;
		$tabla = self::table();
		return (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$tabla}" );
	}

	/**
	 * Crea un curso nuevo. Devuelve el id insertado (o 0 si falla).
	 *
	 * @param array $data [ title, description, published ]
	 */
	public static function create( $data ) {
		global $wpdb;
		$ok = $wpdb->insert(
			self::table(),
			array(
				'title'       => $data['title'],
				'description' => $data['description'],
				'published'   => $data['published'] ? 1 : 0,
			),
			array( '%s', '%s', '%d' ) // formatos: texto, texto, número.
		);
		return $ok ? (int) $wpdb->insert_id : 0;
	}

	/**
	 * Actualiza un curso existente. Devuelve true/false.
	 *
	 * @param int   $id
	 * @param array $data [ title, description, published ]
	 */
	public static function update( $id, $data ) {
		global $wpdb;
		$res = $wpdb->update(
			self::table(),
			array(
				'title'       => $data['title'],
				'description' => $data['description'],
				'published'   => $data['published'] ? 1 : 0,
			),
			array( 'id' => absint( $id ) ),
			array( '%s', '%s', '%d' ), // formatos de los datos.
			array( '%d' )              // formato del WHERE.
		);
		return false !== $res;
	}

	/**
	 * Borra un curso por id. Devuelve true/false.
	 */
	public static function delete( $id ) {
		global $wpdb;
		$res = $wpdb->delete( self::table(), array( 'id' => absint( $id ) ), array( '%d' ) );
		return false !== $res;
	}

	/* ====================================================================
	 *  CÓDIGO DE INVITACIÓN (link general del curso)
	 * ==================================================================== */

	/**
	 * Genera un código alfanumérico único para el link de invitación.
	 */
	private static function generar_token() {
		global $wpdb;
		$tabla = self::table();
		do {
			$token = wp_generate_password( 10, false ); // 10 caracteres, sin símbolos.
			$existe = $wpdb->get_var(
				$wpdb->prepare( "SELECT id FROM {$tabla} WHERE invite_token = %s LIMIT 1", $token )
			);
		} while ( $existe );
		return $token;
	}

	/**
	 * Devuelve el código de invitación del curso; si no tiene, lo crea y guarda.
	 */
	public static function ensure_token( $course_id ) {
		global $wpdb;
		$tabla = self::table();
		$id    = absint( $course_id );
		$token = $wpdb->get_var( $wpdb->prepare( "SELECT invite_token FROM {$tabla} WHERE id = %d", $id ) );
		if ( ! $token ) {
			$token = self::generar_token();
			$wpdb->update( $tabla, array( 'invite_token' => $token ), array( 'id' => $id ), array( '%s' ), array( '%d' ) );
		}
		return $token;
	}

	/**
	 * Cambia el código (invalida el link anterior). Devuelve el nuevo código.
	 */
	public static function regenerate_token( $course_id ) {
		global $wpdb;
		$token = self::generar_token();
		$wpdb->update( self::table(), array( 'invite_token' => $token ), array( 'id' => absint( $course_id ) ), array( '%s' ), array( '%d' ) );
		return $token;
	}

	/**
	 * Busca un curso por su código de invitación. Devuelve el objeto o null.
	 */
	public static function find_by_invite_token( $token ) {
		global $wpdb;
		$tabla = self::table();
		$token = sanitize_text_field( $token );
		if ( '' === $token ) {
			return null;
		}
		return $wpdb->get_row(
			$wpdb->prepare( "SELECT * FROM {$tabla} WHERE invite_token = %s LIMIT 1", $token )
		);
	}
}
