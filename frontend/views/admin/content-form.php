<?php
/**
 * Vista: formulario de contenido (crear / editar).
 *
 * Variables recibidas:
 *   $contenido       object|null  contenido a editar, o null si es nuevo
 *   $module_id       int          id del módulo al que pertenece
 *   $contenidos_url  string       URL de la estructura del curso (volver / redirigir)
 *   $next_order      int          siguiente orden sugerido
 *
 * @package TeammsLMS
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$tipos      = LMS_Content::tipos();
$es_edicion = ( $contenido instanceof stdClass );
$tipo_sel   = $es_edicion ? $contenido->type : 'texto';
$titulo     = $es_edicion ? $contenido->title : '';
$texto      = $es_edicion ? $contenido->content_text : '';
$url        = $es_edicion ? $contenido->content_url : '';
$orden      = $es_edicion ? (int) $contenido->order_index : (int) $next_order;
$content_id = $es_edicion ? (int) $contenido->id : 0;
?>
<div class="lms-breadcrumb">
	<a href="<?php echo esc_url( $contenidos_url ); ?>"><i class="bi bi-arrow-left"></i> Contenidos</a>
	<span>/</span>
	<strong><?php echo $es_edicion ? 'Editar' : 'Nuevo'; ?></strong>
</div>

<div class="lms-pagehead">
	<h1><?php echo $es_edicion ? 'Editar contenido' : 'Nuevo contenido'; ?></h1>
	<p>Elige el tipo de contenido y completa los datos.</p>
</div>

<form class="lms-form" action="<?php echo esc_url( $contenidos_url ); ?>" method="post" enctype="multipart/form-data">
	<input type="hidden" name="lms_action" value="save_content">
	<input type="hidden" name="content_id" value="<?php echo esc_attr( $content_id ); ?>">
	<input type="hidden" name="module_id" value="<?php echo esc_attr( $module_id ); ?>">
	<input type="hidden" name="redirect" value="<?php echo esc_url( $contenidos_url ); ?>">
	<!-- Guarda el archivo/enlace anterior: si al editar no se sube uno nuevo, se conserva. -->
	<input type="hidden" name="current_url" value="<?php echo esc_attr( $url ); ?>">
	<?php wp_nonce_field( 'lms_save_content' ); ?>

	<div class="lms-field">
		<label for="lms-ctype">Tipo de contenido <span class="lms-req">*</span></label>
		<select id="lms-ctype" name="type" data-content-type>
			<?php foreach ( $tipos as $clave => $etiqueta ) : ?>
				<option value="<?php echo esc_attr( $clave ); ?>" <?php selected( $tipo_sel, $clave ); ?>>
					<?php echo esc_html( $etiqueta ); ?>
				</option>
			<?php endforeach; ?>
		</select>
	</div>

	<div class="lms-field">
		<label for="lms-ctitle">Título <span class="lms-req">*</span></label>
		<input type="text" id="lms-ctitle" name="title" value="<?php echo esc_attr( $titulo ); ?>" required placeholder="Ej. ¿Por qué importa la seguridad?">
	</div>

	<!-- Campo para tipo TEXTO (editor enriquecido) -->
	<div class="lms-field" data-when="texto">
		<label>Contenido de texto</label>
		<div class="lms-editor" data-quill-editor></div>
		<textarea name="content_text" data-quill-input hidden><?php echo esc_textarea( $texto ); ?></textarea>
	</div>

	<!-- Campo ENLACE para tipo VIDEO (y opcional para RECURSO externo) -->
	<div class="lms-field" data-when="video recurso">
		<label for="lms-curl">Enlace (URL)</label>
		<input type="url" id="lms-curl" name="content_url" value="<?php echo esc_attr( $url ); ?>" placeholder="https://...">
		<p class="lms-help">Pega el enlace del video (YouTube/Vimeo) o de un recurso externo.</p>
	</div>

	<!-- Campo SUBIR ARCHIVO para tipo PDF / RECURSO -->
	<div class="lms-field" data-when="pdf recurso">
		<label for="lms-cfile">Subir archivo</label>
		<?php if ( $url ) : ?>
			<p class="lms-help">
				Archivo actual:
				<a href="<?php echo esc_url( $url ); ?>" target="_blank" rel="noopener"><?php echo esc_html( basename( wp_parse_url( $url, PHP_URL_PATH ) ?? $url ) ); ?></a>
				— sube uno nuevo solo si quieres reemplazarlo.
			</p>
		<?php endif; ?>
		<input type="file" id="lms-cfile" name="content_file" accept=".pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.txt,.csv,.zip,image/*">
		<p class="lms-help">Sube un PDF o cualquier documento (Word, Excel, PowerPoint, imagen, etc.).</p>
	</div>

	<div class="lms-field">
		<label for="lms-corder">Orden</label>
		<input type="text" id="lms-corder" name="order_index" value="<?php echo esc_attr( $orden ); ?>" inputmode="numeric" style="max-width:120px;">
	</div>

	<div class="lms-form__actions">
		<button type="submit" class="lms-course__btn"><i class="bi bi-save"></i> Guardar contenido</button>
		<a class="lms-btn-ghost" href="<?php echo esc_url( $contenidos_url ); ?>">Cancelar</a>
	</div>
</form>

<script>
// Muestra solo el campo que corresponde al tipo elegido.
// Sin JS, todos los campos quedan visibles (no se rompe nada).
( function () {
	var select = document.querySelector( '[data-content-type]' );
	if ( ! select ) { return; }
	var campos = document.querySelectorAll( '[data-when]' );
	function actualizar() {
		var tipo = select.value;
		campos.forEach( function ( el ) {
			var tipos = el.getAttribute( 'data-when' ).split( ' ' );
			el.style.display = ( tipos.indexOf( tipo ) !== -1 ) ? '' : 'none';
		} );
	}
	select.addEventListener( 'change', actualizar );
	actualizar();
} )();

// Editor enriquecido (Quill) para el contenido de texto.
( function () {
	if ( ! window.Quill ) { return; } // sin Quill: queda el textarea oculto (respaldo).
	var input = document.querySelector( '[data-quill-input]' );
	var edEl  = document.querySelector( '[data-quill-editor]' );
	if ( ! input || ! edEl ) { return; }
	var quill = new Quill( edEl, {
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
	quill.clipboard.dangerouslyPasteHTML( input.value || '' );
	var form = input.closest( 'form' );
	if ( form ) {
		form.addEventListener( 'submit', function () {
			input.value = ( quill.getText().trim() === '' ) ? '' : quill.root.innerHTML;
		} );
	}
} )();
</script>
