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
		// Confirmación de correo: el estudiante escribe el código que recibió y se crea la cuenta.
		if ( isset( $_POST['lms_action'] ) && 'lms_verify_code' === $_POST['lms_action'] ) {
			$this->verify_code();
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
	 * Prefijo del registro pendiente y vigencia del código (30 min). El número
	 * máximo de intentos evita que adivinen el código a fuerza bruta.
	 */
	const PENDING_PREFIX = 'lms_pending_';
	const CODE_TTL       = 30 * MINUTE_IN_SECONDS;
	const CODE_INTENTOS  = 5;

	/** Clave del transient para un correo dado (no expone el correo en claro). */
	private function pending_key( $email ) {
		return self::PENDING_PREFIX . md5( strtolower( $email ) );
	}

	/**
	 * Registro de un estudiante nuevo: NO crea la cuenta todavía. Guarda los datos
	 * como pendientes, envía un código por correo y manda a la pantalla donde lo
	 * escribe. La cuenta se crea en verify_code(), cuando el código coincide.
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

		// Código de 6 dígitos. Guardamos la contraseña ya HASHEADA (nunca en claro).
		$codigo = str_pad( (string) random_int( 0, 999999 ), 6, '0', STR_PAD_LEFT );
		set_transient(
			$this->pending_key( $email ),
			array(
				'nombre'    => $nombre,
				'email'     => $email,
				'pass_hash' => wp_hash_password( $pass ),
				'invite'    => $invite,
				'codigo'    => $codigo,
				'intentos'  => 0,
			),
			self::CODE_TTL
		);

		$this->enviar_correo_codigo( $email, $nombre, $codigo );

		// Pantalla para escribir el código (lleva el correo para reconocer el pendiente).
		$limpia = remove_query_arg( array( 'err', 'lms_action' ), $base );
		$args   = array( 'vista' => 'confirmar', 'email' => $email );
		if ( $invite ) {
			$args['invite'] = $invite;
		}
		wp_safe_redirect( add_query_arg( $args, $limpia ) );
		exit;
	}

	/**
	 * Confirmación: el estudiante escribió el código. Si coincide, crea la cuenta,
	 * inicia sesión e inscribe en el curso de la invitación (si la había).
	 */
	private function verify_code() {
		$base  = isset( $_POST['redirect'] ) ? esc_url_raw( wp_unslash( $_POST['redirect'] ) ) : home_url( '/' );
		$nonce = isset( $_POST['_wpnonce'] ) ? sanitize_text_field( wp_unslash( $_POST['_wpnonce'] ) ) : '';
		if ( ! wp_verify_nonce( $nonce, 'lms_verify_code' ) ) {
			$this->volver( $base, 'expired' );
		}

		$email  = sanitize_email( wp_unslash( $_POST['email'] ?? '' ) );
		$codigo = preg_replace( '/\D/', '', (string) wp_unslash( $_POST['code'] ?? '' ) );
		$invite = sanitize_text_field( wp_unslash( $_POST['invite'] ?? '' ) );
		$datos  = $email ? get_transient( $this->pending_key( $email ) ) : false;

		// No hay registro pendiente o ya caducó (30 min) → vuelve a empezar.
		if ( ! is_array( $datos ) || empty( $datos['codigo'] ) ) {
			$this->a_confirmar( $base, $email, 'expirado', $invite );
		}

		// Demasiados intentos: anulamos el pendiente.
		if ( (int) $datos['intentos'] >= self::CODE_INTENTOS ) {
			delete_transient( $this->pending_key( $email ) );
			$this->volver( $base, 'token', $invite );
		}

		// Código incorrecto: sumamos intento y volvemos a pedirlo.
		if ( ! hash_equals( (string) $datos['codigo'], (string) $codigo ) ) {
			$datos['intentos'] = (int) $datos['intentos'] + 1;
			set_transient( $this->pending_key( $email ), $datos, self::CODE_TTL );
			$this->a_confirmar( $base, $email, 'codigo', $invite );
		}

		// Código correcto: el pendiente ya cumplió su función.
		delete_transient( $this->pending_key( $email ) );

		// Por si entre el registro y la confirmación ya se creó esa cuenta.
		if ( email_exists( $datos['email'] ) ) {
			$this->volver( $base, 'existe', $invite );
		}

		// Crear la cuenta. wp_insert_user vuelve a hashear, así que insertamos con
		// una contraseña temporal y luego fijamos el hash original directamente.
		$user_id = wp_insert_user( array(
			'user_login'   => $datos['email'],
			'user_email'   => $datos['email'],
			'user_pass'    => wp_generate_password( 24, true ),
			'display_name' => $datos['nombre'],
			'first_name'   => $datos['nombre'],
			'role'         => 'lms_student',
		) );

		if ( is_wp_error( $user_id ) ) {
			$this->volver( $base, 'datos', $invite );
		}

		global $wpdb;
		$wpdb->update( $wpdb->users, array( 'user_pass' => $datos['pass_hash'] ), array( 'ID' => $user_id ) );
		clean_user_cache( $user_id );

		// Iniciar sesión con la cuenta recién confirmada.
		wp_set_current_user( $user_id );
		wp_set_auth_cookie( $user_id, true );

		// Inscribir en el curso del link (si vino uno).
		$limpia = remove_query_arg( array( 'vista', 'err', 'invite', 'lms_action', 'email' ), $base );
		$cid    = ! empty( $datos['invite'] ) ? LMS_Enrollment::enroll_from_token( $user_id, $datos['invite'] ) : 0;
		if ( $cid ) {
			wp_safe_redirect( add_query_arg( array( 'vista' => 'curso', 'id' => $cid ), $limpia ) );
		} else {
			wp_safe_redirect( $limpia );
		}
		exit;
	}

	/**
	 * Envía el correo con el código de confirmación. Usa wp_mail(); si el sitio no
	 * tiene SMTP configurado, el correo NO sale (ver guía de instalación de SMTP).
	 */
	private function enviar_correo_codigo( $email, $nombre, $codigo ) {
		$sitio  = wp_specialchars_decode( get_bloginfo( 'name' ), ENT_QUOTES );
		$asunto = sprintf( '%s: tu código de confirmación', $sitio );
		$cuerpo = sprintf(
			"Hola %s:\n\n" .
			"Tu código para crear tu cuenta en %s es:\n\n    %s\n\n" .
			"Escríbelo en la pantalla de confirmación. El código vence en 30 minutos.\n" .
			"Si no fuiste tú, ignora este mensaje: la cuenta no se creará.\n",
			$nombre,
			$sitio,
			$codigo
		);
		wp_mail( $email, $asunto, $cuerpo );
	}

	/** Vuelve a la pantalla de ESCRIBIR EL CÓDIGO con un mensaje (conserva correo e invitación). */
	private function a_confirmar( $base, $email, $err, $invite = '' ) {
		$args = array( 'vista' => 'confirmar', 'err' => $err, 'email' => $email );
		if ( $invite ) {
			$args['invite'] = $invite;
		}
		wp_safe_redirect( add_query_arg( $args, remove_query_arg( array( 'lms_action' ), $base ) ) );
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
