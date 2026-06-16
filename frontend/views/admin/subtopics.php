<?php
/**
 * Vista: lista de subtemas de un módulo.
 *
 * Variables recibidas:
 *   $curso         object  curso (para la miga de pan)
 *   $modulo        object  módulo al que pertenecen los subtemas
 *   $subtemas      array   subtemas del módulo
 *   $list_url      string  URL lista de cursos
 *   $modulos_url   string  URL lista de módulos del curso
 *   $subtemas_url  string  URL de esta página (redirigir tras guardar/borrar)
 *   $nuevo_url     string  URL para crear un subtema nuevo
 *   $msg           string  mensaje de estado
 *
 * @package TeemsLMS
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div class="lms-breadcrumb">
	<a href="<?php echo esc_url( $list_url ); ?>">Cursos</a>
	<span>/</span>
	<a href="<?php echo esc_url( $modulos_url ); ?>"><?php echo esc_html( $curso->title ); ?></a>
	<span>/</span>
	<strong><?php echo esc_html( $modulo->title ); ?></strong>
</div>

<div class="lms-pagehead lms-pagehead--row">
	<div>
		<h1>Subtemas del módulo</h1>
		<p>Contenido de <strong><?php echo esc_html( $modulo->title ); ?></strong>.</p>
	</div>
	<a class="lms-course__btn" href="<?php echo esc_url( $nuevo_url ); ?>"><i class="bi bi-plus-lg"></i> Nuevo subtema</a>
</div>

<?php if ( 'saved' === $msg ) : ?>
	<div class="lms-notice lms-notice--ok"><i class="bi bi-check-circle"></i> Subtema guardado correctamente.</div>
<?php elseif ( 'deleted' === $msg ) : ?>
	<div class="lms-notice lms-notice--ok"><i class="bi bi-trash"></i> Subtema eliminado.</div>
<?php elseif ( 'error' === $msg ) : ?>
	<div class="lms-notice lms-notice--err"><i class="bi bi-exclamation-triangle"></i> El título es obligatorio.</div>
<?php elseif ( 'expired' === $msg ) : ?>
	<div class="lms-notice lms-notice--err"><i class="bi bi-clock-history"></i> El enlace expiró. Vuelve a intentarlo.</div>
<?php endif; ?>

<?php if ( empty( $subtemas ) ) : ?>
	<div class="lms-empty">
		<i class="bi bi-list-ol"></i>
		<p>Este módulo aún no tiene subtemas. ¡Crea el primero!</p>
		<a class="lms-course__btn d-inline-flex" href="<?php echo esc_url( $nuevo_url ); ?>"><i class="bi bi-plus-lg"></i> Nuevo subtema</a>
	</div>
<?php else : ?>
	<div class="lms-tablewrap">
		<table class="lms-table">
			<thead>
				<tr>
					<th style="width:70px;">Orden</th>
					<th>Título del subtema</th>
					<th class="lms-table__actions">Acciones</th>
				</tr>
			</thead>
			<tbody>
				<?php foreach ( $subtemas as $s ) : ?>
					<?php
					$editar_url = add_query_arg(
						array( 'accion' => 'subtema_form', 'modulo' => (int) $modulo->id, 'id' => (int) $s->id ),
						$list_url
					);
					$contenidos_url = add_query_arg(
						array( 'accion' => 'contenidos', 'subtema' => (int) $s->id ),
						$list_url
					);
					$borrar_url = wp_nonce_url(
						add_query_arg(
							array(
								'lms_action' => 'delete_subtopic',
								'id'         => (int) $s->id,
								'redirect'   => rawurlencode( $subtemas_url ),
							),
							$subtemas_url
						),
						'lms_delete_subtopic_' . (int) $s->id
					);
					?>
					<tr>
						<td><span class="lms-ordernum"><?php echo esc_html( $s->order_index ); ?></span></td>
						<td class="lms-table__title"><?php echo esc_html( $s->title ); ?></td>
						<td class="lms-table__actions">
							<a class="lms-iconbtn lms-iconbtn--primary" href="<?php echo esc_url( $contenidos_url ); ?>" title="Gestionar contenidos"><i class="bi bi-collection"></i></a>
							<a class="lms-iconbtn" href="<?php echo esc_url( $editar_url ); ?>" title="Editar"><i class="bi bi-pencil"></i></a>
							<a class="lms-iconbtn lms-iconbtn--danger" href="<?php echo esc_url( $borrar_url ); ?>" title="Borrar" onclick="return confirm('¿Borrar este subtema?');"><i class="bi bi-trash"></i></a>
						</td>
					</tr>
				<?php endforeach; ?>
			</tbody>
		</table>
	</div>
<?php endif; ?>
