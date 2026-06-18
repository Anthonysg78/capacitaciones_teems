<?php
/**
 * Vista: pantalla de INICIAR SESIÓN del LMS (solo login).
 *
 * Tiene un enlace para ir a la pantalla de crear cuenta. Si viene de un link de
 * invitación ($curso/$invite), tras entrar inscribe en ese curso.
 *
 * Variables recibidas:
 *   $base    string       URL de la página del LMS (a donde volver / a donde postear).
 *   $invite  string        código de invitación (o '' si no hay).
 *   $curso   object|null   curso de la invitación (para mostrar contexto) o null.
 *
 * @package TeammsLMS
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$err = isset( $_GET['err'] ) ? sanitize_key( wp_unslash( $_GET['err'] ) ) : '';

// Enlace a la pantalla de crear cuenta (conservando la invitación si la hay).
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

		<?php if ( ! empty( $curso ) ) : ?>
			<p class="lms-join__eyebrow"><i class="bi bi-mortarboard"></i> Para unirte al curso</p>
			<h1 class="lms-login__title"><?php echo esc_html( $curso->title ); ?></h1>
			<p class="lms-login__sub">Inicia sesión y quedarás inscrito automáticamente.</p>
		<?php else : ?>
			<h1 class="lms-login__title">Inicia sesión</h1>
			<p class="lms-login__sub">Ingresa con el correo y la contraseña de tu cuenta.</p>
		<?php endif; ?>

		<?php if ( '1' === $err ) : ?>
			<div class="lms-notice lms-notice--err"><i class="bi bi-exclamation-triangle"></i> Correo o contraseña incorrectos.</div>
		<?php elseif ( 'expired' === $err ) : ?>
			<div class="lms-notice lms-notice--err"><i class="bi bi-clock-history"></i> La sesión del formulario expiró. Inténtalo de nuevo.</div>
		<?php endif; ?>

		<form class="lms-loginform" method="post" action="<?php echo esc_url( $base ); ?>">
			<input type="hidden" name="lms_action" value="lms_login">
			<input type="hidden" name="invite" value="<?php echo esc_attr( $invite ); ?>">
			<input type="hidden" name="redirect" value="<?php echo esc_url( $base ); ?>">
			<?php wp_nonce_field( 'lms_login' ); ?>

			<div class="lms-field">
				<label for="lms-login-email">Correo electrónico</label>
				<input type="text" id="lms-login-email" name="email" required autocomplete="username" placeholder="tucorreo@empresa.com" autofocus>
			</div>

			<div class="lms-field">
				<label for="lms-login-pass">Contraseña</label>
				<input type="password" id="lms-login-pass" name="password" required autocomplete="current-password" placeholder="••••••••">
			</div>

			<label class="lms-login__remember">
				<input type="checkbox" name="remember" value="1"> Mantener mi sesión iniciada
			</label>

			<button type="submit" class="lms-course__btn lms-login__submit">
				<i class="bi bi-box-arrow-in-right"></i> Entrar
			</button>
		</form>

		<p class="lms-authswitch">¿No tienes cuenta? <a href="<?php echo esc_url( $crear_url ); ?>">Crear cuenta</a></p>
	</div>
</div>
