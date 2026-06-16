<?php
/**
 * Vista: lista de módulos de un curso.
 *
 * Variables recibidas:
 *   $curso        object  el curso al que pertenecen los módulos
 *   $modulos      array   módulos de ese curso
 *   $list_url     string  URL de la lista de cursos (volver)
 *   $modulos_url  string  URL de esta página (redirigir tras guardar/borrar)
 *   $nuevo_url    string  URL para crear un módulo nuevo
 *   $msg          string  mensaje de estado
 *
 * @package TeemsLMS
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div class="lms-breadcrumb">
	<a href="<?php echo esc_url( $list_url ); ?>"><i class="bi bi-arrow-left"></i> Cursos</a>
	<span>/</span>
	<strong><?php echo esc_html( $curso->title ); ?></strong>
</div>

<div class="lms-pagehead lms-pagehead--row">
	<div>
		<h1>Módulos del curso</h1>
		<p>Organiza los módulos de <strong><?php echo esc_html( $curso->title ); ?></strong>.</p>
	</div>
	<a class="lms-course__btn" href="<?php echo esc_url( $nuevo_url ); ?>"><i class="bi bi-plus-lg"></i> Nuevo módulo</a>
</div>

<?php if ( 'saved' === $msg ) : ?>
	<div class="lms-notice lms-notice--ok"><i class="bi bi-check-circle"></i> Módulo guardado correctamente.</div>
<?php elseif ( 'deleted' === $msg ) : ?>
	<div class="lms-notice lms-notice--ok"><i class="bi bi-trash"></i> Módulo eliminado.</div>
<?php elseif ( 'error' === $msg ) : ?>
	<div class="lms-notice lms-notice--err"><i class="bi bi-exclamation-triangle"></i> El título es obligatorio.</div>
<?php elseif ( 'expired' === $msg ) : ?>
	<div class="lms-notice lms-notice--err"><i class="bi bi-clock-history"></i> El enlace expiró. Vuelve a intentarlo.</div>
<?php endif; ?>

<?php if ( empty( $modulos ) ) : ?>
	<div class="lms-empty">
		<i class="bi bi-layers"></i>
		<p>Este curso aún no tiene módulos. ¡Crea el primero!</p>
		<a class="lms-course__btn d-inline-flex" href="<?php echo esc_url( $nuevo_url ); ?>"><i class="bi bi-plus-lg"></i> Nuevo módulo</a>
	</div>
<?php else : ?>
	<div class="lms-tablewrap">
		<table class="lms-table">
			<thead>
				<tr>
					<th style="width:70px;">Orden</th>
					<th>Título del módulo</th>
					<th class="lms-table__actions">Acciones</th>
				</tr>
			</thead>
			<tbody>
				<?php foreach ( $modulos as $m ) : ?>
					<?php
					$editar_url = add_query_arg(
						array( 'accion' => 'modulo_form', 'curso' => (int) $curso->id, 'id' => (int) $m->id ),
						$list_url
					);
					$subtemas_url = add_query_arg(
						array( 'accion' => 'subtemas', 'modulo' => (int) $m->id ),
						$list_url
					);
					$borrar_url = wp_nonce_url(
						add_query_arg(
							array(
								'lms_action' => 'delete_module',
								'id'         => (int) $m->id,
								'redirect'   => rawurlencode( $modulos_url ),
							),
							$modulos_url
						),
						'lms_delete_module_' . (int) $m->id
					);
					?>
					<tr>
						<td><span class="lms-ordernum"><?php echo esc_html( $m->order_index ); ?></span></td>
						<td class="lms-table__title"><?php echo esc_html( $m->title ); ?></td>
						<td class="lms-table__actions">
							<a class="lms-iconbtn lms-iconbtn--primary" href="<?php echo esc_url( $subtemas_url ); ?>" title="Gestionar subtemas"><i class="bi bi-list-ol"></i></a>
							<a class="lms-iconbtn" href="<?php echo esc_url( $editar_url ); ?>" title="Editar"><i class="bi bi-pencil"></i></a>
							<a class="lms-iconbtn lms-iconbtn--danger" href="<?php echo esc_url( $borrar_url ); ?>" title="Borrar" onclick="return confirm('¿Borrar este módulo?');"><i class="bi bi-trash"></i></a>
						</td>
					</tr>
				<?php endforeach; ?>
			</tbody>
		</table>
	</div>
<?php endif; ?>
