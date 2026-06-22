<?php
/**
 * Vista: REPORTE imprimible de una empresa (standalone, sin sidebar).
 *
 * Pensada para "Imprimir → Guardar como PDF" del navegador (igual que los
 * certificados). Muestra, por cada estudiante de la empresa, su avance general
 * y el detalle por curso.
 *
 * Variables recibidas:
 *   $empresa      object  la empresa (id, name).
 *   $estudiantes  array   cada item: [ id, nombre, email, cursos, avance ]
 *                          donde avance = [ overall, n, cursos => [ title, pct, completo ] ]
 *   $fecha        string  fecha/hora de generación.
 *   $volver_url   string  URL para volver al panel.
 *
 * @package TeammsLMS
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<style>
	.lms-report { max-width: 900px; margin: 0 auto; padding: 24px; color: #1f2937; }
	.lms-report__head { border-bottom: 2px solid #2563eb; padding-bottom: 12px; margin-bottom: 20px; }
	.lms-report__head h1 { margin: 0 0 4px; font-size: 22px; }
	.lms-report__meta { color: #6b7280; font-size: 13px; }
	.lms-report__student { border: 1px solid #e5e7eb; border-radius: 10px; padding: 14px 16px; margin-bottom: 14px; page-break-inside: avoid; }
	.lms-report__student-top { display: flex; justify-content: space-between; align-items: baseline; gap: 12px; }
	.lms-report__name { font-weight: 600; font-size: 15px; }
	.lms-report__email { color: #6b7280; font-size: 12px; }
	.lms-report__big { font-size: 18px; font-weight: 700; color: #2563eb; white-space: nowrap; }
	.lms-report table { width: 100%; border-collapse: collapse; margin-top: 10px; font-size: 13px; }
	.lms-report th, .lms-report td { text-align: left; padding: 6px 8px; border-bottom: 1px solid #f0f0f0; }
	.lms-report__bar { background: #eef1f6; border-radius: 6px; height: 8px; width: 140px; overflow: hidden; }
	.lms-report__bar i { display: block; height: 100%; background: #2563eb; }
	.lms-report__ok { color: #15803d; font-weight: 600; }
	.lms-report__pend { color: #b45309; }
	.lms-report__bar i, .lms-report__big { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
	@media print { .lms-noprint { display: none !important; } .lms-report { padding: 0; } }
</style>

<div class="lms-report">

	<div class="lms-noprint" style="display:flex; gap:8px; justify-content:flex-end; margin-bottom:16px;">
		<a class="lms-btn-ghost" href="<?php echo esc_url( $volver_url ); ?>"><i class="bi bi-arrow-left"></i> Volver</a>
		<button type="button" class="lms-course__btn" onclick="window.print();"><i class="bi bi-printer"></i> Imprimir / Guardar PDF</button>
	</div>

	<div class="lms-report__head">
		<h1>Reporte de avance — <?php echo esc_html( $empresa->name ); ?></h1>
		<div class="lms-report__meta">
			<?php echo (int) count( $estudiantes ); ?> estudiante<?php echo 1 === count( $estudiantes ) ? '' : 's'; ?>
			· Generado el <?php echo esc_html( $fecha ); ?>
		</div>
	</div>

	<?php if ( empty( $estudiantes ) ) : ?>
		<p>Esta empresa todavía no tiene estudiantes asignados.</p>
	<?php else : ?>
		<?php foreach ( $estudiantes as $e ) : ?>
			<div class="lms-report__student">
				<div class="lms-report__student-top">
					<div>
						<div class="lms-report__name"><?php echo esc_html( $e['nombre'] ); ?></div>
						<div class="lms-report__email"><?php echo esc_html( $e['email'] ); ?></div>
					</div>
					<div class="lms-report__big"><?php echo (int) $e['avance']['overall']; ?>%</div>
				</div>

				<?php if ( empty( $e['avance']['cursos'] ) ) : ?>
					<div class="lms-report__email" style="margin-top:8px;">Sin cursos inscritos.</div>
				<?php else : ?>
					<table>
						<thead>
							<tr><th>Curso</th><th style="width:160px;">Avance</th><th style="width:120px;">Estado</th></tr>
						</thead>
						<tbody>
							<?php foreach ( $e['avance']['cursos'] as $c ) : ?>
								<tr>
									<td><?php echo esc_html( $c['title'] ); ?></td>
									<td>
										<div class="lms-report__bar"><i style="width: <?php echo (int) $c['pct']; ?>%;"></i></div>
										<span style="font-size:11px; color:#6b7280;"><?php echo (int) $c['pct']; ?>%</span>
									</td>
									<td>
										<?php if ( $c['completo'] ) : ?>
											<span class="lms-report__ok">Completado</span>
										<?php else : ?>
											<span class="lms-report__pend">En curso</span>
										<?php endif; ?>
									</td>
								</tr>
							<?php endforeach; ?>
						</tbody>
					</table>
				<?php endif; ?>
			</div>
		<?php endforeach; ?>
	<?php endif; ?>

</div>
