<?php
/**
 * Vista: Reportes por empresa.
 *
 * Tabla con una fila por empresa (más "Sin empresa") y sus métricas:
 * estudiantes, inscripciones, cursos completados, certificados y % de avance.
 *
 * Variables recibidas:
 *   $filas    array  cada fila: [ id, name, ver_url, estudiantes, inscripciones, completados, certificados ]
 *   $totales  array  [ estudiantes, inscripciones, completados, certificados ]
 *
 * @package TeammsLMS
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/** % de avance: cursos completados sobre inscripciones. */
$pct = function ( $completados, $inscripciones ) {
	return $inscripciones > 0 ? round( $completados / $inscripciones * 100 ) : 0;
};
?>
<div class="lms-pagehead">
	<h1>Reportes por empresa</h1>
	<p>Avance de tus estudiantes agrupado por empresa.</p>
</div>

<?php if ( 0 === (int) $totales['estudiantes'] ) : ?>
	<div class="lms-empty">
		<i class="bi bi-bar-chart"></i>
		<p>Todavía no hay estudiantes para reportar. Crea estudiantes y asígnales una empresa.</p>
	</div>
<?php else : ?>
	<div class="lms-tablewrap">
		<table class="lms-table">
			<thead>
				<tr>
					<th>Empresa</th>
					<th>Estudiantes</th>
					<th>Inscripciones</th>
					<th>Completados</th>
					<th>Certificados</th>
					<th>Avance</th>
					<th class="lms-table__actions">PDF</th>
				</tr>
			</thead>
			<tbody>
				<?php foreach ( $filas as $f ) : ?>
					<tr>
						<td>
							<?php if ( $f['ver_url'] ) : ?>
								<a class="lms-table__title lms-link" href="<?php echo esc_url( $f['ver_url'] ); ?>" title="Ver estudiantes"><?php echo esc_html( $f['name'] ); ?></a>
							<?php else : ?>
								<span class="lms-table__title" style="color: var(--muted);"><?php echo esc_html( $f['name'] ); ?></span>
							<?php endif; ?>
						</td>
						<td><span class="lms-pill <?php echo $f['estudiantes'] ? 'lms-pill--ok' : ''; ?>"><?php echo (int) $f['estudiantes']; ?></span></td>
						<td><?php echo (int) $f['inscripciones']; ?></td>
						<td><?php echo (int) $f['completados']; ?></td>
						<td><?php echo (int) $f['certificados']; ?></td>
						<td><?php echo (int) $pct( $f['completados'], $f['inscripciones'] ); ?>%</td>
						<td class="lms-table__actions">
							<?php if ( ! empty( $f['pdf_url'] ) ) : ?>
								<a class="lms-iconbtn" href="<?php echo esc_url( $f['pdf_url'] ); ?>" target="_blank" rel="noopener" title="Descargar PDF de <?php echo esc_attr( $f['name'] ); ?>"><i class="bi bi-file-earmark-pdf"></i></a>
							<?php else : ?>
								<span style="color: var(--muted);">—</span>
							<?php endif; ?>
						</td>
					</tr>
				<?php endforeach; ?>
			</tbody>
			<tfoot>
				<tr>
					<th>Total</th>
					<th><?php echo (int) $totales['estudiantes']; ?></th>
					<th><?php echo (int) $totales['inscripciones']; ?></th>
					<th><?php echo (int) $totales['completados']; ?></th>
					<th><?php echo (int) $totales['certificados']; ?></th>
					<th><?php echo (int) $pct( $totales['completados'], $totales['inscripciones'] ); ?>%</th>
					<th></th>
				</tr>
			</tfoot>
		</table>
	</div>
<?php endif; ?>
