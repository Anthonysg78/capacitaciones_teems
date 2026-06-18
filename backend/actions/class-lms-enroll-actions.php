<?php
/**
 * Acciones de inscripción a cursos por LINK de invitación.
 *
 *  - Abrir ?invite=CODIGO con sesión iniciada  → inscribe y va al curso.
 *  - Registro de estudiante (lms_register)     → crea cuenta + inicia sesión + inscribe.
 *  - regen_invite (admin)                       → cambia el código del curso.
 *
 * El registro/login con un código de invitación pendiente inscribe al usuario
 * en ese curso automáticamente.
 *
 * @package TeammsLMS
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class LMS_Enroll_Actions {

	public function __construct() {
		add_action( 'template_redirect', array( $this, 'handle' ) );
	}

	public function handle() {
		if ( is_admin() ) {
			return;
		}
		if ( isset( $_GET['lms_action'] ) && 'regen_invite' === $_GET['lms_action'] ) {
			$this->regen();
			return;
		}
		if ( isset( $_POST['lms_action'] ) && 'lms_register' === $_POST['lms_action'] ) {
			$this->register();
			return;
		}
		// Abrir el link de invitación ya con sesión → inscribir directo.
		if ( isset( $_GET['invite'] ) && is_user_logged_in() ) {
			$token = sanitize_text_field( wp_unslash( $_GET['invite'] ) );
			$cid   = LMS_Enrollment::enroll_from_token( get_current_user_id(), $token );
			$limpia = remove_query_arg( array( 'invite', 'lms_action', 'err' ) );
			if ( $cid ) {
				wp_safe_redirect( add_query_arg( array( 'vista' => 'curso', 'id' => $cid ), $limpia ) );
			} else {
				wp_safe_redirect( add_query_arg( 'invite_err', '1', $limpia ) );
			}
			exit;
		}
	}

	/**
	 * Registro de un estudiante nuevo (desde la pantalla de invitación).
	 */
	private function register() {
		$base = isset( $_POST['redirect'] ) ? esc_url_raw( wp_unslash( $_POST['redirect'] ) ) : home_url( '/' );
		$nonce = isset( $_POST['_wpnonce'] ) ? sanitize_text_field( wp_unslash( $_POST['_wpnonce'] ) ) : '';
		if ( ! wp_verify_nonce( $nonce, 'lms_register' ) ) {
			$this->volver( $base, 'expired' );
		}

		$nombre = sanitize_text_field( wp_unslash( $_POST['name'] ?? '' ) );
		$email  = sanitize_email( wp_unslash( $_POST['email'] ?? '' ) );
		$pass   = (string) wp_unslash( $_POST['password'] ?? '' );
		$invite = sanitize_text_field( wp_unslash( $_POST['invite'] ?? '' ) );

		// Validaciones básicas.
		if ( '' === $nombre || ! is_email( $email ) || strlen( $pass ) < 6 ) {
			$this->volver( $base, 'datos', $invite );
		}
		// Si el correo ya tiene cuenta, que inicie sesión en su lugar.
		if ( email_exists( $email ) ) {
			$this->volver( $base, 'existe', $invite );
		}

		$user_id = wp_insert_user( array(
			'user_login'   => $email,
			'user_email'   => $email,
			'user_pass'    => $pass,
			'display_name' => $nombre,
			'first_name'   => $nombre,
			'role'         => 'lms_student',
		) );

		if ( is_wp_error( $user_id ) ) {
			$this->volver( $base, 'datos', $invite );
		}

		// Iniciar sesión con la cuenta recién creada.
		wp_set_current_user( $user_id );
		wp_set_auth_cookie( $user_id, true );

		// Inscribir en el curso del link (si vino uno).
		$cid = $invite ? LMS_Enrollment::enroll_from_token( $user_id, $invite ) : 0;

		$limpia = remove_query_arg( array( 'vista', 'err', 'invite', 'lms_action' ), $base );
		if ( $cid ) {
			wp_safe_redirect( add_query_arg( array( 'vista' => 'curso', 'id' => $cid ), $limpia ) );
		} else {
			wp_safe_redirect( $limpia );
		}
		exit;
	}

	/**
	 * Admin: regenerar el código de invitación de un curso (invalida el anterior).
	 */
	private function regen() {
		$id    = isset( $_GET['id'] ) ? absint( $_GET['id'] ) : 0;
		$nonce = isset( $_GET['_wpnonce'] ) ? sanitize_text_field( wp_unslash( $_GET['_wpnonce'] ) ) : '';
		$base  = isset( $_GET['redirect'] ) ? esc_url_raw( wp_unslash( $_GET['redirect'] ) ) : home_url( '/' );
		if ( $id && wp_verify_nonce( $nonce, 'lms_regen_invite_' . $id ) && current_user_can( 'lms_manage' ) ) {
			LMS_Course::regenerate_token( $id );
		}
		wp_safe_redirect( add_query_arg( 'msg', 'invite', $base ) );
		exit;
	}

	/** Vuelve a la pantalla de CREAR CUENTA con un mensaje (conserva la invitación). */
	private function volver( $base, $err, $invite = '' ) {
		$args = array( 'vista' => 'registro', 'err' => $err );
		if ( $invite ) {
			$args['invite'] = $invite;
		}
		wp_safe_redirect( add_query_arg( $args, remove_query_arg( array( 'lms_action' ), $base ) ) );
		exit;
	}
}
