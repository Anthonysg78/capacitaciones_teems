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
 * @package TeammsLMS
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
	<a class="lms-course__btn" href="<?php echo esc_url( $nuevo_url ); ?>" data-modal-trigger data-modal="curso" data-mode="new"><i class="bi bi-plus-lg"></i> Nuevo curso</a>
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
		<a class="lms-course__btn d-inline-flex" href="<?php echo esc_url( $nuevo_url ); ?>" data-modal-trigger data-modal="curso" data-mode="new"><i class="bi bi-plus-lg"></i> Nuevo curso</a>
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
						<span><i class="bi bi-list-ol"></i> <?php echo esc_html( $it['contenidos'] ); ?> contenidos</span>
					</div>
					<a class="lms-btn-outline" href="<?php echo esc_url( $estructura_url ); ?>">
						<i class="bi bi-pencil"></i> Editar estructura
					</a>
					<div class="lms-cardfoot">
						<a class="lms-iconbtn" href="<?php echo esc_url( $editar_url ); ?>" title="Editar datos del curso"
						   data-modal-trigger data-modal="curso" data-mode="edit"
						   data-id="<?php echo (int) $c->id; ?>"
						   data-title="<?php echo esc_attr( $c->title ); ?>"
						   data-desc="<?php echo esc_attr( $c->description ); ?>"
						   data-published="<?php echo (int) $c->published; ?>"><i class="bi bi-sliders"></i></a>
						<a class="lms-iconbtn lms-iconbtn--danger" href="<?php echo esc_url( $borrar_url ); ?>" title="Borrar curso" onclick="return confirm('¿Borrar este curso? Esta acción no se puede deshacer.');"><i class="bi bi-trash"></i></a>
					</div>
				</div>
			</article>
		<?php endforeach; ?>
	</div>
<?php endif; ?>

<!-- ============================ MODAL: CURSO ============================ -->
<div class="modal fade lms-modal" id="lms-modal-curso" tabindex="-1" aria-hidden="true">
	<div class="modal-dialog modal-dialog-centered">
		<div class="modal-content">
			<form method="post" action="<?php echo esc_url( $list_url ); ?>">
				<input type="hidden" name="lms_action" value="save_course">
				<input type="hidden" name="course_id" value="0" data-field="id">
				<input type="hidden" name="redirect" value="<?php echo esc_url( $list_url ); ?>">
				<?php wp_nonce_field( 'lms_save_course' ); ?>
				<div class="modal-header">
					<h5 class="modal-title" data-modal-title>Nuevo curso</h5>
					<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
				</div>
				<div class="modal-body">
					<div class="lms-field">
						<label>Título del curso <span class="lms-req">*</span></label>
						<input type="text" name="title" required placeholder="Ej. Seguridad en Redes">
					</div>
					<div class="lms-field">
						<label>Descripción</label>
						<textarea name="description" rows="5" placeholder="¿De qué trata el curso?"></textarea>
					</div>
					<div class="lms-field lms-field--check">
						<label>
							<input type="checkbox" name="published" value="1">
							Publicar curso (si lo dejas sin marcar, queda como borrador)
						</label>
					</div>
				</div>
				<div class="modal-footer">
					<button type="button" class="lms-btn-ghost" data-bs-dismiss="modal">Cancelar</button>
					<button type="submit" class="lms-course__btn"><i class="bi bi-save"></i> Guardar curso</button>
				</div>
			</form>
		</div>
	</div>
</div>

<script>
( function () {
	var titulos = { curso: [ 'Nuevo curso', 'Editar curso' ] };
	function campo( m, n )     { return m.querySelector( '[name="' + n + '"]' ); }
	function campoData( m, f ) { return m.querySelector( '[data-field="' + f + '"]' ); }

	document.querySelectorAll( '[data-modal-trigger]' ).forEach( function ( t ) {
		t.addEventListener( 'click', function ( e ) {
			if ( ! window.bootstrap ) { return; } // sin JS de Bootstrap: deja navegar a la página (respaldo).
			e.preventDefault();
			var d      = t.dataset;
			var esEdit = ( d.mode === 'edit' );
			var modal  = document.getElementById( 'lms-modal-' + d.modal );
			if ( ! modal ) { return; }

			modal.querySelector( '[data-modal-title]' ).textContent = titulos[ d.modal ][ esEdit ? 1 : 0 ];

			var fId = campoData( modal, 'id' );        if ( fId ) { fId.value = d.id || '0'; }
			var fT  = campo( modal, 'title' );          if ( fT )  { fT.value = d.title || ''; }
			var fD  = campo( modal, 'description' );     if ( fD )  { fD.value = d.desc || ''; }
			var fP  = campo( modal, 'published' );       if ( fP )  { fP.checked = esEdit ? ( d.published === '1' ) : false; }

			bootstrap.Modal.getOrCreateInstance( modal ).show();
		} );
	} );
} )();
</script>
