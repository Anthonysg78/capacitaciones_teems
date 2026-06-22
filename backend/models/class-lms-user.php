<?php
/**
 * Modelo: Usuarios del LMS (capa sobre los usuarios de WordPress).
 *
 * Un "usuario" del LMS es un usuario de WordPress con uno de nuestros roles:
 *   - lms_student              → estudiante que toma cursos
 *   - lms_admin / administrator → administra la plataforma
 *
 * Este modelo solo lee/escribe la CUENTA. La INSCRIPCIÓN a cursos vive en
 * LMS_Enrollment: la cuenta y el acceso a un curso son cosas separadas
 * (regla de negocio #1).
 *
 * @package TeammsLMS
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class LMS_User {

	/** Roles que se gestionan desde el panel. */
	private static function roles() {
		return array( 'lms_admin', 'lms_student', 'administrator' );
	}

	/**
	 * Lista de usuarios gestionables, con los datos que necesita la tabla:
	 * id, nombre, email, perfil (clave + etiqueta), alta y nº de cursos.
	 */
	public static function manageable() {
		$users = get_users( array(
			'role__in' => self::roles(),
			'orderby'  => 'registered',
			'order'    => 'DESC',
		) );
		$lista = array();
		foreach ( $users as $u ) {
			$lista[] = array(
				'id'        => (int) $u->ID,
				'nombre'    => $u->display_name ? $u->display_name : $u->user_login,
				'email'     => $u->user_email,
				'perfil'    => self::role_key( $u ),
				'perfil_lbl'=> self::role_label( $u ),
				'alta'      => $u->user_registered,
				'cursos'    => self::count_courses( (int) $u->ID ),
			);
		}
		return $lista;
	}

	public static function find( $id ) {
		$u = get_user_by( 'id', absint( $id ) );
		return $u ? $u : null;
	}

	/** ¿Es administrador (de WordPress o del LMS)? */
	public static function is_admin_user( $user ) {
		return user_can( $user, 'manage_options' ) || user_can( $user, 'lms_manage' );
	}

	/** Clave de perfil para uso interno: 'admin' | 'estudiante'. */
	public static function role_key( $user ) {
		return self::is_admin_user( $user ) ? 'admin' : 'estudiante';
	}

	/** Etiqueta legible del perfil. */
	public static function role_label( $user ) {
		return self::is_admin_user( $user ) ? 'Administrador' : 'Estudiante';
	}

	/** Nº de cursos en los que está inscrito (publicados o no). */
	public static function count_courses( $user_id ) {
		global $wpdb;
		$tabla = $wpdb->prefix . 'lms_enrollments';
		return (int) $wpdb->get_var(
			$wpdb->prepare( "SELECT COUNT(*) FROM {$tabla} WHERE user_id = %d", absint( $user_id ) )
		);
	}

	/** Cursos inscritos (objetos id/title/published), todos. */
	public static function enrolled_courses( $user_id ) {
		global $wpdb;
		$p = $wpdb->prefix . 'lms_';
		return $wpdb->get_results(
			$wpdb->prepare(
				"SELECT co.id, co.title, co.published
				 FROM {$p}enrollments e
				 INNER JOIN {$p}courses co ON e.course_id = co.id
				 WHERE e.user_id = %d
				 ORDER BY e.enrolled_at DESC",
				absint( $user_id )
			)
		);
	}

	/**
	 * Crea una cuenta. $role admitido: 'lms_student' (por defecto) o 'lms_admin'.
	 * Devuelve el ID nuevo (int) o un WP_Error (p. ej. email ya existe).
	 */
	public static function create( $nombre, $email, $pass, $role = 'lms_student' ) {
		if ( ! in_array( $role, array( 'lms_student', 'lms_admin' ), true ) ) {
			$role = 'lms_student';
		}
		return wp_insert_user( array(
			'user_login'   => $email,
			'user_email'   => $email,
			'user_pass'    => $pass,
			'display_name' => $nombre,
			'first_name'   => $nombre,
			'role'         => $role,
		) );
	}

	/**
	 * Borra la cuenta y limpia todos sus datos del LMS (inscripciones, progreso,
	 * intentos, respuestas, certificados, insignias y log). No deja huérfanos.
	 * Devuelve true si se borró el usuario.
	 */
	public static function delete( $user_id ) {
		$user_id = absint( $user_id );
		if ( ! $user_id ) {
			return false;
		}
		global $wpdb;
		$p = $wpdb->prefix . 'lms_';

		// Respuestas: no tienen user_id; cuelgan de los intentos del usuario.
		$wpdb->query(
			$wpdb->prepare(
				"DELETE FROM {$p}attempt_answers
				 WHERE attempt_id IN ( SELECT id FROM {$p}evaluation_attempts WHERE user_id = %d )",
				$user_id
			)
		);

		// Tablas que sí tienen user_id.
		foreach ( array( 'enrollments', 'content_progress', 'evaluation_attempts', 'certificates', 'user_badges', 'activity_log' ) as $t ) {
			$wpdb->delete( $p . $t, array( 'user_id' => $user_id ), array( '%d' ) );
		}

		// wp_delete_user vive en el área de administración; la cargamos si hace falta.
		if ( ! function_exists( 'wp_delete_user' ) ) {
			require_once ABSPATH . 'wp-admin/includes/user.php';
		}
		return (bool) wp_delete_user( $user_id );
	}
}
