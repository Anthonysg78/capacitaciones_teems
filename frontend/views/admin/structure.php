<?php
/**
 * Vista: EDITOR DE ESTRUCTURA del curso (árbol anidado de 3 niveles).
 *
 * Muestra Módulo → Subtema → Contenido en una sola pantalla, con tarjetas
 * colapsables. Crear/editar usa MODALES (Bootstrap) que se abren sin salir de
 * esta página; el formulario se envía normal y vuelve aquí.
 *
 * Mejora progresiva: cada botón "Añadir/Editar" conserva su enlace a la página
 * del formulario, así que si el JS no carga, la navegación sigue funcionando.
 *
 * Variables recibidas:
 *   $curso  object  el curso que se está editando
 *   $arbol  array   módulos anidados:
 *                   [ [ 'modulo'=>obj, 'subtemas'=>[ [ 'subtema'=>obj, 'contenidos'=>[obj,...] ], ... ] ], ... ]
 *   $list_url  string  URL de la lista de cursos
 *   $msg       string  mensaje de estado
 *
 * @package TeemsLMS
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
	<a class="lms-btn-outline lms-btn-outline--sm" href="<?php echo esc_url( $editar_curso_url ); ?>">
		<i class="bi bi-sliders"></i> Editar datos
	</a>
</div>

<?php if ( 'saved' === $msg ) : ?>
	<div class="lms-notice lms-notice--ok"><i class="bi bi-check-circle"></i> Cambios guardados correctamente.</div>
<?php elseif ( 'deleted' === $msg ) : ?>
	<div class="lms-notice lms-notice--ok"><i class="bi bi-trash"></i> Elemento eliminado.</div>
<?php elseif ( 'error' === $msg ) : ?>
	<div class="lms-notice lms-notice--err"><i class="bi bi-exclamation-triangle"></i> Revisa los datos e inténtalo de nuevo.</div>
<?php elseif ( 'expired' === $msg ) : ?>
	<div class="lms-notice lms-notice--err"><i class="bi bi-clock-history"></i> El enlace expiró. Vuelve a intentarlo.</div>
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
			$m           = $nodo_m['modulo'];
			$subtemas    = $nodo_m['subtemas'];
			$preguntas   = $nodo_m['preguntas'];
			$n_sub       = count( $subtemas );
			$n_preg      = count( $preguntas );
			$editar_m_url = add_query_arg( array( 'accion' => 'modulo_form', 'curso' => (int) $curso->id, 'id' => (int) $m->id ), $list_url );
			$nuevo_s_url  = add_query_arg( array( 'accion' => 'subtema_form', 'modulo' => (int) $m->id ), $list_url );
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
						<span class="lms-tnode__sub"><?php echo (int) $n_sub; ?> subtema<?php echo 1 === $n_sub ? '' : 's'; ?></span>
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
					<?php if ( empty( $subtemas ) ) : ?>
						<p class="lms-tempty">Este módulo aún no tiene subtemas.</p>
					<?php else : ?>
						<?php foreach ( $subtemas as $nodo_s ) : ?>
							<?php
							$s            = $nodo_s['subtema'];
							$contenidos   = $nodo_s['contenidos'];
							$n_con        = count( $contenidos );
							$editar_s_url = add_query_arg( array( 'accion' => 'subtema_form', 'modulo' => (int) $m->id, 'id' => (int) $s->id ), $list_url );
							$nuevo_c_url  = add_query_arg( array( 'accion' => 'contenido_form', 'subtema' => (int) $s->id ), $list_url );
							?>
							<!-- NIVEL 2: SUBTEMA -->
							<div class="lms-tnode lms-tnode--sub is-open">
								<div class="lms-tnode__head">
									<button type="button" class="lms-tnode__chevron" data-toggle-node aria-label="Expandir o contraer">
										<i class="bi bi-chevron-down"></i>
									</button>
									<span class="lms-grip" title="Pronto: arrastrar para reordenar"><i class="bi bi-grip-vertical"></i></span>
									<div class="lms-tnode__titles">
										<span class="lms-tnode__title"><?php echo esc_html( $s->title ); ?></span>
										<span class="lms-tnode__sub"><?php echo (int) $n_con; ?> contenido<?php echo 1 === $n_con ? '' : 's'; ?></span>
									</div>
									<div class="lms-tnode__actions">
										<a class="lms-iconbtn" href="<?php echo esc_url( $editar_s_url ); ?>" title="Editar subtema"
										   data-modal-trigger data-modal="subtema" data-mode="edit"
										   data-module="<?php echo (int) $m->id; ?>"
										   data-id="<?php echo (int) $s->id; ?>"
										   data-title="<?php echo esc_attr( $s->title ); ?>"
										   data-desc="<?php echo esc_attr( $s->description ); ?>"
										   data-order="<?php echo (int) $s->order_index; ?>"><i class="bi bi-pencil"></i></a>
										<a class="lms-iconbtn lms-iconbtn--danger" href="<?php echo esc_url( $borrar_url( 'delete_subtopic', $s->id, 'lms_delete_subtopic_' ) ); ?>" title="Borrar subtema" onclick="return confirm('¿Borrar este subtema y sus contenidos?');"><i class="bi bi-trash"></i></a>
									</div>
								</div>

								<div class="lms-tnode__body">
									<?php foreach ( $contenidos as $c ) : ?>
										<?php
										$tipo_lbl     = isset( $tipos_lbl[ $c->type ] ) ? $tipos_lbl[ $c->type ] : $c->type;
										$tipo_ico     = isset( $tipos_ico[ $c->type ] ) ? $tipos_ico[ $c->type ] : 'bi-dot';
										$editar_c_url = add_query_arg( array( 'accion' => 'contenido_form', 'subtema' => (int) $s->id, 'id' => (int) $c->id ), $list_url );
										?>
										<!-- NIVEL 3: CONTENIDO -->
										<div class="lms-tleaf lms-tleaf--<?php echo esc_attr( $c->type ); ?>">
											<span class="lms-grip" title="Pronto: arrastrar para reordenar"><i class="bi bi-grip-vertical"></i></span>
											<span class="lms-tleaf__icon"><i class="bi <?php echo esc_attr( $tipo_ico ); ?>"></i></span>
											<span class="lms-tleaf__title"><?php echo esc_html( $c->title ); ?></span>
											<span class="lms-tleaf__type"><?php echo esc_html( $tipo_lbl ); ?></span>
											<span class="lms-tleaf__actions">
												<a class="lms-iconbtn" href="<?php echo esc_url( $editar_c_url ); ?>" title="Editar contenido"
												   data-modal-trigger data-modal="contenido" data-mode="edit"
												   data-subtema="<?php echo (int) $s->id; ?>"
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

									<a class="lms-tadd" href="<?php echo esc_url( $nuevo_c_url ); ?>"
									   data-modal-trigger data-modal="contenido" data-mode="new"
									   data-subtema="<?php echo (int) $s->id; ?>" data-order="<?php echo (int) ( $n_con + 1 ); ?>">
										<i class="bi bi-plus-lg"></i> Añadir contenido
									</a>
								</div>
							</div>
						<?php endforeach; ?>
					<?php endif; ?>

					<a class="lms-tadd lms-tadd--sub" href="<?php echo esc_url( $nuevo_s_url ); ?>"
					   data-modal-trigger data-modal="subtema" data-mode="new"
					   data-module="<?php echo (int) $m->id; ?>" data-order="<?php echo (int) ( $n_sub + 1 ); ?>">
						<i class="bi bi-plus-lg"></i> Añadir subtema
					</a>

					<!-- Evaluación del módulo (parte del módulo, después de los subtemas) -->
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
	El orden de módulos, subtemas y contenidos se define con el campo <strong>Orden</strong> de cada formulario.
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

<!-- Modal: SUBTEMA -->
<div class="modal fade lms-modal" id="lms-modal-subtema" tabindex="-1" aria-hidden="true">
	<div class="modal-dialog modal-dialog-centered">
		<div class="modal-content">
			<form method="post" action="<?php echo esc_url( $estructura_url ); ?>">
				<input type="hidden" name="lms_action" value="save_subtopic">
				<input type="hidden" name="subtopic_id" value="0" data-field="id">
				<input type="hidden" name="module_id" value="0" data-field="module">
				<input type="hidden" name="redirect" value="<?php echo esc_url( $estructura_url ); ?>">
				<?php wp_nonce_field( 'lms_save_subtopic' ); ?>
				<div class="modal-header">
					<h5 class="modal-title" data-modal-title>Nuevo subtema</h5>
					<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
				</div>
				<div class="modal-body">
					<div class="lms-field">
						<label>Título del subtema <span class="lms-req">*</span></label>
						<input type="text" name="title" required placeholder="Ej. ¿Qué es una contraseña segura?">
					</div>
					<div class="lms-field">
						<label>Descripción</label>
						<textarea name="description" rows="4" placeholder="Breve descripción del subtema."></textarea>
					</div>
					<div class="lms-field">
						<label>Orden</label>
						<input type="text" name="order_index" value="1" inputmode="numeric" style="max-width:120px;">
					</div>
				</div>
				<div class="modal-footer">
					<button type="button" class="lms-btn-ghost" data-bs-dismiss="modal">Cancelar</button>
					<button type="submit" class="lms-course__btn"><i class="bi bi-save"></i> Guardar subtema</button>
				</div>
			</form>
		</div>
	</div>
</div>

<!-- Modal: CONTENIDO -->
<div class="modal fade lms-modal" id="lms-modal-contenido" tabindex="-1" aria-hidden="true">
	<div class="modal-dialog modal-dialog-centered">
		<div class="modal-content">
			<form method="post" action="<?php echo esc_url( $estructura_url ); ?>">
				<input type="hidden" name="lms_action" value="save_content">
				<input type="hidden" name="content_id" value="0" data-field="id">
				<input type="hidden" name="subtopic_id" value="0" data-field="subtema">
				<input type="hidden" name="redirect" value="<?php echo esc_url( $estructura_url ); ?>">
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
						<textarea name="content_text" rows="6" placeholder="Escribe aquí la información del contenido."></textarea>
					</div>
					<div class="lms-field" data-when="video pdf recurso">
						<label>Enlace (URL)</label>
						<input type="url" name="content_url" placeholder="https://...">
						<p class="lms-help">Pega el enlace del video (YouTube/Vimeo), del PDF o del recurso externo.</p>
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

	// 2) Abrir modales (rellenando el formulario según el botón pulsado).
	var titulos = {
		modulo:    [ 'Nuevo módulo', 'Editar módulo' ],
		subtema:   [ 'Nuevo subtema', 'Editar subtema' ],
		contenido: [ 'Nuevo contenido', 'Editar contenido' ],
		pregunta:  [ 'Nueva pregunta', 'Editar pregunta' ]
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
			var tipo  = d.modal;          // modulo | subtema | contenido
			var esEdit = ( d.mode === 'edit' );
			var modal = document.getElementById( 'lms-modal-' + tipo );
			if ( ! modal ) { return; }

			// Título del modal.
			modal.querySelector( '[data-modal-title]' ).textContent = titulos[ tipo ][ esEdit ? 1 : 0 ];

			// Id (0 = nuevo) y contexto padre.
			var fId = campoData( modal, 'id' ); if ( fId ) { fId.value = d.id || '0'; }
			if ( tipo === 'subtema' || tipo === 'pregunta' ) { var fM = campoData( modal, 'module' );  if ( fM ) { fM.value = d.module || '0'; } }
			if ( tipo === 'contenido' ) { var fS = campoData( modal, 'subtema' ); if ( fS ) { fS.value = d.subtema || '0'; } }

			// Campos del formulario.
			var fTitle = campo( modal, 'title' );       if ( fTitle ) { fTitle.value = d.title || ''; }
			var fOrder = campo( modal, 'order_index' );  if ( fOrder ) { fOrder.value = d.order || '1'; }
			if ( tipo === 'subtema' ) {
				var fDesc = campo( modal, 'description' ); if ( fDesc ) { fDesc.value = d.desc || ''; }
			}
			if ( tipo === 'contenido' ) {
				var fType = campo( modal, 'type' );        if ( fType ) { fType.value = d.ctype || 'texto'; }
				var fText = campo( modal, 'content_text' ); if ( fText ) { fText.value = d.text || ''; }
				var fUrl  = campo( modal, 'content_url' );  if ( fUrl )  { fUrl.value  = d.url || ''; }
				toggleContenido( modal );
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
