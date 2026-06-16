<?php
/**
 * Vista: EXAMEN del módulo (el estudiante responde).
 *
 * Preguntas y opciones ya vienen barajadas desde el controlador. Si una
 * pregunta tiene varias respuestas correctas, se muestran checkboxes; si solo
 * una, botones de radio. NO se revela cuál es la correcta.
 *
 * Variables recibidas:
 *   $curso, $modulo   object
 *   $preguntas        array   preguntas con ->options
 *   $attempt_number   int     número de intento (1 o 2)
 *   $max_intentos     int
 *   $nota_minima      int
 *   $form_url         string  a dónde se envía (y se vuelve)
 *   $back_url         string  volver al curso
 *
 * @package TeemsLMS
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>

<a class="lms-back" href="<?php echo esc_url( $back_url ); ?>"><i class="bi bi-arrow-left"></i> Volver al curso</a>

<div class="lms-coursecard">
	<div>
		<span class="lms-eyebrow">Evaluación del módulo</span>
		<h1 class="lms-coursecard__title"><?php echo esc_html( $modulo->title ); ?></h1>
		<p class="lms-coursecard__desc">
			Intento <strong><?php echo (int) $attempt_number; ?></strong> de <?php echo (int) $max_intentos; ?>.
			Necesitas <strong><?php echo (int) $nota_minima; ?>/10</strong> para aprobar. Responde todas las preguntas.
		</p>
	</div>
</div>

<form class="lms-quiz" method="post" action="<?php echo esc_url( $form_url ); ?>">
	<input type="hidden" name="lms_action" value="submit_evaluation">
	<input type="hidden" name="module_id" value="<?php echo (int) $modulo->id; ?>">
	<input type="hidden" name="redirect" value="<?php echo esc_url( $form_url ); ?>">
	<?php wp_nonce_field( 'lms_submit_evaluation' ); ?>

	<?php foreach ( $preguntas as $idx => $q ) : ?>
		<?php
		// ¿Varias correctas? -> checkboxes; si no, radios.
		$n_correctas = 0;
		foreach ( $q->options as $o ) {
			if ( (int) $o->is_correct === 1 ) {
				$n_correctas++;
			}
		}
		$multi = ( $n_correctas > 1 );
		$tipo  = $multi ? 'checkbox' : 'radio';
		$name  = $multi ? 'respuesta[' . (int) $q->id . '][]' : 'respuesta[' . (int) $q->id . ']';
		?>
		<div class="lms-qbox">
			<p class="lms-qbox__text">
				<span class="lms-evalq__num"><?php echo (int) ( $idx + 1 ); ?></span>
				<?php echo esc_html( $q->question_text ); ?>
				<?php if ( $multi ) : ?><span class="lms-qbox__hint">(varias respuestas)</span><?php endif; ?>
			</p>
			<div class="lms-qbox__opts">
				<?php foreach ( $q->options as $o ) : ?>
					<label class="lms-opt">
						<input type="<?php echo esc_attr( $tipo ); ?>" name="<?php echo esc_attr( $name ); ?>" value="<?php echo (int) $o->id; ?>">
						<span><?php echo esc_html( $o->option_text ); ?></span>
					</label>
				<?php endforeach; ?>
			</div>
		</div>
	<?php endforeach; ?>

	<div class="lms-quiz__foot">
		<button type="submit" class="lms-course__btn" onclick="return confirm('¿Enviar tus respuestas? Se usará uno de tus intentos.');">
			<i class="bi bi-send"></i> Enviar respuestas
		</button>
		<a class="lms-btn-ghost" href="<?php echo esc_url( $back_url ); ?>">Cancelar</a>
	</div>
</form>
