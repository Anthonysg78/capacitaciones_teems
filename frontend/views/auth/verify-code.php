<?php
/**
 * Vista: ESCRIBIR EL CÓDIGO de confirmación.
 *
 * Tras registrarse, el estudiante llega aquí con su correo. Escribe el código de
 * 6 dígitos que recibió por email; si coincide, se crea la cuenta (verify_code()).
 *
 * Variables recibidas:
 *   $base        string       URL de la página del LMS (a donde postear / volver).
 *   $invite      string        código de invitación (o '' si no hay).
 *   $curso       object|null   curso de la invitación (para contexto) o null.
 *   $email_pend  string        correo al que se envió el código.
 *
 * @package TeammsLMS
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$err = isset( $_GET['err'] ) ? sanitize_key( wp_unslash( $_GET['err'] ) ) : '';

// Volver a crear cuenta (conservando la invitación si la hay).
$args_reg = array( 'vista' => 'registro' );
if ( ! empty( $invite ) ) {
	$args_reg['invite'] = $invite;
}
$crear_url = add_query_arg( $args_reg, $base );
?>
<div class="lms-login">
	<div class="lms-login__box">
		<div class="lms-login__brand">
			<span class="lms-login__logo"><i class="bi bi-mortarboard-fill"></i></span>
			Capacitaciones <strong>Teamms</strong>
		</div>

		<h1 class="lms-login__title">Confirma tu correo</h1>
		<p class="lms-login__sub">
			Te enviamos un código a
			<strong><?php echo esc_html( $email_pend ? $email_pend : 'tu correo' ); ?></strong>.
			Escríbelo aquí para activar tu cuenta.
		</p>

		<?php if ( 'codigo' === $err ) : ?>
			<div class="lms-notice lms-notice--err"><i class="bi bi-exclamation-triangle"></i> El código no es correcto. Revísalo e inténtalo de nuevo.</div>
		<?php elseif ( 'expirado' === $err ) : ?>
			<div class="lms-notice lms-notice--err"><i class="bi bi-clock-history"></i> El código venció o no encontramos tu registro. Crea tu cuenta de nuevo.</div>
		<?php elseif ( 'expired' === $err ) : ?>
			<div class="lms-notice lms-notice--err"><i class="bi bi-clock-history"></i> El formulario expiró. Inténtalo de nuevo.</div>
		<?php endif; ?>

		<form class="lms-loginform" method="post" action="<?php echo esc_url( $base ); ?>">
			<input type="hidden" name="lms_action" value="lms_verify_code">
			<input type="hidden" name="email" value="<?php echo esc_attr( $email_pend ); ?>">
			<input type="hidden" name="invite" value="<?php echo esc_attr( $invite ); ?>">
			<input type="hidden" name="redirect" value="<?php echo esc_url( $base ); ?>">
			<?php wp_nonce_field( 'lms_verify_code' ); ?>

			<div class="lms-field">
				<label for="lms-code">Código de 6 dígitos</label>
				<input type="text" id="lms-code" name="code" required inputmode="numeric" pattern="[0-9]{6}" maxlength="6" autocomplete="one-time-code" placeholder="••••••" autofocus>
			</div>

			<button type="submit" class="lms-course__btn lms-login__submit">
				<i class="bi bi-check2-circle"></i> Confirmar y crear cuenta
			</button>
		</form>

		<p class="lms-authswitch">¿No recibiste el código? <a href="<?php echo esc_url( $crear_url ); ?>">Regístrate de nuevo</a></p>
	</div>
</div>
