<?php
/**
 * Vista: formulario de empresa (crear / editar).
 *
 * Variables recibidas:
 *   $empresa   object|null  empresa a editar, o null si es nueva.
 *   $list_url  string        URL de la lista (volver / redirigir).
 *
 * @package TeammsLMS
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$es_edicion = ! empty( $empresa );
$nombre     = $es_edicion ? $empresa->name : '';
$titulo     = $es_edicion ? 'Editar empresa' : 'Nueva empresa';
?>
<div class="lms-pagehead">
	<h1><?php echo esc_html( $titulo ); ?></h1>
	<p>Las empresas agrupan estudiantes. Asignas la empresa a cada estudiante desde la sección Usuarios.</p>
</div>

<form class="lms-form" action="<?php echo esc_url( $list_url ); ?>" method="post">
	<input type="hidden" name="lms_action" value="save_company">
	<input type="hidden" name="redirect" value="<?php echo esc_url( $list_url ); ?>">
	<?php if ( $es_edicion ) : ?>
		<input type="hidden" name="company_id" value="<?php echo (int) $empresa->id; ?>">
	<?php endif; ?>
	<?php wp_nonce_field( 'lms_save_company' ); ?>

	<div class="lms-field">
		<label for="lms-company-name">Nombre de la empresa <span class="lms-req">*</span></label>
		<input type="text" id="lms-company-name" name="name" required value="<?php echo esc_attr( $nombre ); ?>" placeholder="Ej. Constructora Andina S.A." autofocus>
	</div>

	<div class="lms-form__actions">
		<button type="submit" class="lms-course__btn"><i class="bi bi-check2"></i> <?php echo $es_edicion ? 'Guardar cambios' : 'Crear empresa'; ?></button>
		<a class="lms-btn-ghost" href="<?php echo esc_url( $list_url ); ?>">Cancelar</a>
	</div>
</form>
