<?php
/**
 * Vista: ESTADO / RESULTADO de la evaluación del módulo.
 *
 * Muestra si aprobó, la nota del último intento, los intentos usados, el botón
 * para rendir/reintentar (si puede) y la retroalimentación pregunta por pregunta.
 *
 * Variables recibidas:
 *   $curso, $modulo   object
 *   $preguntas        array   preguntas con ->options
 *   $n_intentos       int     intentos usados
 *   $max_intentos     int
 *   $nota_minima      int
 *   $aprobada         bool
 *   $can_take         bool    puede rendir/reintentar
 *   $ultimo           object|null  último intento
 *   $sel_map          array   [ question_id => [option_id,...] ] del último intento
 *   $rendir_url       string  URL para rendir
 *   $back_url         string  volver al curso
 *
 * @package TeemsLMS
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$sin_preguntas = empty( $preguntas );
$restantes     = max( 0, (int) $max_intentos - (int) $n_intentos );
?>

<a class="lms-back" href="<?php echo esc_url( $back_url ); ?>"><i class="bi bi-arrow-left"></i> Volver al curso</a>

<div class="lms-coursecard">
	<div>
		<span class="lms-eyebrow">Evaluación del módulo</span>
		<h1 class="lms-coursecard__title"><?php echo esc_html( $modulo->title ); ?></h1>
		<p class="lms-coursecard__desc">
			Nota mínima para aprobar: <strong><?php echo (int) $nota_minima; ?>/10</strong> ·
			Intentos usados: <strong><?php echo (int) $n_intentos; ?>/<?php echo (int) $max_intentos; ?></strong>
		</p>
	</div>
</div>

<?php if ( $sin_preguntas ) : ?>
	<div class="lms-empty">
		<i class="bi bi-patch-question"></i>
		<p>Esta evaluación aún no tiene preguntas. Vuelve más tarde.</p>
	</div>
<?php else : ?>

	<!-- Tarjeta de estado -->
	<?php if ( $aprobada ) : ?>
		<div class="lms-evalresult lms-evalresult--ok">
			<i class="bi bi-trophy"></i>
			<div>
				<strong>¡Aprobaste la evaluación!</strong>
				<?php if ( $ultimo ) : ?><span>Nota: <?php echo esc_html( rtrim( rtrim( (string) $ultimo->score, '0' ), '.' ) ); ?>/10</span><?php endif; ?>
			</div>
		</div>
	<?php elseif ( $ultimo ) : ?>
		<div class="lms-evalresult lms-evalresult--fail">
			<i class="bi bi-x-octagon"></i>
			<div>
				<strong>No aprobaste todavía.</strong>
				<span>Nota: <?php echo esc_html( rtrim( rtrim( (string) $ultimo->score, '0' ), '.' ) ); ?>/10 ·
				<?php echo $can_take ? 'Te queda ' . (int) $restantes . ' intento(s).' : 'Sin intentos restantes: módulo cerrado.'; ?></span>
			</div>
		</div>
	<?php else : ?>
		<div class="lms-evalresult lms-evalresult--info">
			<i class="bi bi-pencil-square"></i>
			<div>
				<strong>Aún no has rendido esta evaluación.</strong>
				<span><?php echo (int) count( $preguntas ); ?> pregunta(s) · <?php echo (int) $max_intentos; ?> intentos disponibles.</span>
			</div>
		</div>
	<?php endif; ?>

	<!-- Botón rendir / reintentar -->
	<?php if ( $can_take ) : ?>
		<div class="lms-quiz__foot">
			<a class="lms-course__btn" href="<?php echo esc_url( $rendir_url ); ?>">
				<i class="bi bi-play-circle"></i> <?php echo $ultimo ? 'Reintentar evaluación' : 'Rendir evaluación'; ?>
			</a>
		</div>
	<?php endif; ?>

	<!-- Retroalimentación del último intento -->
	<?php if ( $ultimo ) : ?>
		<h2 class="lms-section-title">Revisión de tu último intento</h2>
		<div class="lms-feedback">
			<?php foreach ( $preguntas as $idx => $q ) : ?>
				<?php
				$correct_ids = array();
				foreach ( $q->options as $o ) {
					if ( (int) $o->is_correct === 1 ) {
						$correct_ids[] = (int) $o->id;
					}
				}
				$sel_ids = isset( $sel_map[ (int) $q->id ] ) ? $sel_map[ (int) $q->id ] : array();
				$ordc = $correct_ids; sort( $ordc );
				$ords = $sel_ids;     sort( $ords );
				$ok   = ( ! empty( $ordc ) && $ordc === $ords );
				?>
				<div class="lms-fbq">
					<p class="lms-fbq__text">
						<span class="lms-fbq__mark lms-fbq__mark--<?php echo $ok ? 'ok' : 'no'; ?>">
							<i class="bi <?php echo $ok ? 'bi-check-lg' : 'bi-x-lg'; ?>"></i>
						</span>
						<span><?php echo (int) ( $idx + 1 ); ?>. <?php echo esc_html( $q->question_text ); ?></span>
					</p>
					<ul class="lms-fbopts">
						<?php foreach ( $q->options as $o ) : ?>
							<?php
							$es_correcta = in_array( (int) $o->id, $correct_ids, true );
							$la_eligio   = in_array( (int) $o->id, $sel_ids, true );
							$clase       = '';
							if ( $es_correcta ) {
								$clase = 'is-correct';            // siempre marcar la correcta en verde
							} elseif ( $la_eligio ) {
								$clase = 'is-wrong';              // eligió una incorrecta
							}
							?>
							<li class="lms-fbopt <?php echo esc_attr( $clase ); ?>">
								<i class="bi <?php echo $es_correcta ? 'bi-check-circle-fill' : ( $la_eligio ? 'bi-x-circle-fill' : 'bi-circle' ); ?>"></i>
								<span><?php echo esc_html( $o->option_text ); ?></span>
								<?php if ( $la_eligio ) : ?><span class="lms-fbopt__tag">tu respuesta</span><?php endif; ?>
							</li>
						<?php endforeach; ?>
					</ul>
				</div>
			<?php endforeach; ?>
		</div>
	<?php endif; ?>

<?php endif; ?>
