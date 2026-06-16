<?php
/**
 * Vista: lista de cursos del estudiante.
 *
 * Variables recibidas:
 *   $cursos       array  lista de cursos [ titulo, desc, progreso, modulos, color ]
 *   $total        int    total de cursos
 *   $completados  int    cursos completados (100%)
 *   $en_curso     int    cursos en progreso
 *
 * @package TeemsLMS
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div class="lms-pagehead">
	<h1>Continúa tu aprendizaje</h1>
	<p>Tienes <strong><?php echo esc_html( $en_curso ); ?></strong> curso(s) en progreso. ¡Sigue así!</p>
</div>

<div class="lms-stats">
	<div class="lms-stat"><i class="bi bi-collection-play"></i><div><span class="lms-stat__num"><?php echo esc_html( $total ); ?></span><span class="lms-stat__lbl">Cursos inscritos</span></div></div>
	<div class="lms-stat"><i class="bi bi-hourglass-split"></i><div><span class="lms-stat__num"><?php echo esc_html( $en_curso ); ?></span><span class="lms-stat__lbl">En progreso</span></div></div>
	<div class="lms-stat"><i class="bi bi-check-circle"></i><div><span class="lms-stat__num"><?php echo esc_html( $completados ); ?></span><span class="lms-stat__lbl">Completados</span></div></div>
</div>

<h2 class="lms-section-title">Mis cursos</h2>

<?php if ( empty( $cursos ) ) : ?>
	<div class="lms-empty">
		<i class="bi bi-collection-play"></i>
		<p>Todavía no hay cursos disponibles. Cuando el administrador publique un curso, aparecerá aquí.</p>
	</div>
<?php else : ?>
	<div class="lms-courses">
		<?php foreach ( $cursos as $c ) : ?>
			<?php $curso_url = add_query_arg( array( 'vista' => 'curso', 'id' => (int) $c['id'] ) ); ?>
			<article class="lms-course">
				<div class="lms-course__cover" style="background: linear-gradient(135deg, <?php echo esc_attr( $c['color'] ); ?> 0%, #0b1f4d 130%);">
					<i class="bi bi-mortarboard"></i>
				</div>
				<div class="lms-course__body">
					<h3 class="lms-course__title"><?php echo esc_html( $c['titulo'] ); ?></h3>
					<p class="lms-course__desc"><?php echo esc_html( $c['desc'] ); ?></p>
					<div class="lms-course__meta">
						<span><i class="bi bi-layers"></i> <?php echo esc_html( $c['modulos'] ); ?> módulos</span>
						<span class="lms-course__pct"><?php echo esc_html( $c['progreso'] ); ?>%</span>
					</div>
					<div class="lms-progress"><div class="lms-progress__bar" style="width: <?php echo esc_attr( $c['progreso'] ); ?>%; background: <?php echo esc_attr( $c['color'] ); ?>;"></div></div>
					<a class="lms-course__btn" href="<?php echo esc_url( $curso_url ); ?>">
						<?php echo ( 100 === $c['progreso'] ) ? 'Repasar' : ( ( $c['progreso'] > 0 ) ? 'Continuar' : 'Empezar' ); ?>
						<i class="bi bi-arrow-right"></i>
					</a>
				</div>
			</article>
		<?php endforeach; ?>
	</div>
<?php endif; ?>

<p class="lms-demo-note"><i class="bi bi-info-circle"></i> El progreso (%) se activará cuando agreguemos el seguimiento de avance.</p>
