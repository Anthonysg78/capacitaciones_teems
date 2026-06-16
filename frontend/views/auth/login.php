<?php
/**
 * Vista: pantalla de selección de rol (login de demostración).
 *
 * Variables recibidas:
 *   $base  string  URL de la página sin parámetros de vista/rol.
 *
 * @package TeemsLMS
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$opciones = array(
	'admin'      => array( 'Administrador', 'bi-shield-lock', 'Crear cursos, empresas y usuarios', 'admin' ),
	'empresa'    => array( 'Empresa',       'bi-building',    'Supervisar a mis colaboradores',    'empresa' ),
	'estudiante' => array( 'Estudiante',    'bi-mortarboard', 'Tomar cursos y evaluaciones',       'est' ),
);
?>
<div class="lms-login">
	<div class="lms-login__box">
		<div class="lms-login__brand">
			<span class="lms-login__logo"><i class="bi bi-mortarboard-fill"></i></span>
			Capacitaciones <strong>Teems</strong>
		</div>
		<h1 class="lms-login__title">¿Cómo quieres entrar?</h1>
		<p class="lms-login__sub">Selecciona un perfil para acceder a la plataforma.</p>

		<div class="lms-login__opts">
			<?php foreach ( $opciones as $clave => $o ) : ?>
				<?php list( $nombre, $icono, $desc, $mod ) = $o; ?>
				<a class="lms-login__opt lms-login__opt--<?php echo esc_attr( $mod ); ?>"
				   href="<?php echo esc_url( add_query_arg( 'perfil', $clave, $base ) ); ?>">
					<i class="bi <?php echo esc_attr( $icono ); ?>"></i>
					<span class="lms-login__optname"><?php echo esc_html( $nombre ); ?></span>
					<span class="lms-login__optdesc"><?php echo esc_html( $desc ); ?></span>
				</a>
			<?php endforeach; ?>
		</div>

		<a class="lms-login__logout" href="<?php echo esc_url( wp_logout_url( home_url() ) ); ?>">
			<i class="bi bi-box-arrow-right"></i> Cerrar sesión de WordPress
		</a>
		<p class="lms-login__note">Selector temporal de demostración. Se reemplazará por el login real (por invitación) en la Semana 2.</p>
	</div>
</div>
