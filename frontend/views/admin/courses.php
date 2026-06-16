<?php
/**
 * Vista: catálogo de cursos (panel admin) en formato de tarjetas.
 *
 * Variables recibidas:
 *   $items     array   cada item: [ 'curso'=>obj, 'modulos'=>int, 'subtemas'=>int ]
 *   $nuevo_url string  URL para crear un curso nuevo
 *   $list_url  string  URL base de esta lista
 *   $msg       string  mensaje de estado
 *
 * @package TeemsLMS
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div class="lms-pagehead lms-pagehead--row">
	<div>
		<h1>Cursos</h1>
		<p>Catálogo de cursos y su estructura.</p>
	</div>
	<a class="lms-course__btn" href="<?php echo esc_url( $nuevo_url ); ?>"><i class="bi bi-plus-lg"></i> Nuevo curso</a>
</div>

<?php if ( 'saved' === $msg ) : ?>
	<div class="lms-notice lms-notice--ok"><i class="bi bi-check-circle"></i> Curso guardado correctamente.</div>
<?php elseif ( 'deleted' === $msg ) : ?>
	<div class="lms-notice lms-notice--ok"><i class="bi bi-trash"></i> Curso eliminado.</div>
<?php elseif ( 'error' === $msg ) : ?>
	<div class="lms-notice lms-notice--err"><i class="bi bi-exclamation-triangle"></i> El título es obligatorio.</div>
<?php endif; ?>

<?php if ( empty( $items ) ) : ?>
	<div class="lms-empty">
		<i class="bi bi-book"></i>
		<p>Todavía no hay cursos. ¡Crea el primero!</p>
		<a class="lms-course__btn d-inline-flex" href="<?php echo esc_url( $nuevo_url ); ?>"><i class="bi bi-plus-lg"></i> Nuevo curso</a>
	</div>
<?php else : ?>
	<div class="lms-courses">
		<?php foreach ( $items as $it ) : ?>
			<?php
			$c            = $it['curso'];
			$estructura_url = add_query_arg( array( 'accion' => 'modulos', 'id' => (int) $c->id ), $list_url );
			$editar_url   = add_query_arg( array( 'accion' => 'editar', 'id' => (int) $c->id ), $list_url );
			$borrar_url   = wp_nonce_url(
				add_query_arg(
					array(
						'lms_action' => 'delete_course',
						'id'         => (int) $c->id,
						'redirect'   => rawurlencode( $list_url ),
					),
					$list_url
				),
				'lms_delete_course_' . (int) $c->id
			);
			$desc = wp_trim_words( wp_strip_all_tags( (string) $c->description ), 16, '…' );
			?>
			<article class="lms-course">
				<div class="lms-course__cover" style="background: linear-gradient(150deg, #2563eb 0%, #0b1f4d 120%);">
					<span class="lms-course__covertag <?php echo $c->published ? 'is-pub' : ''; ?>">
						<?php echo $c->published ? 'Publicado' : 'Borrador'; ?>
					</span>
					<span class="lms-course__coverico"><i class="bi bi-book"></i></span>
				</div>
				<div class="lms-course__body">
					<h3 class="lms-course__title"><?php echo esc_html( $c->title ); ?></h3>
					<p class="lms-course__desc"><?php echo esc_html( $desc ); ?></p>
					<div class="lms-course__meta">
						<span><i class="bi bi-layers"></i> <?php echo esc_html( $it['modulos'] ); ?> módulos</span>
						<span><i class="bi bi-list-ol"></i> <?php echo esc_html( $it['subtemas'] ); ?> subtemas</span>
					</div>
					<a class="lms-btn-outline" href="<?php echo esc_url( $estructura_url ); ?>">
						<i class="bi bi-pencil"></i> Editar estructura
					</a>
					<div class="lms-cardfoot">
						<a class="lms-iconbtn" href="<?php echo esc_url( $editar_url ); ?>" title="Editar datos del curso"><i class="bi bi-sliders"></i></a>
						<a class="lms-iconbtn lms-iconbtn--danger" href="<?php echo esc_url( $borrar_url ); ?>" title="Borrar curso" onclick="return confirm('¿Borrar este curso? Esta acción no se puede deshacer.');"><i class="bi bi-trash"></i></a>
					</div>
				</div>
			</article>
		<?php endforeach; ?>
	</div>
<?php endif; ?>
