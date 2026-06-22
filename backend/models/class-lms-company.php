<?php
/**
 * Modelo: Empresa (agrupación de estudiantes, SIN login ni panel).
 *
 * Una empresa es solo una "etiqueta/grupo" que el admin asigna a cada
 * estudiante. La pertenencia NO vive aquí: se guarda en el user_meta
 * 'lms_company_id' de cada estudiante (una empresa por estudiante).
 *
 * Única puerta de entrada a la tabla wp_lms_companies. Mismo patrón que
 * LMS_Course / LMS_User: consultas con $wpdb->prepare.
 *
 * @package TeammsLMS
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class LMS_Company {

	/** Clave del user_meta donde el estudiante guarda su empresa. */
	const META_KEY = 'lms_company_id';

	/** Nombre de la tabla con prefijo (ej. wp_lms_companies). */
	public static function table() {
		global $wpdb;
		return $wpdb->prefix . 'lms_companies';
	}

	/** Todas las empresas, de la más nueva a la más vieja. */
	public static function all() {
		global $wpdb;
		$tabla = self::table();
		return $wpdb->get_results( "SELECT * FROM {$tabla} ORDER BY name ASC" );
	}

	/** Busca una empresa por id. Devuelve el objeto o null. */
	public static function find( $id ) {
		global $wpdb;
		$tabla = self::table();
		return $wpdb->get_row(
			$wpdb->prepare( "SELECT * FROM {$tabla} WHERE id = %d", absint( $id ) )
		);
	}

	/** Nombre de la empresa (o '' si no existe / no tiene). */
	public static function name_of( $id ) {
		$id = absint( $id );
		if ( ! $id ) {
			return '';
		}
		$empresa = self::find( $id );
		return $empresa ? $empresa->name : '';
	}

	/** Cuántas empresas hay. */
	public static function count() {
		global $wpdb;
		$tabla = self::table();
		return (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$tabla}" );
	}

	/** Crea una empresa. Devuelve el id nuevo (o 0 si falla). */
	public static function create( $name ) {
		global $wpdb;
		$ok = $wpdb->insert( self::table(), array( 'name' => $name ), array( '%s' ) );
		return $ok ? (int) $wpdb->insert_id : 0;
	}

	/** Renombra una empresa. Devuelve true/false. */
	public static function update( $id, $name ) {
		global $wpdb;
		$res = $wpdb->update(
			self::table(),
			array( 'name' => $name ),
			array( 'id' => absint( $id ) ),
			array( '%s' ),
			array( '%d' )
		);
		return false !== $res;
	}

	/**
	 * Borra una empresa y, además, la desasigna de los estudiantes que la tenían
	 * (no deja referencias huérfanas en el user_meta). Devuelve true/false.
	 */
	public static function delete( $id ) {
		global $wpdb;
		$id = absint( $id );
		if ( ! $id ) {
			return false;
		}
		// Quitar la empresa a todos los estudiantes que la tenían asignada.
		$wpdb->delete( $wpdb->usermeta, array( 'meta_key' => self::META_KEY, 'meta_value' => $id ), array( '%s', '%d' ) );
		$res = $wpdb->delete( self::table(), array( 'id' => $id ), array( '%d' ) );
		return false !== $res;
	}

	/**
	 * Estudiantes asignados a una empresa, con los datos que necesita la tabla:
	 * id, nombre, email y nº de cursos. Ordenados por nombre.
	 */
	public static function students( $id ) {
		$users = get_users( array(
			'meta_key'   => self::META_KEY,
			'meta_value' => absint( $id ),
			'orderby'    => 'display_name',
			'order'      => 'ASC',
		) );
		$lista = array();
		foreach ( $users as $u ) {
			$lista[] = array(
				'id'     => (int) $u->ID,
				'nombre' => $u->display_name ? $u->display_name : $u->user_login,
				'email'  => $u->user_email,
				'cursos' => class_exists( 'LMS_User' ) ? LMS_User::count_courses( (int) $u->ID ) : 0,
			);
		}
		return $lista;
	}

	/** Nº de estudiantes asignados a una empresa. */
	public static function count_students( $id ) {
		global $wpdb;
		return (int) $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM {$wpdb->usermeta} WHERE meta_key = %s AND meta_value = %d",
				self::META_KEY,
				absint( $id )
			)
		);
	}

	/** Empresa asignada a un usuario (id) o 0 si no tiene. */
	public static function of_user( $user_id ) {
		return (int) get_user_meta( absint( $user_id ), self::META_KEY, true );
	}

	/**
	 * Asigna (o quita) la empresa de un usuario. company_id = 0 → la quita.
	 */
	public static function assign( $user_id, $company_id ) {
		$user_id    = absint( $user_id );
		$company_id = absint( $company_id );
		if ( ! $user_id ) {
			return;
		}
		if ( $company_id && self::find( $company_id ) ) {
			update_user_meta( $user_id, self::META_KEY, $company_id );
		} else {
			delete_user_meta( $user_id, self::META_KEY );
		}
	}
}
