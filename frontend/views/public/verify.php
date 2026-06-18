<?php
/**
 * Vista: VERIFICACIÓN PÚBLICA de un certificado (sin login, sin sidebar).
 *
 * Se llega por el QR / enlace público (?vista=verificar&codigo=...). Confirma
 * si el certificado es auténtico y muestra sus datos.
 *
 * Variables recibidas:
 *   $cert    object|null  certificado encontrado (o null)
 *   $codigo  string       código consultado
 *
 * @package TeemsLMS
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$valido = ( null !== $cert );
?>
<div class="lms-verify">
	<div class="lms-verify__card">

		<div class="lms-verify__brand">
			<span class="lms-verify__logo"><i class="bi bi-mortarboard-fill"></i></span>
			Capacitaciones <strong>Teamms</strong>
		</div>

		<?php if ( $valido ) : ?>
			<div class="lms-verify__status lms-verify__status--ok">
				<i class="bi bi-patch-check-fill"></i>
				<span>Certificado válido</span>
			</div>

			<table class="lms-verify__data">
				<tr><th>Estudiante</th><td><?php echo esc_html( $cert->student_name ); ?></td></tr>
				<tr><th>Curso</th><td><?php echo esc_html( $cert->course_title ); ?></td></tr>
				<tr><th>Fecha de emisión</th><td><?php echo esc_html( mysql2date( 'd/m/Y', $cert->issued_at ) ); ?></td></tr>
				<tr><th>Código</th><td class="lms-verify__code"><?php echo esc_html( $cert->unique_code ); ?></td></tr>
			</table>

			<p class="lms-verify__note">Este certificado fue emitido por la plataforma y es auténtico.</p>
		<?php else : ?>
			<div class="lms-verify__status lms-verify__status--bad">
				<i class="bi bi-x-octagon-fill"></i>
				<span>Certificado no válido</span>
			</div>
			<p class="lms-verify__note">
				No encontramos ningún certificado con el código
				<strong><?php echo esc_html( $codigo ? $codigo : '(vacío)' ); ?></strong>.
				Verifica que el enlace o el código sean correctos.
			</p>
		<?php endif; ?>

	</div>
</div>
