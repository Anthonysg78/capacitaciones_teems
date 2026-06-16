<?php
/**
 * Vista: formulario de subtema (crear / editar).
 *
 * Variables recibidas:
 *   $subtema       object|null  subtema a editar, o null si es nuevo
 *   $module_id     int          id del módulo al que pertenece
 *   $subtemas_url  string       URL de la lista de subtemas (volver / redirigir)
 *   $next_order    int          siguiente orden sugerido
 *
 * @package TeemsLMS
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$es_edicion  = ( $subtema instanceof stdClass );
$titulo      = $es_edicion ? $subtema->title : '';
$descripcion = $es_edicion ? $subtema->description : '';
$orden       = $es_edicion ? (int) $subtema->order_index : (int) $next_order;
$subtopic_id = $es_edicion ? (int) $subtema->id : 0;
?>
<div class="lms-breadcrumb">
	<a href="<?php echo esc_url( $subtemas_url ); ?>"><i class="bi bi-arrow-left"></i> Subtemas</a>
	<span>/</span>
	<strong><?php echo $es_edicion ? 'Editar' : 'Nuevo'; ?></strong>
</div>

<div class="lms-pagehead">
	<h1><?php echo $es_edicion ? 'Editar subtema' : 'Nuevo subtema'; ?></h1>
	<p>Define el título, la descripción y el orden del subtema.</p>
</div>

<form class="lms-form" action="<?php echo esc_url( $subtemas_url ); ?>" method="post">
	<input type="hidden" name="lms_action" value="save_subtopic">
	<input type="hidden" name="subtopic_id" value="<?php echo esc_attr( $subtopic_id ); ?>">
	<input type="hidden" name="module_id" value="<?php echo esc_attr( $module_id ); ?>">
	<input type="hidden" name="redirect" value="<?php echo esc_url( $subtemas_url ); ?>">
	<?php wp_nonce_field( 'lms_save_subtopic' ); ?>

	<div class="lms-field">
		<label for="lms-stitle">Título del subtema <span class="lms-req">*</span></label>
		<input type="text" id="lms-stitle" name="title" value="<?php echo esc_attr( $titulo ); ?>" required placeholder="Ej. ¿Qué es una contraseña segura?">
	</div>

	<div class="lms-field">
		<label for="lms-sdesc">Descripción</label>
		<textarea id="lms-sdesc" name="description" rows="4" placeholder="Breve descripción del subtema."><?php echo esc_textarea( $descripcion ); ?></textarea>
	</div>

	<div class="lms-field">
		<label for="lms-sorder">Orden</label>
		<input type="text" id="lms-sorder" name="order_index" value="<?php echo esc_attr( $orden ); ?>" inputmode="numeric" style="max-width:120px;">
	</div>

	<div class="lms-form__actions">
		<button type="submit" class="lms-course__btn"><i class="bi bi-save"></i> Guardar subtema</button>
		<a class="lms-btn-ghost" href="<?php echo esc_url( $subtemas_url ); ?>">Cancelar</a>
	</div>
</form>
