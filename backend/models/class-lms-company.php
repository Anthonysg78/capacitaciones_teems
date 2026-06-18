<?php
/**
 * Modelo: Empresa.
 *
 * Única puerta de entrada a la tabla wp_lms_companies. Centraliza todas las
 * consultas (listar, buscar, crear, actualizar, borrar) usando $wpdb->prepare
 * para que sean seguras. Mismo patrón que LMS_Course.
 *
 * @package TeammsLMS
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class LMS_Company {

	/**
	 * Nombre de la tabla con el prefijo de WordPress (ej. wp_lms_companies).
	 */
	public static function table() {
		global $wpdb;
		return $wpdb->prefix . 'lms_companies';
	}

	/**
	 * Devuelve todas las empresas, de la más nueva a la más vieja.
	 */
	public static function all() {
		global $wpdb;
		$tabla = self::table();
		return $wpdb->get_results( "SELECT * FROM {$tabla} ORDER BY created_at DESC" );
	}

	/**
	 * Busca una empresa por su id. Devuelve el objeto o null.
	 */
	public static function find( $id ) {
		global $wpdb;
		$tabla = self::table();
		return $wpdb->get_row(
			$wpdb->prepare( "SELECT * FROM {$tabla} WHERE id = %d", absint( $id ) )
		);
	}

	/**
	 * Cuenta cuántas empresas hay.
	 */
	public static function count() {
		global $wpdb;
		$tabla = self::table();
		return (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$tabla}" );
	}

	/**
	 * Crea una empresa nueva. Devuelve el id insertado (o 0 si falla).
	 *
	 * @param array $data [ name, contact_email, logo_url, active ]
	 */
	public static function create( $data ) {
		global $wpdb;
		$ok = $wpdb->insert(
			self::table(),
			array(
				'name'          => $data['name'],
				'contact_email' => $data['contact_email'] ?? '',
				'logo_url'      => $data['logo_url'] ?? '',
				'active'        => ! empty( $data['active'] ) ? 1 : 0,
			),
			array( '%s', '%s', '%s', '%d' )
		);
		return $ok ? (int) $wpdb->insert_id : 0;
	}

	/**
	 * Actualiza una empresa existente. Devuelve true/false.
	 *
	 * @param int   $id
	 * @param array $data [ name, contact_email, logo_url, active ]
	 */
	public static function update( $id, $data ) {
		global $wpdb;
		$res = $wpdb->update(
			self::table(),
			array(
				'name'          => $data['name'],
				'contact_email' => $data['contact_email'] ?? '',
				'logo_url'      => $data['logo_url'] ?? '',
				'active'        => ! empty( $data['active'] ) ? 1 : 0,
			),
			array( 'id' => absint( $id ) ),
			array( '%s', '%s', '%s', '%d' ),
			array( '%d' )
		);
		return false !== $res;
	}

	/**
	 * Borra una empresa por id. Devuelve true/false.
	 */
	public static function delete( $id ) {
		global $wpdb;
		$res = $wpdb->delete( self::table(), array( 'id' => absint( $id ) ), array( '%d' ) );
		return false !== $res;
	}
}
