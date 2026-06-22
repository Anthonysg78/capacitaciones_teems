<?php
/**
 * Vista: avance de UN estudiante, curso por curso (dashboard).
 *
 * Variables recibidas:
 *   $nombre    string  nombre del estudiante.
 *   $email     string  correo del estudiante.
 *   $empresa   string  nombre de la empresa (o '' si no tiene / no vino).
 *   $avance    array   [ overall (int), n (int), cursos => [ title, pct, completo ] ]
 *   $back_url  string  URL para volver (a la empresa o a la lista).
 *
 * @package TeammsLMS
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div class="lms-pagehead lms-pagehead--row">
	<div>
		<h1><?php echo esc_html( $nombre ); ?></h1>
		<p>
			<?php echo esc_html( $email ); ?>
			<?php if ( $empresa ) : ?>· <i class="bi bi-building"></i> <?php echo esc_html( $empresa ); ?><?php endif; ?>
		</p>
	</div>
	<a class="lms-btn-ghost" href="<?php echo esc_url( $back_url ); ?>"><i class="bi bi-arrow-left"></i> Volver</a>
</div>

<!-- Avance general -->
<div class="lms-vprogress" style="margin-bottom: 24px;">
	<div class="lms-vprogress__top">
		<span class="lms-vprogress__label">Avance general (<?php echo (int) $avance['n']; ?> curso<?php echo 1 === (int) $avance['n'] ? '' : 's'; ?>)</span>
		<span class="lms-vprogress__num"><strong><?php echo (int) $avance['overall']; ?>%</strong></span>
	</div>
	<div class="lms-progress"><div class="lms-progress__bar" style="width: <?php echo (int) $avance['overall']; ?>%; background: #2563eb;"></div></div>
</div>

<?php if ( empty( $avance['cursos'] ) ) : ?>
	<div class="lms-empty">
		<i class="bi bi-book"></i>
		<p>Este estudiante todavía no está inscrito en ningún curso.</p>
	</div>
<?php else : ?>
	<div class="lms-tablewrap">
		<table class="lms-table">
			<thead>
				<tr>
					<th>Curso</th>
					<th>Avance</th>
					<th>Estado</th>
				</tr>
			</thead>
			<tbody>
				<?php foreach ( $avance['cursos'] as $c ) : ?>
					<tr>
						<td><div class="lms-table__title"><?php echo esc_html( $c['title'] ); ?></div></td>
						<td>
							<div class="lms-progress" style="min-width:160px;"><div class="lms-progress__bar" style="width: <?php echo (int) $c['pct']; ?>%; background: #2563eb;"></div></div>
							<div style="color: var(--muted); font-size: 12px; margin-top:4px;"><?php echo (int) $c['pct']; ?>%</div>
						</td>
						<td>
							<?php if ( $c['completo'] ) : ?>
								<span class="lms-tag lms-tag--ok"><i class="bi bi-check-circle"></i> Completado</span>
							<?php else : ?>
								<span class="lms-tag lms-tag--draft">En curso</span>
							<?php endif; ?>
						</td>
					</tr>
				<?php endforeach; ?>
			</tbody>
		</table>
	</div>
<?php endif; ?>
