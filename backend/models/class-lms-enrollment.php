<?php
/**
 * Modelo: Inscripción de un estudiante a un curso.
 *
 * Tabla wp_lms_enrollments: una fila = "este usuario está inscrito en este
 * curso". El estudiante solo ve y entra a los cursos en los que está inscrito.
 * La inscripción ocurre al abrir el LINK de invitación del curso.
 *
 * @package TeammsLMS
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class LMS_Enrollment {

	public static function table() {
		global $wpdb;
		return $wpdb->prefix . 'lms_enrollments';
	}

	/**
	 * ¿El usuario está inscrito en el curso?
	 */
	public static function is_enrolled( $user_id, $course_id ) {
		global $wpdb;
		$tabla = self::table();
		$row   = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT id FROM {$tabla} WHERE user_id = %d AND course_id = %d LIMIT 1",
				absint( $user_id ),
				absint( $course_id )
			)
		);
		return ! empty( $row );
	}

	/**
	 * Inscribe al usuario en el curso (si no lo estaba ya). Idempotente.
	 * Devuelve true si quedó inscrito. 
	 */
	public static function enroll( $user_id, $course_id ) {
		$user_id   = absint( $user_id );
		$course_id = absint( $course_id );
		if ( ! $user_id || ! $course_id ) {
			return false;
		}
		if ( self::is_enrolled( $user_id, $course_id ) ) {
			return true;
		}
		global $wpdb;
		$wpdb->insert(
			self::table(),
			array( 'user_id' => $user_id, 'course_id' => $course_id ),
			array( '%d', '%d' )
		);
		return true;
	}

	/**
	 * Inscribe a partir del CÓDIGO de invitación de un curso.
	 * Devuelve el id del curso si funcionó, o 0 si el código no es válido.
	 */
	public static function enroll_from_token( $user_id, $token ) {
		$curso = LMS_Course::find_by_invite_token( $token );
		if ( ! $curso ) {
			return 0;
		}
		self::enroll( $user_id, (int) $curso->id );
		return (int) $curso->id;
	}

	/**
	 * Cursos PUBLICADOS en los que el usuario está inscrito (objetos de curso).
	 */
	public static function courses_for_user( $user_id ) {
		global $wpdb;
		$p = $wpdb->prefix . 'lms_';
		return $wpdb->get_results(
			$wpdb->prepare(
				"SELECT co.*
				 FROM {$p}enrollments e
				 INNER JOIN {$p}courses co ON e.course_id = co.id
				 WHERE e.user_id = %d AND co.published = 1
				 ORDER BY e.enrolled_at DESC",
				absint( $user_id )
			)
		);
	}
}
