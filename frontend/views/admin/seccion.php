<?php
/**
 * Vista: sección del admin aún no construida (placeholder).
 *
 * Variables recibidas:
 *   $titulo  string  título de la sección
 *   $icono   string  clase del icono Bootstrap
 *   $texto   string  descripción de lo que hará la sección
 *
 * @package TeemsLMS
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div class="lms-pagehead">
	<h1><?php echo esc_html( $titulo ); ?></h1>
	<p><?php echo esc_html( $texto ); ?></p>
</div>
<div class="lms-empty">
	<i class="bi <?php echo esc_attr( $icono ); ?>"></i>
	<p>La gestión de <strong><?php echo esc_html( $titulo ); ?></strong> estará disponible muy pronto.</p>
	<a class="lms-btn d-inline-flex" 
	href="<?php echo esc_url( remove_query_arg( array( 'vista', 'id' ) ) ); ?>"><i class="bi bi-arrow-left"></i> Volver al panel</a>
</div>
