<?php
/**
 * Modelo: Certificado de CURSO.
 *
 * Cuando un estudiante COMPLETA un curso (aprueba la evaluación de todos sus
 * módulos) se le emite UN certificado con un código único (UUID) verificable
 * públicamente. Las evaluaciones siguen siendo por módulo, pero el certificado
 * es uno solo por curso.
 *
 * Tabla wp_lms_certificates: id, user_id, course_id, issued_at, unique_code,
 * pdf_url, public_url.
 *
 * @package TeammsLMS
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class LMS_Certificate {

	public static function table() {
		global $wpdb;
		return $wpdb->prefix . 'lms_certificates';
	}

	/**
	 * ¿Ya existe certificado para este usuario y curso? Devuelve el código o ''.
	 */
	public static function existing_code( $user_id, $course_id ) {
		global $wpdb;
		$tabla = self::table();
		$code  = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT unique_code FROM {$tabla} WHERE user_id = %d AND course_id = %d LIMIT 1",
				absint( $user_id ),
				absint( $course_id )
			)
		);
		return $code ? (string) $code : '';
	}

	/**
	 * Emite el certificado del curso si aún no existe. Devuelve el código único.
	 * Idempotente: si ya existía, devuelve el mismo código.
	 */
	public static function issue( $user_id, $course_id ) {
		$existente = self::existing_code( $user_id, $course_id );
		if ( '' !== $existente ) {
			return $existente;
		}
		global $wpdb;
		$code = wp_generate_uuid4(); // 36 caracteres.
		$wpdb->insert(
			self::table(),
			array(
				'user_id'     => absint( $user_id ),
				'course_id'   => absint( $course_id ),
				'unique_code' => $code,
			),
			array( '%d', '%d', '%s' )
		);
		return $code;
	}

	/**
	 * Certificados de un usuario, con el título del curso (para la lista).
	 */
	public static function all_for_user( $user_id ) {
		global $wpdb;
		$p = $wpdb->prefix . 'lms_';
		return $wpdb->get_results(
			$wpdb->prepare(
				"SELECT cert.*, co.title AS course_title
				 FROM {$p}certificates cert
				 INNER JOIN {$p}courses co ON cert.course_id = co.id
				 WHERE cert.user_id = %d
				 ORDER BY cert.issued_at DESC",
				absint( $user_id )
			)
		);
	}

	/**
	 * Detalle completo de un certificado por su código, para mostrar/verificar.
	 * Devuelve un objeto con datos del curso, fecha y nombre del alumno
	 * (o null si el código no existe).
	 */
	public static function details_by_code( $code ) {
		global $wpdb;
		$p   = $wpdb->prefix . 'lms_';
		$row = $wpdb->get_row(
			$wpdb->prepare(
				"SELECT cert.*, co.title AS course_title
				 FROM {$p}certificates cert
				 INNER JOIN {$p}courses co ON cert.course_id = co.id
				 WHERE cert.unique_code = %s
				 LIMIT 1",
				sanitize_text_field( $code )
			)
		);
		if ( ! $row ) {
			return null;
		}
		$user              = get_userdata( (int) $row->user_id );
		$row->student_name = ( $user && $user->display_name ) ? $user->display_name : 'Estudiante';
		return $row;
	}
}
