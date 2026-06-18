<?php
/**
 * Autenticación del LMS (login y logout propios, en el frontend).
 *
 * El usuario externo entra por la pantalla del LMS (correo + contraseña); por
 * dentro usamos la autenticación de WordPress (wp_signon), pero la persona
 * nunca ve WordPress ni /wp-login.php.
 *
 * @package TeammsLMS
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class LMS_Auth_Actions {

	public function __construct() {
		// 'template_redirect' corre antes de imprimir HTML, así que podemos
		// fijar la cookie de sesión y redirigir sin "headers already sent".
		add_action( 'template_redirect', array( $this, 'handle' ) );
	}

	public function handle() {
		if ( is_admin() ) {
			return;
		}
		if ( isset( $_POST['lms_action'] ) && 'lms_login' === $_POST['lms_action'] ) {
			$this->login();
			return;
		}
		if ( isset( $_GET['lms_action'] ) && 'lms_logout' === $_GET['lms_action'] ) {
			$this->logout();
		}
	}

	private function login() {
		$nonce  = isset( $_POST['_wpnonce'] ) ? sanitize_text_field( wp_unslash( $_POST['_wpnonce'] ) ) : '';
		$base   = isset( $_POST['redirect'] ) ? esc_url_raw( wp_unslash( $_POST['redirect'] ) ) : home_url( '/' );
		$invite = sanitize_text_field( wp_unslash( $_POST['invite'] ?? '' ) );

		if ( ! wp_verify_nonce( $nonce, 'lms_login' ) ) {
			$this->volver_login( $base, 'expired', $invite );
		}

		// El "correo" puede ser email o usuario; WordPress acepta ambos. La
		// contraseña NO se sanitiza (puede tener símbolos), solo se quita slashes.
		$creds = array(
			'user_login'    => sanitize_text_field( wp_unslash( $_POST['email'] ?? '' ) ),
			'user_password' => (string) wp_unslash( $_POST['password'] ?? '' ),
			'remember'      => ! empty( $_POST['remember'] ),
		);

		if ( '' === $creds['user_login'] || '' === $creds['user_password'] ) {
			$this->volver_login( $base, '1', $invite );
		}

		$user = wp_signon( $creds, is_ssl() );
		if ( is_wp_error( $user ) ) {
			$this->volver_login( $base, '1', $invite );
		}

		// Si venía con un link de invitación a un curso, lo inscribimos y lo
		// llevamos directo a ese curso.
		$limpia = remove_query_arg( array( 'vista', 'err', 'lms_action', 'invite' ), $base );
		if ( $invite ) {
			$cid = LMS_Enrollment::enroll_from_token( $user->ID, $invite );
			if ( $cid ) {
				wp_safe_redirect( add_query_arg( array( 'vista' => 'curso', 'id' => $cid ), $limpia ) );
				exit;
			}
		}

		// Sesión iniciada: entramos al LMS (sin parámetros de login).
		wp_safe_redirect( $limpia );
		exit;
	}

	private function logout() {
		$base = isset( $_GET['redirect'] ) ? esc_url_raw( wp_unslash( $_GET['redirect'] ) ) : home_url( '/' );
		wp_logout();
		wp_safe_redirect( add_query_arg( 'vista', 'login', remove_query_arg( array( 'lms_action', 'err' ), $base ) ) );
		exit;
	}

	/** Vuelve a la pantalla de login con un código de error (conserva la invitación). */
	private function volver_login( $base, $err, $invite = '' ) {
		$args = array( 'vista' => 'login', 'err' => $err );
		if ( $invite ) {
			$args['invite'] = $invite;
		}
		wp_safe_redirect( add_query_arg( $args, remove_query_arg( array( 'lms_action' ), $base ) ) );
		exit;
	}
}
