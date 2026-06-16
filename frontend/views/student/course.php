<?php
/**
 * Vista: VISOR de curso del estudiante (solo lectura).
 *
 * Muestra la estructura del curso: módulos → subtemas → contenidos. El texto
 * se lee en línea; el video, PDF o recurso se abren con un botón.
 *
 * Variables recibidas:
 *   $curso        object  curso que se está viendo
 *   $arbol        array   módulos anidados con subtemas y contenidos
 *   $volver_url   string  URL para volver a "Mis cursos"
 *   $completados  array   ids de contenidos ya completados por el usuario
 *   $hechos       int     cuántos contenidos completó
 *   $total        int     total de contenidos del curso
 *   $percent      int     porcentaje de avance (0-100)
 *   $viewer_url   string  URL de esta página (para volver tras marcar)
 *
 * @package TeemsLMS
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$tipos_lbl = LMS_Content::tipos();
$tipos_ico = array(
	'texto'   => 'bi-file-text',
	'video'   => 'bi-play-circle',
	'pdf'     => 'bi-file-earmark-pdf',
	'recurso' => 'bi-link-45deg',
);
// Texto del botón para abrir contenidos con enlace.
$btn_lbl = array(
	'video'   => 'Ver video',
	'pdf'     => 'Abrir PDF',
	'recurso' => 'Abrir recurso',
);
?>

<a class="lms-back" href="<?php echo esc_url( $volver_url ); ?>"><i class="bi bi-arrow-left"></i> Volver a mis cursos</a>

<div class="lms-coursecard">
	<div>
		<span class="lms-eyebrow">Curso</span>
		<h1 class="lms-coursecard__title"><?php echo esc_html( $curso->title ); ?></h1>
		<?php if ( ! empty( $curso->description ) ) : ?>
			<p class="lms-coursecard__desc"><?php echo esc_html( $curso->description ); ?></p>
		<?php endif; ?>
	</div>
</div>

<?php if ( $total > 0 ) : ?>
	<div class="lms-vprogress">
		<div class="lms-vprogress__top">
			<span class="lms-vprogress__label">Tu avance</span>
			<span class="lms-vprogress__num"><?php echo (int) $hechos; ?> de <?php echo (int) $total; ?> · <strong><?php echo (int) $percent; ?>%</strong></span>
		</div>
		<div class="lms-progress"><div class="lms-progress__bar" style="width: <?php echo (int) $percent; ?>%; background: #2563eb;"></div></div>
	</div>
<?php endif; ?>

<?php if ( empty( $arbol ) ) : ?>
	<div class="lms-empty">
		<i class="bi bi-journal-x"></i>
		<p>Este curso aún no tiene contenido. Vuelve más tarde.</p>
	</div>
<?php else : ?>
	<div class="lms-viewer">
		<?php foreach ( $arbol as $nodo_m ) : ?>
			<?php
			$m           = $nodo_m['modulo'];
			$subtemas    = $nodo_m['subtemas'];
			$n_preguntas = $nodo_m['n_preguntas'];
			$aprobada    = $nodo_m['aprobada'];
			$eval_url    = $nodo_m['eval_url'];
			?>
			<section class="lms-vmod">
				<div class="lms-vmod__head">
					<span class="lms-tbadge"><?php echo (int) $m->order_index; ?></span>
					<h2 class="lms-vmod__title"><?php echo esc_html( $m->title ); ?></h2>
				</div>

				<?php if ( empty( $subtemas ) ) : ?>
					<p class="lms-tempty">Este módulo aún no tiene subtemas.</p>
				<?php else : ?>
					<?php foreach ( $subtemas as $nodo_s ) : ?>
						<?php
						$s          = $nodo_s['subtema'];
						$contenidos = $nodo_s['contenidos'];
						?>
						<div class="lms-vsub">
							<h3 class="lms-vsub__title"><?php echo esc_html( $s->title ); ?></h3>
							<?php if ( ! empty( $s->description ) ) : ?>
								<p class="lms-vsub__desc"><?php echo esc_html( $s->description ); ?></p>
							<?php endif; ?>

							<?php if ( empty( $contenidos ) ) : ?>
								<p class="lms-tempty">Este subtema aún no tiene contenidos.</p>
							<?php else : ?>
								<?php foreach ( $contenidos as $c ) : ?>
									<?php
									$tipo_lbl = isset( $tipos_lbl[ $c->type ] ) ? $tipos_lbl[ $c->type ] : $c->type;
									$tipo_ico = isset( $tipos_ico[ $c->type ] ) ? $tipos_ico[ $c->type ] : 'bi-dot';
									?>
									<?php $done = in_array( (int) $c->id, $completados, true ); ?>
									<div class="lms-vcontent lms-vcontent--<?php echo esc_attr( $c->type ); ?><?php echo $done ? ' is-done' : ''; ?>">
										<div class="lms-vcontent__head">
											<span class="lms-tleaf__icon"><i class="bi <?php echo esc_attr( $tipo_ico ); ?>"></i></span>
											<span class="lms-vcontent__title"><?php echo esc_html( $c->title ); ?></span>
											<span class="lms-tleaf__type"><?php echo esc_html( $tipo_lbl ); ?></span>
										</div>

										<?php if ( 'texto' === $c->type ) : ?>
											<?php if ( '' !== trim( (string) $c->content_text ) ) : ?>
												<div class="lms-vcontent__text"><?php echo wpautop( wp_kses_post( $c->content_text ) ); ?></div>
											<?php endif; ?>
										<?php elseif ( ! empty( $c->content_url ) ) : ?>
											<a class="lms-btn-outline lms-btn-outline--sm" href="<?php echo esc_url( $c->content_url ); ?>" target="_blank" rel="noopener noreferrer">
												<i class="bi <?php echo esc_attr( $tipo_ico ); ?>"></i>
												<?php echo esc_html( isset( $btn_lbl[ $c->type ] ) ? $btn_lbl[ $c->type ] : 'Abrir' ); ?>
												<i class="bi bi-box-arrow-up-right"></i>
											</a>
										<?php else : ?>
											<p class="lms-tempty">Este contenido aún no tiene enlace.</p>
										<?php endif; ?>

										<form class="lms-vcontent__foot" method="post" action="<?php echo esc_url( $viewer_url ); ?>">
											<input type="hidden" name="lms_action" value="toggle_progress">
											<input type="hidden" name="content_id" value="<?php echo (int) $c->id; ?>">
											<input type="hidden" name="redirect" value="<?php echo esc_url( $viewer_url ); ?>">
											<?php wp_nonce_field( 'lms_toggle_progress' ); ?>
											<button type="submit" class="lms-checkbtn<?php echo $done ? ' is-done' : ''; ?>">
												<i class="bi <?php echo $done ? 'bi-check-circle-fill' : 'bi-circle'; ?>"></i>
												<?php echo $done ? 'Completado' : 'Marcar como completado'; ?>
											</button>
										</form>
									</div>
								<?php endforeach; ?>
							<?php endif; ?>
						</div>
					<?php endforeach; ?>
				<?php endif; ?>

				<?php if ( $n_preguntas > 0 ) : ?>
					<a class="lms-vexam <?php echo $aprobada ? 'is-ok' : ''; ?>" href="<?php echo esc_url( $eval_url ); ?>">
						<span class="lms-vexam__icon"><i class="bi <?php echo $aprobada ? 'bi-trophy-fill' : 'bi-card-checklist'; ?>"></i></span>
						<span class="lms-vexam__txt">
							<strong>Evaluación del módulo</strong>
							<span><?php echo $aprobada ? '¡Aprobada! Ver resultado' : (int) $n_preguntas . ' pregunta(s) · pon a prueba lo aprendido'; ?></span>
						</span>
						<span class="lms-vexam__go"><?php echo $aprobada ? 'Ver' : 'Rendir'; ?> <i class="bi bi-arrow-right"></i></span>
					</a>
				<?php endif; ?>
			</section>
		<?php endforeach; ?>
	</div>
<?php endif; ?>
