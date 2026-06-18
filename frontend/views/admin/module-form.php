<?php
/**
 * Vista: formulario de módulo (crear / editar).
 *
 * Variables recibidas:
 *   $modulo       object|null  módulo a editar, o null si es nuevo
 *   $course_id    int          id del curso al que pertenece
 *   $modulos_url  string       URL de la lista de módulos (volver / redirigir)
 *   $next_order   int          siguiente orden sugerido
 *
 * @package TeammsLMS
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$es_edicion = ( $modulo instanceof stdClass );
$titulo     = $es_edicion ? $modulo->title : '';
$orden      = $es_edicion ? (int) $modulo->order_index : (int) $next_order;
$module_id  = $es_edicion ? (int) $modulo->id : 0;
?>
<div class="lms-breadcrumb">
	<a href="<?php echo esc_url( $modulos_url ); ?>"><i class="bi bi-arrow-left"></i> Módulos</a>
	<span>/</span>
	<strong><?php echo $es_edicion ? 'Editar' : 'Nuevo'; ?></strong>
</div>

<div class="lms-pagehead">
	<h1><?php echo $es_edicion ? 'Editar módulo' : 'Nuevo módulo'; ?></h1>
	<p>Define el título y el orden del módulo dentro del curso.</p>
</div>

<form class="lms-form" action="<?php echo esc_url( $modulos_url ); ?>" method="post">
	<input type="hidden" name="lms_action" value="save_module">
	<input type="hidden" name="module_id" value="<?php echo esc_attr( $module_id ); ?>">
	<input type="hidden" name="course_id" value="<?php echo esc_attr( $course_id ); ?>">
	<input type="hidden" name="redirect" value="<?php echo esc_url( $modulos_url ); ?>">
	<?php wp_nonce_field( 'lms_save_module' ); ?>

	<div class="lms-field">
		<label for="lms-mtitle">Título del módulo <span class="lms-req">*</span></label>
		<input type="text" id="lms-mtitle" name="title" value="<?php echo esc_attr( $titulo ); ?>" required placeholder="Ej. Introducción a la Ciberseguridad">
	</div>

	<div class="lms-field">
		<label for="lms-morder">Orden</label>
		<input type="text" id="lms-morder" name="order_index" value="<?php echo esc_attr( $orden ); ?>" inputmode="numeric" style="max-width:120px;">
	</div>

	<div class="lms-form__actions">
		<button type="submit" class="lms-course__btn"><i class="bi bi-save"></i> Guardar módulo</button>
		<a class="lms-btn-ghost" href="<?php echo esc_url( $modulos_url ); ?>">Cancelar</a>
	</div>
</form>
