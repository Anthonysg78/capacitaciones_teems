<?php
/**
 * Vista: pantalla de CREAR CUENTA del LMS (solo registro).
 *
 * Tiene un enlace para ir a la pantalla de iniciar sesión. Si viene de un link
 * de invitación ($curso/$invite), tras crear la cuenta inscribe en ese curso.
 *
 * Variables recibidas:
 *   $base    string       URL de la página del LMS (a donde postear / volver).
 *   $invite  string        código de invitación (o '' si no hay).
 *   $curso   object|null   curso de la invitación (para mostrar contexto) o null.
 *
 * @package TeammsLMS
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$err = isset( $_GET['err'] ) ? sanitize_key( wp_unslash( $_GET['err'] ) ) : '';

// Enlace a la pantalla de iniciar sesión (conservando la invitación si la hay).
$login_url = ! empty( $invite ) ? add_query_arg( 'invite', $invite, $base ) : $base;
?>
<div class="lms-login">
	<div class="lms-login__box">
		<div class="lms-login__brand">
			<span class="lms-login__logo"><i class="bi bi-mortarboard-fill"></i></span>
			Capacitaciones <strong>Teamms</strong>
		</div>

		<?php if ( ! empty( $curso ) ) : ?>
			<p class="lms-join__eyebrow"><i class="bi bi-mortarboard"></i> Te invitaron al curso</p>
			<h1 class="lms-login__title"><?php echo esc_html( $curso->title ); ?></h1>
			<p class="lms-login__sub">Crea tu cuenta y quedarás inscrito automáticamente.</p>
		<?php else : ?>
			<h1 class="lms-login__title">Crear cuenta</h1>
			<p class="lms-login__sub">Regístrate para acceder a la plataforma.</p>
		<?php endif; ?>

		<?php if ( 'datos' === $err ) : ?>
			<div class="lms-notice lms-notice--err"><i class="bi bi-exclamation-triangle"></i> Revisa los datos: nombre, correo válido y contraseña de al menos 6 caracteres.</div>
		<?php elseif ( 'existe' === $err ) : ?>
			<div class="lms-notice lms-notice--err"><i class="bi bi-info-circle"></i> Ese correo ya tiene cuenta. <a href="<?php echo esc_url( $login_url ); ?>">Inicia sesión</a>.</div>
		<?php elseif ( 'expired' === $err ) : ?>
			<div class="lms-notice lms-notice--err"><i class="bi bi-clock-history"></i> El formulario expiró. Inténtalo de nuevo.</div>
		<?php endif; ?>

		<form class="lms-loginform" method="post" action="<?php echo esc_url( $base ); ?>">
			<input type="hidden" name="lms_action" value="lms_register">
			<input type="hidden" name="invite" value="<?php echo esc_attr( $invite ); ?>">
			<input type="hidden" name="redirect" value="<?php echo esc_url( $base ); ?>">
			<?php wp_nonce_field( 'lms_register' ); ?>

			<div class="lms-field">
				<label for="lms-reg-name">Nombre completo</label>
				<input type="text" id="lms-reg-name" name="name" required placeholder="Ej. María Pérez" autofocus>
			</div>
			<div class="lms-field">
				<label for="lms-reg-email">Correo electrónico</label>
				<input type="email" id="lms-reg-email" name="email" required autocomplete="username" placeholder="tucorreo@empresa.com">
			</div>
			<div class="lms-field">
				<label for="lms-reg-pass">Contraseña <small class="lms-muted">(mínimo 6 caracteres)</small></label>
				<input type="password" id="lms-reg-pass" name="password" required minlength="6" autocomplete="new-password" placeholder="••••••••">
			</div>

			<button type="submit" class="lms-course__btn lms-login__submit">
				<i class="bi bi-person-plus"></i> Crear cuenta<?php echo ! empty( $curso ) ? ' e inscribirme' : ''; ?>
			</button>
		</form>

		<p class="lms-authswitch">¿Ya tienes cuenta? <a href="<?php echo esc_url( $login_url ); ?>">Inicia sesión</a></p>
	</div>
</div>
