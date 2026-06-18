<?php
/**
 * Vista: lista de certificados del estudiante.
 *
 * Variables recibidas:
 *   $certs  array   certificados (con course_title, unique_code, issued_at)
 *   $base   string  URL base de la app para armar los enlaces
 *
 * @package TeammsLMS
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div class="lms-pagehead">
	<h1>Mis Certificados</h1>
	<p>Cuando completas un curso (apruebas la evaluación de todos sus módulos), ganas un certificado verificable.</p>
</div>

<?php if ( empty( $certs ) ) : ?>
	<div class="lms-empty">
		<i class="bi bi-award"></i>
		<p>Aún no tienes certificados. Completa un curso (aprueba todos sus módulos) para obtener el tuyo.</p>
	</div>
<?php else : ?>
	<div class="lms-certs">
		<?php foreach ( $certs as $c ) : ?>
			<?php
			$ver_url = add_query_arg(
				array( 'vista' => 'certificado', 'codigo' => $c->unique_code ),
				$base
			);
			$fecha = mysql2date( 'd/m/Y', $c->issued_at );
			?>
			<article class="lms-cert">
				<div class="lms-cert__ribbon"><i class="bi bi-award-fill"></i></div>
				<div class="lms-cert__body">
					<span class="lms-cert__course">Curso completado</span>
					<h3 class="lms-cert__module"><?php echo esc_html( $c->course_title ); ?></h3>
					<span class="lms-cert__date"><i class="bi bi-calendar-check"></i> Emitido el <?php echo esc_html( $fecha ); ?></span>
					<a class="lms-course__btn" href="<?php echo esc_url( $ver_url ); ?>" target="_blank" rel="noopener">
						<i class="bi bi-eye"></i> Ver certificado
					</a>
				</div>
			</article>
		<?php endforeach; ?>
	</div>
<?php endif; ?>
