<?php
/**
 * Vista: menú lateral (sidebar).
 *
 * Variables recibidas:
 *   $perfil        string  perfil actual (admin|estudiante)
 *   $perfil_label  string  etiqueta legible del perfil
 *   $nav           array   ítems del menú [ clave => [ etiqueta, icono ] ]
 *   $vista_actual  string  vista activa
 *
 * @package TeammsLMS
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<aside class="lms-side">
	<div class="lms-side__brand">
		<span class="lms-side__logo"><i class="bi bi-mortarboard-fill"></i></span>
		<span class="lms-side__brandtext">
			<strong>Teamms</strong>
			<small><?php echo esc_html( $perfil_label ); ?></small>
		</span>
	</div>

	<nav class="lms-side__nav">
		<?php foreach ( $nav as $clave => $datos ) : ?>
			<?php
			list( $etiqueta, $icono ) = $datos;
			$url    = ( 'dashboard' === $clave ) ? remove_query_arg( array( 'vista', 'id' ) ) : add_query_arg( 'vista', $clave );
			$activo = ( $vista_actual === $clave ) ? ' is-active' : '';
			?>
			<a class="lms-side__link<?php echo esc_attr( $activo ); ?>" href="<?php echo esc_url( $url ); ?>">
				<i class="bi <?php echo esc_attr( $icono ); ?>"></i>
				<span><?php echo esc_html( $etiqueta ); ?></span>
			</a>
		<?php endforeach; ?>
	</nav>

	<div class="lms-side__foot">
		<a class="lms-side__link" href="<?php echo esc_url( add_query_arg( 'vista', 'login', remove_query_arg( 'id' ) ) ); ?>">
			<i class="bi bi-box-arrow-right"></i>
			<span>Cerrar sesión</span>
		</a>
	</div>
</aside>