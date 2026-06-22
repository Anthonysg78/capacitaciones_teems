<?php
/**
 * Vista: lista de empresas (panel admin).
 *
 * Las empresas solo AGRUPAN estudiantes (no inician sesión). Desde aquí el
 * admin las crea/edita (en un modal), ve sus estudiantes y las borra. La
 * asignación estudiante→empresa se hace en la sección Usuarios.
 *
 * Variables recibidas:
 *   $items     array   cada item: [ empresa (objeto), estudiantes (int) ]
 *   $nuevo_url string  URL de respaldo para crear (sin JS)
 *   $list_url  string  URL base de esta lista
 *   $msg       string  mensaje de estado
 *
 * @package TeammsLMS
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div class="lms-pagehead lms-pagehead--row">
	<div>
		<h1>Empresas</h1>
		<p>Agrupa a tus estudiantes por empresa. La empresa no inicia sesión: es solo una etiqueta para organizar y reportar.</p>
	</div>
	<a class="lms-course__btn" href="<?php echo esc_url( $nuevo_url ); ?>" data-bs-toggle="modal" data-bs-target="#lms-modal-empresa" data-mode="nuevo"><i class="bi bi-building-add"></i> Nueva empresa</a>
</div>

<?php if ( 'saved' === $msg ) : ?>
	<div class="lms-notice lms-notice--ok"><i class="bi bi-check-circle"></i> Empresa guardada correctamente.</div>
<?php elseif ( 'deleted' === $msg ) : ?>
	<div class="lms-notice lms-notice--ok"><i class="bi bi-trash"></i> Empresa eliminada.</div>
<?php elseif ( 'datos' === $msg ) : ?>
	<div class="lms-notice lms-notice--err"><i class="bi bi-exclamation-triangle"></i> El nombre de la empresa es obligatorio.</div>
<?php elseif ( 'denied' === $msg ) : ?>
	<div class="lms-notice lms-notice--err"><i class="bi bi-shield-lock"></i> No tienes permiso para esta acción.</div>
<?php elseif ( 'expired' === $msg ) : ?>
	<div class="lms-notice lms-notice--err"><i class="bi bi-clock-history"></i> La sesión del formulario expiró. Inténtalo de nuevo.</div>
<?php endif; ?>

<?php if ( empty( $items ) ) : ?>
	<div class="lms-empty">
		<i class="bi bi-building"></i>
		<p>Todavía no hay empresas. ¡Crea la primera!</p>
		<a class="lms-course__btn d-inline-flex" href="<?php echo esc_url( $nuevo_url ); ?>" data-bs-toggle="modal" data-bs-target="#lms-modal-empresa" data-mode="nuevo"><i class="bi bi-building-add"></i> Nueva empresa</a>
	</div>
<?php else : ?>
	<div class="lms-tablewrap">
		<table class="lms-table">
			<thead>
				<tr>
					<th>Empresa</th>
					<th>Estudiantes</th>
					<th class="lms-table__actions">Acciones</th>
				</tr>
			</thead>
			<tbody>
				<?php foreach ( $items as $it ) : ?>
					<?php
					$empresa    = $it['empresa'];
					$ver_url    = add_query_arg( array( 'accion' => 'ver', 'id' => (int) $empresa->id ), $list_url );
					$borrar_url = wp_nonce_url(
						add_query_arg(
							array(
								'lms_action' => 'delete_company',
								'id'         => (int) $empresa->id,
								'redirect'   => rawurlencode( $list_url ),
							),
							$list_url
						),
						'lms_delete_company_' . (int) $empresa->id
					);
					?>
					<tr>
						<td>
							<a class="lms-table__title lms-link" href="<?php echo esc_url( $ver_url ); ?>" title="Ver estudiantes"><?php echo esc_html( $empresa->name ); ?></a>
						</td>
						<td>
							<a href="<?php echo esc_url( $ver_url ); ?>" title="Ver estudiantes">
								<span class="lms-pill <?php echo $it['estudiantes'] ? 'lms-pill--ok' : ''; ?>"><?php echo (int) $it['estudiantes']; ?></span>
							</a>
						</td>
						<td class="lms-table__actions">
							<a class="lms-iconbtn" href="<?php echo esc_url( $ver_url ); ?>" title="Ver estudiantes"><i class="bi bi-people"></i></a>
							<button type="button" class="lms-iconbtn" title="Editar"
								data-bs-toggle="modal" data-bs-target="#lms-modal-empresa"
								data-mode="editar" data-id="<?php echo (int) $empresa->id; ?>" data-name="<?php echo esc_attr( $empresa->name ); ?>"><i class="bi bi-pencil"></i></button>
							<a class="lms-iconbtn lms-iconbtn--danger" href="<?php echo esc_url( $borrar_url ); ?>" title="Eliminar empresa" onclick="return confirm('¿Eliminar la empresa <?php echo esc_js( $empresa->name ); ?>? Los estudiantes no se borran; solo quedan sin empresa asignada.');"><i class="bi bi-trash"></i></a>
						</td>
					</tr>
				<?php endforeach; ?>
			</tbody>
		</table>
	</div>
<?php endif; ?>

<!-- ============================ MODAL: EMPRESA ============================ -->
<div class="modal fade lms-modal" id="lms-modal-empresa" tabindex="-1" aria-hidden="true">
	<div class="modal-dialog modal-dialog-centered">
		<div class="modal-content">
			<form method="post" action="<?php echo esc_url( $list_url ); ?>">
				<input type="hidden" name="lms_action" value="save_company">
				<input type="hidden" name="redirect" value="<?php echo esc_url( $list_url ); ?>">
				<input type="hidden" name="company_id" id="lms-empresa-id" value="">
				<?php wp_nonce_field( 'lms_save_company' ); ?>
				<div class="modal-header">
					<h5 class="modal-title" id="lms-empresa-title">Nueva empresa</h5>
					<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
				</div>
				<div class="modal-body">
					<div class="lms-field">
						<label>Nombre de la empresa <span class="lms-req">*</span></label>
						<input type="text" name="name" id="lms-empresa-name" required placeholder="Ej. Constructora Andina S.A.">
					</div>
				</div>
				<div class="modal-footer">
					<button type="button" class="lms-btn-ghost" data-bs-dismiss="modal">Cancelar</button>
					<button type="submit" class="lms-course__btn"><i class="bi bi-check2"></i> Guardar</button>
				</div>
			</form>
		</div>
	</div>
</div>

<script>
( function () {
	var modal = document.getElementById( 'lms-modal-empresa' );
	if ( ! modal ) { return; }
	modal.addEventListener( 'show.bs.modal', function ( ev ) {
		var btn   = ev.relatedTarget || {};
		var mode  = btn.getAttribute ? btn.getAttribute( 'data-mode' ) : 'nuevo';
		var id    = btn.getAttribute ? ( btn.getAttribute( 'data-id' ) || '' ) : '';
		var name  = btn.getAttribute ? ( btn.getAttribute( 'data-name' ) || '' ) : '';
		modal.querySelector( '#lms-empresa-id' ).value   = ( 'editar' === mode ) ? id : '';
		modal.querySelector( '#lms-empresa-name' ).value = ( 'editar' === mode ) ? name : '';
		modal.querySelector( '#lms-empresa-title' ).textContent = ( 'editar' === mode ) ? 'Editar empresa' : 'Nueva empresa';
	} );
} )();
</script>
