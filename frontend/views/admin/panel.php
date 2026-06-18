<?php
/**
 * Vista: panel principal del administrador.
 *
 * Variables recibidas (conteos reales de la BD):
 *   $cursos, $modulos, $inscripciones, $certificados  int
 *
 * @package TeammsLMS
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div class="lms-pagehead">
	<h1>Panel de administración</h1>
	<p>Gestiona toda la plataforma desde aquí: cursos, usuarios y más.</p>
</div>

<div class="lms-stats">
	<div class="lms-stat"><i class="bi bi-book"></i><div><span class="lms-stat__num"><?php echo esc_html( $cursos ); ?></span><span class="lms-stat__lbl">Cursos</span></div></div>
	<div class="lms-stat"><i class="bi bi-layers"></i><div><span class="lms-stat__num"><?php echo esc_html( $modulos ); ?></span><span class="lms-stat__lbl">Módulos</span></div></div>
	<div class="lms-stat"><i class="bi bi-mortarboard"></i><div><span class="lms-stat__num"><?php echo esc_html( $inscripciones ); ?></span><span class="lms-stat__lbl">Inscripciones</span></div></div>
	<div class="lms-stat"><i class="bi bi-award"></i><div><span class="lms-stat__num"><?php echo esc_html( $certificados ); ?></span><span class="lms-stat__lbl">Certificados</span></div></div>
</div>

<h2 class="lms-section-title">Accesos rápidos</h2>
<div class="lms-actions">
	<a class="lms-action" href="<?php echo esc_url( add_query_arg( 'vista', 'cursos' ) ); ?>"><i class="bi bi-journal-plus"></i><span>Crear curso</span></a>
	<a class="lms-action" href="<?php echo esc_url( add_query_arg( 'vista', 'usuarios' ) ); ?>"><i class="bi bi-person-plus"></i><span>Invitar usuario</span></a>
	<a class="lms-action" href="<?php echo esc_url( add_query_arg( 'vista', 'preguntas' ) ); ?>"><i class="bi bi-patch-question"></i><span>Banco de preguntas</span></a>
</div>

<p class="lms-demo-note"><i class="bi bi-info-circle"></i> Las secciones de gestión se irán activando semana a semana. Las estadísticas ya son reales (leídas de la base de datos).</p>
