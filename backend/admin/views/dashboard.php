<?php
/**
 * Vista: Dashboard del administrador del LMS.
 *
 * Recibe desde class-lms-admin.php:
 *   $stats             (array con conteos)
 *   $tablas_existentes (int, nº de tablas wp_lms_)
 *
 * @package TeemsLMS
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Definimos las tarjetas de estadísticas en un arreglo para no repetir HTML.
$tarjetas = array(
	array( 'label' => 'Empresas',      'valor' => $stats['empresas'],      'icon' => 'building',   'color' => '#2563eb' ),
	array( 'label' => 'Cursos',        'valor' => $stats['cursos'],        'icon' => 'book',       'color' => '#7c3aed' ),
	array( 'label' => 'Módulos',       'valor' => $stats['modulos'],       'icon' => 'layers',     'color' => '#0891b2' ),
	array( 'label' => 'Inscripciones', 'valor' => $stats['inscripciones'], 'icon' => 'users',      'color' => '#059669' ),
	array( 'label' => 'Certificados',  'valor' => $stats['certificados'],  'icon' => 'award',      'color' => '#d97706' ),
);

$db_ok = ( 17 === $tablas_existentes );
?>

<div class="wrap lms-wrap">

	<div class="lms-hero">
		<div class="lms-hero__icon">🎓</div>
		<div>
			<h1 class="lms-hero__title">Capacitaciones Teems</h1>
			<p class="lms-hero__subtitle">Plataforma de capacitación empresarial · Panel de administración</p>
		</div>
		<span class="lms-badge-version">v<?php echo esc_html( TEEMS_LMS_VERSION ); ?></span>
	</div>

	<!-- Estado del sistema -->
	<div class="lms-status <?php echo $db_ok ? 'lms-status--ok' : 'lms-status--error'; ?>">
		<?php if ( $db_ok ) : ?>
			<span class="lms-status__dot"></span>
			<strong>Sistema operativo.</strong> Las <?php echo esc_html( $tablas_existentes ); ?> tablas de la base de datos están creadas y conectadas correctamente.
		<?php else : ?>
			<span class="lms-status__dot"></span>
			<strong>Atención.</strong> Solo se detectaron <?php echo esc_html( $tablas_existentes ); ?> de 17 tablas. Desactiva y reactiva el plugin.
		<?php endif; ?>
	</div>

	<!-- Tarjetas de estadísticas -->
	<div class="lms-cards">
		<?php foreach ( $tarjetas as $t ) : ?>
			<div class="lms-card">
				<div class="lms-card__bar" style="background: <?php echo esc_attr( $t['color'] ); ?>"></div>
				<div class="lms-card__value"><?php echo esc_html( $t['valor'] ); ?></div>
				<div class="lms-card__label"><?php echo esc_html( $t['label'] ); ?></div>
			</div>
		<?php endforeach; ?>
	</div>

	<!-- Próximos pasos -->
	<div class="lms-panel">
		<h2 class="lms-panel__title">📋 Próximos pasos del proyecto</h2>
		<ul class="lms-steps">
			<li class="lms-steps__done">Semana 1 — Estructura base y 17 tablas de la base de datos</li>
			<li class="lms-steps__current">Semana 2 — Roles del sistema, invitaciones y activación de cuenta</li>
			<li>Semana 3 — Gestión de empresas y usuarios</li>
			<li>Semana 4 — Cursos, módulos y subtemas</li>
			<li>Semana 5 en adelante — Contenidos, evaluaciones, certificados…</li>
		</ul>
		<p class="lms-note">Esta es una vista de bienvenida temporal. Las secciones reales (Empresas, Usuarios, Cursos…) se irán agregando semana a semana.</p>
	</div>

</div>
