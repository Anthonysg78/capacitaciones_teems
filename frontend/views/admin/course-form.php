<?php
/**
 * Vista: formulario de curso (crear / editar).
 *
 * Variables recibidas:
 *   $curso     object|null  curso a editar, o null si es nuevo
 *   $list_url  string       URL de la lista (volver / redirigir)
 *
 * @package TeammsLMS
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$es_edicion  = ( $curso instanceof stdClass );
$titulo      = $es_edicion ? $curso->title : '';
$descripcion = $es_edicion ? $curso->description : '';
$publicado   = $es_edicion ? (int) $curso->published : 0;
$course_id   = $es_edicion ? (int) $curso->id : 0;
$portada     = $es_edicion ? (string) $curso->thumbnail_url : '';
?>
<div class="lms-pagehead">
	<h1><?php echo $es_edicion ? 'Editar curso' : 'Nuevo curso'; ?></h1>
	<p>Completa los datos del curso.</p>
</div>

<form class="lms-form" action="<?php echo esc_url( $list_url ); ?>" method="post" enctype="multipart/form-data">
	<input type="hidden" name="lms_action" value="save_course">
	<input type="hidden" name="course_id" value="<?php echo esc_attr( $course_id ); ?>">
	<input type="hidden" name="redirect" value="<?php echo esc_url( $list_url ); ?>">
	<input type="hidden" name="current_cover" value="<?php echo esc_url( $portada ); ?>">
	<?php wp_nonce_field( 'lms_save_course' ); ?>

	<div class="lms-field">
		<label for="lms-title">Título del curso <span class="lms-req">*</span></label>
		<input type="text" id="lms-title" name="title" value="<?php echo esc_attr( $titulo ); ?>" required placeholder="Ej. Seguridad en Redes">
	</div>

	<div class="lms-field">
		<label for="lms-desc">Descripción</label>
		<textarea id="lms-desc" name="description" rows="5" placeholder="¿De qué trata el curso?"><?php echo esc_textarea( $descripcion ); ?></textarea>
	</div>

	<div class="lms-field lms-field--check">
		<label>
			<input type="checkbox" name="published" value="1" <?php checked( $publicado, 1 ); ?>>
			Publicar curso (si lo dejas sin marcar, queda como borrador)
		</label>
	</div>

	<div class="lms-field">
		<label for="lms-cover">Portada del curso <small class="lms-muted">(imagen, opcional)</small></label>
		<?php if ( $portada ) : ?>
			<img src="<?php echo esc_url( $portada ); ?>" alt="Portada actual" style="display:block; width:100%; max-height:160px; object-fit:cover; border-radius:8px; margin-bottom:8px;">
		<?php endif; ?>
		<input type="file" id="lms-cover" name="cover_file" accept="image/*">
		<?php if ( $portada ) : ?>
			<label class="lms-muted" style="display:block; margin-top:6px; font-weight:400;">
				<input type="checkbox" name="remove_cover" value="1"> Quitar portada actual
			</label>
		<?php endif; ?>
	</div>

	<div class="lms-form__actions">
		<button type="submit" class="lms-course__btn"><i class="bi bi-save"></i> Guardar curso</button>
		<a class="lms-btn-ghost" href="<?php echo esc_url( $list_url ); ?>">Cancelar</a>
	</div>
</form>
