<?php
/**
 * Vista: CERTIFICADO imprimible (standalone, sin sidebar).
 *
 * Se abre por código (?vista=certificado&codigo=...). Incluye QR que apunta a
 * la verificación pública. Botón "Imprimir" usa la impresión del navegador
 * (Guardar como PDF).
 *
 * Variables recibidas:
 *   $cert        object|null  datos del certificado (o null si el código no existe)
 *   $verify_url  string       URL pública de verificación (la que codifica el QR)
 *   $back_url    string       volver a "Mis certificados"
 *
 * @package TeammsLMS
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! $cert ) : ?>
	<div class="lms-certpage">
		<div class="lms-diploma lms-diploma--error">
			<i class="bi bi-exclamation-triangle"></i>
			<h1>Certificado no encontrado</h1>
			<p>El código no corresponde a ningún certificado emitido.</p>
		</div>
	</div>
	<?php
	return;
endif;

$fecha   = mysql2date( 'd \d\e F \d\e Y', $cert->issued_at );
$qr_src  = 'https://api.qrserver.com/v1/create-qr-code/?size=150x150&margin=0&data=' . rawurlencode( $verify_url );
?>
<div class="lms-certpage">

	<div class="lms-certbar lms-noprint">
		<a class="lms-btn-ghost" href="<?php echo esc_url( $back_url ); ?>"><i class="bi bi-arrow-left"></i> Mis certificados</a>
		<button type="button" class="lms-course__btn" onclick="window.print();"><i class="bi bi-printer"></i> Imprimir / Guardar PDF</button>
	</div>

	<div class="lms-diploma">
		<div class="lms-diploma__head">
			<span class="lms-diploma__logo"><i class="bi bi-mortarboard-fill"></i></span>
			<span class="lms-diploma__brand">Capacitaciones <strong>Teamms</strong></span>
		</div>

		<p class="lms-diploma__eyebrow">Certificado de aprobación</p>
		<p class="lms-diploma__intro">Se otorga el presente certificado a</p>
		<h1 class="lms-diploma__name"><?php echo esc_html( $cert->student_name ); ?></h1>

		<p class="lms-diploma__intro">por haber completado y aprobado el curso</p>
		<h2 class="lms-diploma__module"><?php echo esc_html( $cert->course_title ); ?></h2>

		<div class="lms-diploma__foot">
			<div class="lms-diploma__sign">
				<span class="lms-diploma__date">Emitido el <?php echo esc_html( $fecha ); ?></span>
				<span class="lms-diploma__code">Código: <?php echo esc_html( $cert->unique_code ); ?></span>
				<a class="lms-diploma__verify" href="<?php echo esc_url( $verify_url ); ?>" target="_blank" rel="noopener">Verificar autenticidad</a>
			</div>
			<div class="lms-diploma__qr">
				<img src="<?php echo esc_url( $qr_src ); ?>" alt="Código QR de verificación" width="120" height="120">
				<span>Escanea para verificar</span>
			</div>
		</div>
	</div>
</div>
