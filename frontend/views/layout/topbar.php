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
	<div class="lms-topbar__lead">
		<button class="lms-burger" type="button" data-nav-toggle aria-label="Abrir menú">
			<i class="bi bi-list"></i>
		</button>
		<span class="lms-topbar__brand-mobile"><i class="bi bi-mortarboard-fill"></i> Teems</span>
		<div class="lms-topbar__greet">
			Bienvenido de nuevo, <span><?php echo esc_html( $primer ); ?></span>
		</div>
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

<!-- Fondo oscuro del menú móvil (cierra al tocar) -->
<div class="lms-nav-overlay" data-nav-close></div>

<script>
( function () {
	var app     = document.querySelector( '.lms-app' );
	var toggle  = document.querySelector( '[data-nav-toggle]' );
	var overlay = document.querySelector( '[data-nav-close]' );
	if ( ! app || ! toggle ) { return; }
	function cerrar() { app.classList.remove( 'is-nav-open' ); }
	toggle.addEventListener( 'click', function () { app.classList.toggle( 'is-nav-open' ); } );
	if ( overlay ) { overlay.addEventListener( 'click', cerrar ); }
	// Cerrar el cajón al elegir una opción del menú.
	document.querySelectorAll( '.lms-side__link' ).forEach( function ( link ) {
		link.addEventListener( 'click', cerrar );
	} );
} )();
</script>
