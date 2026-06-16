<?php
/**
 * Vista: estado vacío del estudiante (certificados / insignias).
 *
 * Variables recibidas:
 *   $titulo  string  título de la sección
 *   $icono   string  clase del icono Bootstrap
 *   $texto   string  mensaje del estado vacío
 *
 * @package TeemsLMS
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div class="lms-pagehead">
	<h1><?php echo esc_html( $titulo ); ?></h1>
</div>
<div class="lms-empty">
	<i class="bi <?php echo esc_attr( $icono ); ?>"></i>
	<p><?php echo esc_html( $texto ); ?></p>
</div>
