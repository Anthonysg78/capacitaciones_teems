<?php
/**
 * Vista: estudiantes de UNA empresa.
 *
 * Variables recibidas:
 *   $empresa       object  la empresa (id, name).
 *   $estudiantes   array   cada item: [ id, nombre, email, cursos ].
 *   $list_url      string  URL de la lista de empresas (volver).
 *   $usuarios_url  string  URL de la sección Usuarios (para asignar empresas).
 *
 * @package TeammsLMS
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div class="lms-pagehead lms-pagehead--row">
	<div>
		<h1><i class="bi bi-building"></i> <?php echo esc_html( $empresa->name ); ?></h1>
		<p>Estudiantes asignados a esta empresa (<?php echo count( $estudiantes ); ?>).</p>
	</div>
	<a class="lms-btn-ghost" href="<?php echo esc_url( $list_url ); ?>"><i class="bi bi-arrow-left"></i> Volver a Empresas</a>
</div>

<?php if ( empty( $estudiantes ) ) : ?>
	<div class="lms-empty">
		<i class="bi bi-people"></i>
		<p>Esta empresa todavía no tiene estudiantes asignados.</p>
		<a class="lms-course__btn d-inline-flex" href="<?php echo esc_url( $usuarios_url ); ?>"><i class="bi bi-person-gear"></i> Asignar desde Usuarios</a>
	</div>
<?php else : ?>
	<div class="lms-tablewrap">
		<table class="lms-table">
			<thead>
				<tr>
					<th>Estudiante</th>
					<th>Cursos</th>
				</tr>
			</thead>
			<tbody>
				<?php foreach ( $estudiantes as $e ) : ?>
					<tr>
						<td>
							<div class="lms-table__title"><?php echo esc_html( $e['nombre'] ); ?></div>
							<div style="color: var(--muted); font-size: 13px;"><?php echo esc_html( $e['email'] ); ?></div>
						</td>
						<td><span class="lms-pill <?php echo $e['cursos'] ? 'lms-pill--ok' : ''; ?>"><?php echo (int) $e['cursos']; ?></span></td>
					</tr>
				<?php endforeach; ?>
			</tbody>
		</table>
	</div>
<?php endif; ?>
