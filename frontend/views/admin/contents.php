<?php
/**
 * Vista: lista de contenidos de un subtema.
 *
 * Variables recibidas:
 *   $subtema         object  subtema al que pertenecen los contenidos
 *   $modulo          object  módulo del subtema (para la miga de pan)
 *   $contenidos      array   contenidos del subtema
 *   $subtemas_url    string  URL de la lista de subtemas (volver)
 *   $contenidos_url  string  URL de esta página (redirigir tras guardar/borrar)
 *   $nuevo_url       string  URL para crear un contenido nuevo
 *   $msg             string  mensaje de estado
 *
 * @package TeemsLMS
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$tipos  = LMS_Content::tipos();
$iconos = array(
	'texto'   => 'bi-file-text',
	'video'   => 'bi-play-circle',
	'pdf'     => 'bi-file-earmark-pdf',
	'recurso' => 'bi-link-45deg',
);
?>
<div class="lms-breadcrumb">
	<a href="<?php echo esc_url( $subtemas_url ); ?>"><i class="bi bi-arrow-left"></i> Subtemas</a>
	<span>/</span>
	<strong><?php echo esc_html( $subtema->title ); ?></strong>
</div>

<div class="lms-pagehead lms-pagehead--row">
	<div>
		<h1>Contenidos del subtema</h1>
		<p>Material de <strong><?php echo esc_html( $subtema->title ); ?></strong>: texto, video, PDF o recursos.</p>
	</div>
	<a class="lms-course__btn" href="<?php echo esc_url( $nuevo_url ); ?>"><i class="bi bi-plus-lg"></i> Nuevo contenido</a>
</div>

<?php if ( 'saved' === $msg ) : ?>
	<div class="lms-notice lms-notice--ok"><i class="bi bi-check-circle"></i> Contenido guardado correctamente.</div>
<?php elseif ( 'deleted' === $msg ) : ?>
	<div class="lms-notice lms-notice--ok"><i class="bi bi-trash"></i> Contenido eliminado.</div>
<?php elseif ( 'error' === $msg ) : ?>
	<div class="lms-notice lms-notice--err"><i class="bi bi-exclamation-triangle"></i> Revisa los datos: el título es obligatorio y debes elegir un tipo válido.</div>
<?php elseif ( 'expired' === $msg ) : ?>
	<div class="lms-notice lms-notice--err"><i class="bi bi-clock-history"></i> El enlace expiró. Vuelve a intentarlo.</div>
<?php endif; ?>

<?php if ( empty( $contenidos ) ) : ?>
	<div class="lms-empty">
		<i class="bi bi-collection"></i>
		<p>Este subtema aún no tiene contenidos. ¡Agrega el primero!</p>
		<a class="lms-course__btn d-inline-flex" href="<?php echo esc_url( $nuevo_url ); ?>"><i class="bi bi-plus-lg"></i> Nuevo contenido</a>
	</div>
<?php else : ?>
	<div class="lms-tablewrap">
		<table class="lms-table">
			<thead>
				<tr>
					<th style="width:70px;">Orden</th>
					<th style="width:170px;">Tipo</th>
					<th>Título del contenido</th>
					<th class="lms-table__actions">Acciones</th>
				</tr>
			</thead>
			<tbody>
				<?php foreach ( $contenidos as $c ) : ?>
					<?php
					$tipo_label = isset( $tipos[ $c->type ] ) ? $tipos[ $c->type ] : $c->type;
					$tipo_icono = isset( $iconos[ $c->type ] ) ? $iconos[ $c->type ] : 'bi-dot';
					$editar_url = add_query_arg(
						array( 'accion' => 'contenido_form', 'subtema' => (int) $subtema->id, 'id' => (int) $c->id ),
						$contenidos_url
					);
					$borrar_url = wp_nonce_url(
						add_query_arg(
							array(
								'lms_action' => 'delete_content',
								'id'         => (int) $c->id,
								'redirect'   => rawurlencode( $contenidos_url ),
							),
							$contenidos_url
						),
						'lms_delete_content_' . (int) $c->id
					);
					?>
					<tr>
						<td><span class="lms-ordernum"><?php echo esc_html( $c->order_index ); ?></span></td>
						<td>
							<span class="lms-badge lms-badge--<?php echo esc_attr( $c->type ); ?>">
								<i class="bi <?php echo esc_attr( $tipo_icono ); ?>"></i> <?php echo esc_html( $tipo_label ); ?>
							</span>
						</td>
						<td class="lms-table__title"><?php echo esc_html( $c->title ); ?></td>
						<td class="lms-table__actions">
							<a class="lms-iconbtn" href="<?php echo esc_url( $editar_url ); ?>" title="Editar"><i class="bi bi-pencil"></i></a>
							<a class="lms-iconbtn lms-iconbtn--danger" href="<?php echo esc_url( $borrar_url ); ?>" title="Borrar" onclick="return confirm('¿Borrar este contenido?');"><i class="bi bi-trash"></i></a>
						</td>
					</tr>
				<?php endforeach; ?>
			</tbody>
		</table>
	</div>
<?php endif; ?>
