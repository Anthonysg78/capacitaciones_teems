<?php
/**
 * Vista: EDITOR DE ESTRUCTURA del curso (árbol de 2 niveles).
 *
 * Muestra Módulo → Contenido en una sola pantalla, con tarjetas colapsables.
 * Cada módulo lleva además su Evaluación (banco de preguntas). Crear/editar usa
 * MODALES (Bootstrap) que se abren sin salir de esta página; el formulario se
 * envía normal y vuelve aquí.
 *
 * Mejora progresiva: cada botón "Añadir/Editar" conserva su enlace a la página
 * del formulario, así que si el JS no carga, la navegación sigue funcionando.
 *
 * Variables recibidas:
 *   $curso  object  el curso que se está editando
 *   $arbol  array   módulos: [ [ 'modulo'=>obj, 'contenidos'=>[obj,...], 'preguntas'=>[obj,...] ], ... ]
 *   $list_url  string  URL de la lista de cursos
 *   $msg       string  mensaje de estado
 *
 * @package TeammsLMS
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// URL de ESTA página (para volver tras crear/editar/borrar).
$estructura_url = add_query_arg( array( 'accion' => 'modulos', 'id' => (int) $curso->id ), $list_url );

// Tipos de contenido: etiqueta + icono.
$tipos_lbl = LMS_Content::tipos();
$tipos_ico = array(
	'texto'   => 'bi-file-text',
	'video'   => 'bi-play-circle',
	'pdf'     => 'bi-file-earmark-pdf',
	'recurso' => 'bi-link-45deg',
);

// URLs de cabecera.
$editar_curso_url = add_query_arg( array( 'accion' => 'editar', 'id' => (int) $curso->id ), $list_url );
$nuevo_modulo_url = add_query_arg( array( 'accion' => 'modulo_form', 'curso' => (int) $curso->id ), $list_url );
$orden_modulo     = count( $arbol ) + 1; // sugerencia de orden para un módulo nuevo.

// Link de invitación al curso (se comparte con los estudiantes).
$invite_token = LMS_Course::ensure_token( (int) $curso->id );
$invite_url   = add_query_arg( 'invite', $invite_token, get_permalink( get_the_ID() ) );
$invite_regen = wp_nonce_url(
	add_query_arg(
		array( 'lms_action' => 'regen_invite', 'id' => (int) $curso->id, 'redirect' => rawurlencode( $estructura_url ) ),
		$estructura_url
	),
	'lms_regen_invite_' . (int) $curso->id
);

/** Ayudante: enlace de borrado con nonce que vuelve a esta página. */
$borrar_url = function ( $accion, $id, $nonce ) use ( $estructura_url ) {
	return wp_nonce_url(
		add_query_arg(
			array( 'lms_action' => $accion, 'id' => (int) $id, 'redirect' => rawurlencode( $estructura_url ) ),
			$estructura_url
		),
		$nonce . (int) $id
	);
};
?>

<a class="lms-back" href="<?php echo esc_url( $list_url ); ?>"><i class="bi bi-arrow-left"></i> Volver a cursos</a>

<!-- Cabecera de datos del curso -->
<div class="lms-coursecard">
	<div>
		<span class="lms-eyebrow">Editando curso</span>
		<h1 class="lms-coursecard__title"><?php echo esc_html( $curso->title ); ?></h1>
		<?php if ( ! empty( $curso->description ) ) : ?>
			<p class="lms-coursecard__desc"><?php echo esc_html( wp_trim_words( wp_strip_all_tags( (string) $curso->description ), 30, '…' ) ); ?></p>
		<?php endif; ?>
	</div>
	<a class="lms-btn-outline lms-btn-outline--sm" href="<?php echo esc_url( $editar_curso_url ); ?>"
	   data-modal-trigger data-modal="curso" data-mode="edit"
	   data-id="<?php echo (int) $curso->id; ?>"
	   data-title="<?php echo esc_attr( $curso->title ); ?>"
	   data-desc="<?php echo esc_attr( $curso->description ); ?>"
	   data-published="<?php echo (int) $curso->published; ?>">
		<i class="bi bi-sliders"></i> Editar datos
	</a>
</div>

<!-- Link de invitación al curso -->
<div class="lms-invite">
	<div class="lms-invite__info">
		<span class="lms-invite__label"><i class="bi bi-link-45deg"></i> Link de invitación al curso</span>
		<input type="text" class="lms-invite__url" value="<?php echo esc_url( $invite_url ); ?>" readonly onclick="this.select();">
	</div>
	<div class="lms-invite__actions">
		<button type="button" class="lms-course__btn lms-invite__copy" data-copy="<?php echo esc_attr( $invite_url ); ?>"><i class="bi bi-clipboard"></i> Copiar link</button>
		<a class="lms-iconbtn" href="<?php echo esc_url( $invite_regen ); ?>" title="Regenerar (invalida el link anterior)" onclick="return confirm('¿Regenerar el link? El anterior dejará de funcionar.');"><i class="bi bi-arrow-clockwise"></i></a>
	</div>
</div>

<?php if ( 'saved' === $msg ) : ?>
	<div class="lms-notice lms-notice--ok"><i class="bi bi-check-circle"></i> Cambios guardados correctamente.</div>
<?php elseif ( 'deleted' === $msg ) : ?>
	<div class="lms-notice lms-notice--ok"><i class="bi bi-trash"></i> Elemento eliminado.</div>
<?php elseif ( 'error' === $msg ) : ?>
	<div class="lms-notice lms-notice--err"><i class="bi bi-exclamation-triangle"></i> Revisa los datos e inténtalo de nuevo.</div>
<?php elseif ( 'expired' === $msg ) : ?>
	<div class="lms-notice lms-notice--err"><i class="bi bi-clock-history"></i> El enlace expiró. Vuelve a intentarlo.</div>
<?php elseif ( 'invite' === $msg ) : ?>
	<div class="lms-notice lms-notice--ok"><i class="bi bi-link-45deg"></i> Link de invitación regenerado. El anterior ya no funciona.</div>
<?php endif; ?>

<!-- Barra de sección -->
<div class="lms-sectionbar">
	<h2 class="lms-sectionbar__title">Estructura del curso</h2>
	<a class="lms-course__btn" href="<?php echo esc_url( $nuevo_modulo_url ); ?>"
	   data-modal-trigger data-modal="modulo" data-mode="new"
	   data-course="<?php echo (int) $curso->id; ?>" data-order="<?php echo (int) $orden_modulo; ?>">
		<i class="bi bi-plus-lg"></i> Añadir módulo
	</a>
</div>

<?php if ( empty( $arbol ) ) : ?>
	<div class="lms-empty">
		<i class="bi bi-diagram-3"></i>
		<p>Este curso aún no tiene módulos. ¡Crea el primero para empezar a construir la estructura!</p>
		<a class="lms-course__btn d-inline-flex" href="<?php echo esc_url( $nuevo_modulo_url ); ?>"
		   data-modal-trigger data-modal="modulo" data-mode="new"
		   data-course="<?php echo (int) $curso->id; ?>" data-order="<?php echo (int) $orden_modulo; ?>">
			<i class="bi bi-plus-lg"></i> Añadir módulo
		</a>
	</div>
<?php else : ?>
	<div class="lms-tree">
		<?php foreach ( $arbol as $nodo_m ) : ?>
			<?php
			$m            = $nodo_m['modulo'];
			$contenidos   = $nodo_m['contenidos'];
			$preguntas    = $nodo_m['preguntas'];
			$n_con        = count( $contenidos );
			$n_preg       = count( $preguntas );
			$editar_m_url = add_query_arg( array( 'accion' => 'modulo_form', 'curso' => (int) $curso->id, 'id' => (int) $m->id ), $list_url );
			$nuevo_c_url  = add_query_arg( array( 'accion' => 'contenido_form', 'modulo' => (int) $m->id ), $list_url );
			?>
			<!-- NIVEL 1: MÓDULO -->
			<div class="lms-tnode lms-tnode--mod is-open">
				<div class="lms-tnode__head">
					<button type="button" class="lms-tnode__chevron" data-toggle-node aria-label="Expandir o contraer">
						<i class="bi bi-chevron-down"></i>
					</button>
					<span class="lms-grip" title="Pronto: arrastrar para reordenar"><i class="bi bi-grip-vertical"></i></span>
					<span class="lms-tbadge"><?php echo (int) $m->order_index; ?></span>
					<div class="lms-tnode__titles">
						<span class="lms-tnode__title"><?php echo esc_html( $m->title ); ?></span>
						<span class="lms-tnode__sub"><?php echo (int) $n_con; ?> contenido<?php echo 1 === $n_con ? '' : 's'; ?></span>
					</div>
					<div class="lms-tnode__actions">
						<a class="lms-iconbtn" href="<?php echo esc_url( $editar_m_url ); ?>" title="Editar módulo"
						   data-modal-trigger data-modal="modulo" data-mode="edit"
						   data-id="<?php echo (int) $m->id; ?>"
						   data-title="<?php echo esc_attr( $m->title ); ?>"
						   data-order="<?php echo (int) $m->order_index; ?>"><i class="bi bi-pencil"></i></a>
						<a class="lms-iconbtn lms-iconbtn--danger" href="<?php echo esc_url( $borrar_url( 'delete_module', $m->id, 'lms_delete_module_' ) ); ?>" title="Borrar módulo" onclick="return confirm('¿Borrar este módulo y todo su contenido?');"><i class="bi bi-trash"></i></a>
					</div>
				</div>

				<div class="lms-tnode__body">
					<?php if ( empty( $contenidos ) ) : ?>
						<p class="lms-tempty">Este módulo aún no tiene contenidos.</p>
					<?php else : ?>
						<?php foreach ( $contenidos as $c ) : ?>
							<?php
							$tipo_lbl     = isset( $tipos_lbl[ $c->type ] ) ? $tipos_lbl[ $c->type ] : $c->type;
							$tipo_ico     = isset( $tipos_ico[ $c->type ] ) ? $tipos_ico[ $c->type ] : 'bi-dot';
							$editar_c_url = add_query_arg( array( 'accion' => 'contenido_form', 'modulo' => (int) $m->id, 'id' => (int) $c->id ), $list_url );
							?>
							<!-- NIVEL 2: CONTENIDO -->
							<div class="lms-tleaf lms-tleaf--<?php echo esc_attr( $c->type ); ?>">
								<span class="lms-grip" title="Pronto: arrastrar para reordenar"><i class="bi bi-grip-vertical"></i></span>
								<span class="lms-tleaf__icon"><i class="bi <?php echo esc_attr( $tipo_ico ); ?>"></i></span>
								<span class="lms-tleaf__title"><?php echo esc_html( $c->title ); ?></span>
								<span class="lms-tleaf__type"><?php echo esc_html( $tipo_lbl ); ?></span>
								<span class="lms-tleaf__actions">
									<a class="lms-iconbtn" href="<?php echo esc_url( $editar_c_url ); ?>" title="Editar contenido"
									   data-modal-trigger data-modal="contenido" data-mode="edit"
									   data-module="<?php echo (int) $m->id; ?>"
									   data-id="<?php echo (int) $c->id; ?>"
									   data-ctype="<?php echo esc_attr( $c->type ); ?>"
									   data-title="<?php echo esc_attr( $c->title ); ?>"
									   data-text="<?php echo esc_attr( $c->content_text ); ?>"
									   data-url="<?php echo esc_attr( $c->content_url ); ?>"
									   data-order="<?php echo (int) $c->order_index; ?>"><i class="bi bi-pencil"></i></a>
									<a class="lms-iconbtn lms-iconbtn--danger" href="<?php echo esc_url( $borrar_url( 'delete_content', $c->id, 'lms_delete_content_' ) ); ?>" title="Borrar contenido" onclick="return confirm('¿Borrar este contenido?');"><i class="bi bi-trash"></i></a>
								</span>
							</div>
						<?php endforeach; ?>
					<?php endif; ?>

					<a class="lms-tadd" href="<?php echo esc_url( $nuevo_c_url ); ?>"
					   data-modal-trigger data-modal="contenido" data-mode="new"
					   data-module="<?php echo (int) $m->id; ?>" data-order="<?php echo (int) ( $n_con + 1 ); ?>">
						<i class="bi bi-plus-lg"></i> Añadir contenido
					</a>

					<!-- Evaluación del módulo (parte del módulo, después de los contenidos) -->
					<div class="lms-eval">
						<div class="lms-eval__head">
							<span class="lms-eval__icon"><i class="bi bi-card-checklist"></i></span>
							<div class="lms-tnode__titles">
								<span class="lms-tnode__title">Evaluación del módulo</span>
								<span class="lms-tnode__sub"><?php echo (int) $n_preg; ?> pregunta<?php echo 1 === $n_preg ? '' : 's'; ?></span>
							</div>
						</div>

						<?php if ( empty( $preguntas ) ) : ?>
							<p class="lms-tempty">Aún no hay preguntas en la evaluación.</p>
						<?php else : ?>
							<?php foreach ( $preguntas as $qi => $q ) : ?>
								<?php
								$op_data = array();
								foreach ( $q->options as $o ) {
									$op_data[] = array( 'text' => $o->option_text, 'correct' => ( (int) $o->is_correct === 1 ) );
								}
								?>
								<div class="lms-evalq">
									<span class="lms-evalq__num"><?php echo (int) ( $qi + 1 ); ?></span>
									<span class="lms-evalq__text"><?php echo esc_html( $q->question_text ); ?></span>
									<span class="lms-evalq__actions">
										<a class="lms-iconbtn" href="#" title="Editar pregunta"
										   data-modal-trigger data-modal="pregunta" data-mode="edit"
										   data-module="<?php echo (int) $m->id; ?>"
										   data-id="<?php echo (int) $q->id; ?>"
										   data-text="<?php echo esc_attr( $q->question_text ); ?>"
										   data-options="<?php echo esc_attr( wp_json_encode( $op_data ) ); ?>"><i class="bi bi-pencil"></i></a>
										<a class="lms-iconbtn lms-iconbtn--danger" href="<?php echo esc_url( $borrar_url( 'delete_question', $q->id, 'lms_delete_question_' ) ); ?>" title="Borrar pregunta" onclick="return confirm('¿Borrar esta pregunta?');"><i class="bi bi-trash"></i></a>
									</span>
								</div>
							<?php endforeach; ?>
						<?php endif; ?>

						<a class="lms-tadd" href="#" data-modal-trigger data-modal="pregunta" data-mode="new" data-module="<?php echo (int) $m->id; ?>">
							<i class="bi bi-plus-lg"></i> Añadir pregunta
						</a>
					</div>
				</div>
			</div>
		<?php endforeach; ?>
	</div>
<?php endif; ?>

<p class="lms-treefoot">
	<i class="bi bi-info-circle"></i>
	El orden de módulos y contenidos se define con el campo <strong>Orden</strong> de cada formulario.
	(Arrastrar para reordenar llegará más adelante.)
</p>

<!-- ============================ MODALES ============================ -->

<!-- Modal: MÓDULO -->
<div class="modal fade lms-modal" id="lms-modal-modulo" tabindex="-1" aria-hidden="true">
	<div class="modal-dialog modal-dialog-centered">
		<div class="modal-content">
			<form method="post" action="<?php echo esc_url( $estructura_url ); ?>">
				<input type="hidden" name="lms_action" value="save_module">
				<input type="hidden" name="module_id" value="0" data-field="id">
				<input type="hidden" name="course_id" value="<?php echo (int) $curso->id; ?>">
				<input type="hidden" name="redirect" value="<?php echo esc_url( $estructura_url ); ?>">
				<?php wp_nonce_field( 'lms_save_module' ); ?>
				<div class="modal-header">
					<h5 class="modal-title" data-modal-title>Nuevo módulo</h5>
					<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
				</div>
				<div class="modal-body">
					<div class="lms-field">
						<label>Título del módulo <span class="lms-req">*</span></label>
						<input type="text" name="title" required placeholder="Ej. Introducción a la Ciberseguridad">
					</div>
					<div class="lms-field">
						<label>Orden</label>
						<input type="text" name="order_index" value="1" inputmode="numeric" style="max-width:120px;">
					</div>
				</div>
				<div class="modal-footer">
					<button type="button" class="lms-btn-ghost" data-bs-dismiss="modal">Cancelar</button>
					<button type="submit" class="lms-course__btn"><i class="bi bi-save"></i> Guardar módulo</button>
				</div>
			</form>
		</div>
	</div>
</div>

<!-- Modal: CONTENIDO -->
<div class="modal fade lms-modal" id="lms-modal-contenido" tabindex="-1" aria-hidden="true">
	<div class="modal-dialog modal-dialog-centered">
		<div class="modal-content">
			<form method="post" action="<?php echo esc_url( $estructura_url ); ?>" enctype="multipart/form-data">
				<input type="hidden" name="lms_action" value="save_content">
				<input type="hidden" name="content_id" value="0" data-field="id">
				<input type="hidden" name="module_id" value="0" data-field="module">
				<input type="hidden" name="redirect" value="<?php echo esc_url( $estructura_url ); ?>">
				<input type="hidden" name="current_url" value="" data-field="current_url">
				<?php wp_nonce_field( 'lms_save_content' ); ?>
				<div class="modal-header">
					<h5 class="modal-title" data-modal-title>Nuevo contenido</h5>
					<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
				</div>
				<div class="modal-body">
					<div class="lms-field">
						<label>Tipo de contenido <span class="lms-req">*</span></label>
						<select name="type">
							<?php foreach ( $tipos_lbl as $clave => $etiqueta ) : ?>
								<option value="<?php echo esc_attr( $clave ); ?>"><?php echo esc_html( $etiqueta ); ?></option>
							<?php endforeach; ?>
						</select>
					</div>
					<div class="lms-field">
						<label>Título <span class="lms-req">*</span></label>
						<input type="text" name="title" required placeholder="Ej. ¿Por qué importa la seguridad?">
					</div>
					<div class="lms-field" data-when="texto">
						<label>Contenido de texto</label>
						<div class="lms-editor" data-quill-editor></div>
						<textarea name="content_text" data-quill-input hidden></textarea>
					</div>
					<div class="lms-field" data-when="video recurso">
						<label>Enlace (URL)</label>
						<input type="url" name="content_url" placeholder="https://...">
						<p class="lms-help">Pega el enlace del video (YouTube/Vimeo) o de un recurso externo.</p>
					</div>
					<div class="lms-field" data-when="pdf recurso">
						<label>Subir archivo</label>
						<p class="lms-help" data-current-file style="display:none;">Archivo actual: <a data-current-link href="#" target="_blank" rel="noopener">ver</a> — sube uno nuevo solo si quieres reemplazarlo.</p>
						<input type="file" name="content_file" accept=".pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.txt,.csv,.zip,image/*">
						<p class="lms-help">Sube un PDF o cualquier documento (Word, Excel, PowerPoint, imagen, etc.).</p>
					</div>
					<div class="lms-field">
						<label>Orden</label>
						<input type="text" name="order_index" value="1" inputmode="numeric" style="max-width:120px;">
					</div>
				</div>
				<div class="modal-footer">
					<button type="button" class="lms-btn-ghost" data-bs-dismiss="modal">Cancelar</button>
					<button type="submit" class="lms-course__btn"><i class="bi bi-save"></i> Guardar contenido</button>
				</div>
			</form>
		</div>
	</div>
</div>

<!-- Modal: CURSO (editar datos del curso, sin salir de esta página) -->
<div class="modal fade lms-modal" id="lms-modal-curso" tabindex="-1" aria-hidden="true">
	<div class="modal-dialog modal-dialog-centered">
		<div class="modal-content">
			<form method="post" action="<?php echo esc_url( $list_url ); ?>">
				<input type="hidden" name="lms_action" value="save_course">
				<input type="hidden" name="course_id" value="0" data-field="id">
				<input type="hidden" name="redirect" value="<?php echo esc_url( $estructura_url ); ?>">
				<?php wp_nonce_field( 'lms_save_course' ); ?>
				<div class="modal-header">
					<h5 class="modal-title" data-modal-title>Editar curso</h5>
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

<!-- Modal: PREGUNTA (evaluación del módulo) -->
<div class="modal fade lms-modal" id="lms-modal-pregunta" tabindex="-1" aria-hidden="true">
	<div class="modal-dialog modal-dialog-centered">
		<div class="modal-content">
			<form method="post" action="<?php echo esc_url( $estructura_url ); ?>">
				<input type="hidden" name="lms_action" value="save_question">
				<input type="hidden" name="question_id" value="0" data-field="id">
				<input type="hidden" name="module_id" value="0" data-field="module">
				<input type="hidden" name="redirect" value="<?php echo esc_url( $estructura_url ); ?>">
				<?php wp_nonce_field( 'lms_save_question' ); ?>
				<div class="modal-header">
					<h5 class="modal-title" data-modal-title>Nueva pregunta</h5>
					<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
				</div>
				<div class="modal-body">
					<div class="lms-field">
						<label>Enunciado <span class="lms-req">*</span></label>
						<textarea name="question_text" rows="3" required placeholder="Ej. ¿Cuál es la longitud mínima recomendada para una contraseña segura?"></textarea>
					</div>

					<div class="lms-switchrow">
						<label class="lms-switch">
							<input type="checkbox" data-multi-toggle>
							<span class="lms-switch__slider"></span>
						</label>
						<span class="lms-switchrow__label">Permitir varias respuestas correctas</span>
					</div>

					<label class="lms-field__label">Opciones <span class="lms-req">*</span> <span class="lms-help-inline">(marca la(s) correcta(s))</span></label>
					<div data-options-list></div>
					<button type="button" class="lms-tadd" data-add-option><i class="bi bi-plus-lg"></i> Añadir opción</button>
				</div>
				<div class="modal-footer">
					<button type="button" class="lms-btn-ghost" data-bs-dismiss="modal">Cancelar</button>
					<button type="submit" class="lms-course__btn"><i class="bi bi-save"></i> Guardar pregunta</button>
				</div>
			</form>
		</div>
	</div>
</div>

<script>
( function () {
	// 1) Colapsar/expandir nodos del árbol.
	document.querySelectorAll( '[data-toggle-node]' ).forEach( function ( btn ) {
		btn.addEventListener( 'click', function () {
			var nodo = btn.closest( '.lms-tnode' );
			if ( nodo ) { nodo.classList.toggle( 'is-open' ); }
		} );
	} );

	// 1b) Copiar el link de invitación al portapapeles.
	var copyBtn = document.querySelector( '[data-copy]' );
	if ( copyBtn ) {
		copyBtn.addEventListener( 'click', function () {
			var url = copyBtn.getAttribute( 'data-copy' );
			var inp = document.querySelector( '.lms-invite__url' );
			function feedback() {
				var orig = copyBtn.innerHTML;
				copyBtn.innerHTML = '<i class="bi bi-check-lg"></i> ¡Copiado!';
				setTimeout( function () { copyBtn.innerHTML = orig; }, 1800 );
			}
			function fallback() { if ( inp ) { inp.focus(); inp.select(); try { document.execCommand( 'copy' ); } catch ( e ) {} feedback(); } }
			if ( navigator.clipboard && navigator.clipboard.writeText ) {
				navigator.clipboard.writeText( url ).then( feedback, fallback );
			} else {
				fallback();
			}
		} );
	}

	// 1c) Editor enriquecido (Quill) para el contenido tipo texto.
	var contModal = document.getElementById( 'lms-modal-contenido' );
	var quillEd   = null;
	if ( contModal && window.Quill ) {
		var qEl = contModal.querySelector( '[data-quill-editor]' );
		if ( qEl ) {
			quillEd = new Quill( qEl, {
				theme: 'snow',
				placeholder: 'Escribe aquí la información del contenido.',
				modules: { toolbar: [
					[ { header: [ 2, 3, false ] } ],
					[ 'bold', 'italic', 'underline' ],
					[ { list: 'ordered' }, { list: 'bullet' } ],
					[ 'link' ],
					[ 'clean' ]
				] }
			} );
			var qForm  = contModal.querySelector( 'form' );
			var qInput = contModal.querySelector( '[data-quill-input]' );
			if ( qForm && qInput ) {
				qForm.addEventListener( 'submit', function () {
					qInput.value = ( quillEd.getText().trim() === '' ) ? '' : quillEd.root.innerHTML;
				} );
			}
		}
	}

	// 2) Abrir modales (rellenando el formulario según el botón pulsado).
	var titulos = {
		modulo:    [ 'Nuevo módulo', 'Editar módulo' ],
		contenido: [ 'Nuevo contenido', 'Editar contenido' ],
		pregunta:  [ 'Nueva pregunta', 'Editar pregunta' ],
		curso:     [ 'Editar curso', 'Editar curso' ]
	};

	function campo( modal, name ) { return modal.querySelector( '[name="' + name + '"]' ); }
	function campoData( modal, field ) { return modal.querySelector( '[data-field="' + field + '"]' ); }

	// --- Modal de pregunta: opciones dinámicas (añadir/quitar) ---
	var pModal   = document.getElementById( 'lms-modal-pregunta' );
	var optList  = pModal ? pModal.querySelector( '[data-options-list]' ) : null;
	var multiTog = pModal ? pModal.querySelector( '[data-multi-toggle]' ) : null;

	function renumOptions() {
		if ( ! optList ) { return; }
		var rows = optList.querySelectorAll( '.lms-qoptrow' );
		rows.forEach( function ( row, i ) {
			row.querySelector( '.lms-correctbox' ).value = i;
			row.querySelector( '[type="text"]' ).placeholder = 'Opción ' + ( i + 1 );
			var del = row.querySelector( '[data-del-option]' );
			// El botón de quitar solo aparece si hay más de 2 opciones (mínimo 2).
			if ( del ) { del.style.visibility = ( rows.length > 2 ) ? 'visible' : 'hidden'; }
		} );
	}

	function addOption( text, correct ) {
		if ( ! optList ) { return; }
		var row = document.createElement( 'div' );
		row.className = 'lms-qoptrow';
		row.innerHTML =
			'<input type="checkbox" name="correct[]" class="lms-correctbox" title="Marcar como correcta">' +
			'<input type="text" name="option_text[]">' +
			'<button type="button" class="lms-optdel" data-del-option title="Quitar opción"><i class="bi bi-x-lg"></i></button>';
		optList.appendChild( row );
		row.querySelector( '[type="text"]' ).value = text || '';
		if ( correct ) { row.querySelector( '.lms-correctbox' ).checked = true; }
		renumOptions();
	}

	function resetOptions( n ) {
		if ( ! optList ) { return; }
		optList.innerHTML = '';
		for ( var k = 0; k < n; k++ ) { addOption( '', false ); }
	}

	function toggleContenido( modal ) {
		var sel = campo( modal, 'type' );
		if ( ! sel ) { return; }
		modal.querySelectorAll( '[data-when]' ).forEach( function ( el ) {
			var tipos = el.getAttribute( 'data-when' ).split( ' ' );
			el.style.display = ( tipos.indexOf( sel.value ) !== -1 ) ? '' : 'none';
		} );
	}

	document.querySelectorAll( '[data-modal-trigger]' ).forEach( function ( t ) {
		t.addEventListener( 'click', function ( e ) {
			if ( ! window.bootstrap ) { return; } // sin JS de Bootstrap: deja navegar (respaldo).
			e.preventDefault();
			var d     = t.dataset;
			var tipo  = d.modal;          // modulo | contenido | pregunta | curso
			var esEdit = ( d.mode === 'edit' );
			var modal = document.getElementById( 'lms-modal-' + tipo );
			if ( ! modal ) { return; }

			// Título del modal.
			modal.querySelector( '[data-modal-title]' ).textContent = titulos[ tipo ][ esEdit ? 1 : 0 ];

			// Id (0 = nuevo) y contexto padre (el módulo).
			var fId = campoData( modal, 'id' ); if ( fId ) { fId.value = d.id || '0'; }
			if ( tipo === 'contenido' || tipo === 'pregunta' ) {
				var fM = campoData( modal, 'module' ); if ( fM ) { fM.value = d.module || '0'; }
			}

			// Campos del formulario.
			var fTitle = campo( modal, 'title' );       if ( fTitle ) { fTitle.value = d.title || ''; }
			var fOrder = campo( modal, 'order_index' );  if ( fOrder ) { fOrder.value = d.order || '1'; }

			if ( tipo === 'contenido' ) {
				var fType = campo( modal, 'type' );        if ( fType ) { fType.value = d.ctype || 'texto'; }
				var fText = campo( modal, 'content_text' ); if ( fText ) { fText.value = d.text || ''; }
				if ( quillEd ) { quillEd.clipboard.dangerouslyPasteHTML( d.text || '' ); }
				var fUrl  = campo( modal, 'content_url' );  if ( fUrl )  { fUrl.value  = d.url || ''; }
				var fCur  = campo( modal, 'current_url' );  if ( fCur )  { fCur.value  = d.url || ''; }
				var fFile = campo( modal, 'content_file' ); if ( fFile ) { fFile.value = ''; } // limpiar el input de archivo.
				// Mostrar el archivo/enlace actual (si lo hay) al editar.
				var note  = modal.querySelector( '[data-current-file]' );
				var link  = modal.querySelector( '[data-current-link]' );
				if ( note && link ) {
					if ( d.url ) { link.href = d.url; note.style.display = ''; }
					else { note.style.display = 'none'; }
				}
				toggleContenido( modal );
			}
			if ( tipo === 'curso' ) {
				var fCDesc = campo( modal, 'description' ); if ( fCDesc ) { fCDesc.value = d.desc || ''; }
				var fCPub  = campo( modal, 'published' );   if ( fCPub )  { fCPub.checked = ( d.published === '1' ); }
			}
			if ( tipo === 'pregunta' ) {
				var fQ = campo( modal, 'question_text' ); if ( fQ ) { fQ.value = d.text || ''; }
				if ( multiTog ) { multiTog.checked = false; }
				if ( esEdit ) {
					var opts = [];
					try { opts = JSON.parse( d.options || '[]' ); } catch ( err ) { opts = []; }
					optList.innerHTML = '';
					var nCorrectas = 0;
					opts.forEach( function ( o ) {
						addOption( o.text || '', !! o.correct );
						if ( o.correct ) { nCorrectas++; }
					} );
					if ( opts.length < 2 ) { addOption( '', false ); } // por seguridad
					// Si la pregunta ya tenía varias correctas, encender el switch.
					if ( multiTog && nCorrectas > 1 ) { multiTog.checked = true; }
				} else {
					resetOptions( 3 ); // nueva pregunta: empieza con 3 opciones.
				}
			}

			bootstrap.Modal.getOrCreateInstance( modal ).show();
		} );
	} );

	// 3) En el modal de contenido, mostrar el campo según el tipo elegido.
	var selTipo = document.querySelector( '#lms-modal-contenido [name="type"]' );
	if ( selTipo ) {
		selTipo.addEventListener( 'change', function () {
			toggleContenido( document.getElementById( 'lms-modal-contenido' ) );
		} );
	}

	// 4) Modal de pregunta: añadir/quitar opciones + una sola correcta (salvo switch).
	if ( optList ) {
		// Añadir opción.
		pModal.querySelector( '[data-add-option]' ).addEventListener( 'click', function () {
			addOption( '', false );
		} );

		// Quitar opción (delegación) — nunca por debajo de 2.
		optList.addEventListener( 'click', function ( e ) {
			var del = e.target.closest( '[data-del-option]' );
			if ( ! del ) { return; }
			if ( optList.querySelectorAll( '.lms-qoptrow' ).length > 2 ) {
				del.closest( '.lms-qoptrow' ).remove();
				renumOptions();
			}
		} );

		// Si el switch está apagado, solo una correcta a la vez (delegación).
		optList.addEventListener( 'change', function ( e ) {
			var box = e.target;
			if ( ! box.classList || ! box.classList.contains( 'lms-correctbox' ) ) { return; }
			if ( box.checked && multiTog && ! multiTog.checked ) {
				optList.querySelectorAll( '.lms-correctbox' ).forEach( function ( otra ) {
					if ( otra !== box ) { otra.checked = false; }
				} );
			}
		} );

		// Al volver a "una sola", dejar solo la primera marcada.
		if ( multiTog ) {
			multiTog.addEventListener( 'change', function () {
				if ( ! multiTog.checked ) {
					var ya = false;
					optList.querySelectorAll( '.lms-correctbox' ).forEach( function ( box ) {
						if ( box.checked ) { if ( ya ) { box.checked = false; } else { ya = true; } }
					} );
				}
			} );
		}
	}
} )();
</script>
