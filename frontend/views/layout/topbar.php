<?php
/**
 * Vista: barra superior (topbar).
 *
 * Variables recibidas:
 *   $perfil        string  perfil actual
 *   $perfil_label  string  etiqueta legible del perfil
 *   $nombre        string  nombre completo del usuario
 *   $primer        string  primer nombre
 *   $iniciales     string  iniciales para el avatar
 *
 * @package TeemsLMS
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<header class="lms-topbar">
	<div class="lms-topbar__greet">
		Bienvenido de nuevo, <span><?php echo esc_html( $primer ); ?></span>
	</div>
	<div class="lms-topbar__right">
		<span class="lms-rolebadge lms-rolebadge--<?php echo esc_attr( $perfil ); ?>">
			<?php echo esc_html( $perfil_label ); ?>
		</span>
		<button class="lms-bell" type="button" aria-label="Notificaciones">
			<i class="bi bi-bell"></i><span class="lms-bell__dot"></span>
		</button>
		<div class="lms-userbox">
			<span class="lms-userbox__avatar"><?php echo esc_html( $iniciales ); ?></span>
			<span class="lms-userbox__info">
				<strong><?php echo esc_html( $nombre ); ?></strong>
				<small><?php echo esc_html( $perfil_label ); ?></small>
			</span>
		</div>
	</div>
</header>
