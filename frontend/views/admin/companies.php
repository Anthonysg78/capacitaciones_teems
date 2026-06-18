
<?php
/**
 * Vista: lista de EMPRESAS (panel admin) con modal para crear/editar.
 *
 * Mismo patrón que la estructura del curso: tabla + modal Bootstrap que se abre
 * sin salir de la página. El formulario se envía normal y vuelve aquí.
 *
 * Variables recibidas:
 *   $empresas  array   lista de empresas (objetos de la BD)
 *   $list_url  string  URL de esta página (volver / redirigir tras guardar)
 *   $msg       string  mensaje de estado
 *
 * @package TeammsLMS
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/** Ayudante: enlace de borrado con nonce que vuelve a esta página. */
$borrar_empresa_url = function ( $id ) use ( $list_url ) {
	return wp_nonce_url(
		add_query_arg(
			array( 'lms_action' => 'delete_company', 'id' => (int) $id, 'redirect' => rawurlencode( $list_url ) ),
			$list_url
		),
		'lms_delete_company_' . (int) $id
	);
};
?>
<div class="lms-pagehead lms-pagehead--row">
	<div>
		<h1>Empresas</h1>
		<p>Empresas clientes que inscriben a sus colaboradores.</p>
	</div>
	<a class="lms-course__btn" href="#" data-modal-trigger data-modal="empresa" data-mode="new">
		<i class="bi bi-plus-lg"></i> Nueva empresa
	</a>
</div>

<?php if ( 'saved' === $msg ) : ?>
	<div class="lms-notice lms-notice--ok"><i class="bi bi-check-circle"></i> Empresa guardada correctamente.</div>
<?php elseif ( 'deleted' === $msg ) : ?>
	<div class="lms-notice lms-notice--ok"><i class="bi bi-trash"></i> Empresa eliminada.</div>
<?php elseif ( 'error' === $msg ) : ?>
	<div class="lms-notice lms-notice--err"><i class="bi bi-exclamation-triangle"></i> El nombre es obligatorio.</div>
<?php elseif ( 'expired' === $msg ) : ?>
	<div class="lms-notice lms-notice--err"><i class="bi bi-clock-history"></i> El enlace expiró. Vuelve a intentarlo.</div>
<?php endif; ?>

<?php if ( empty( $empresas ) ) : ?>
	<div class="lms-empty">
		<i class="bi bi-building"></i>
		<p>Todavía no hay empresas registradas. ¡Crea la primera!</p>
		<a class="lms-course__btn d-inline-flex" href="#" data-modal-trigger data-modal="empresa" data-mode="new">
			<i class="bi bi-plus-lg"></i> Nueva empresa
		</a>
	</div>
<?php else : ?>
	<div class="lms-tablewrap">
		<table class="lms-table">
			<thead>
				<tr>
					<th>Empresa</th>
					<th>Email de contacto</th>
					<th style="width:110px;">Estado</th>
					<th class="lms-table__actions">Acciones</th>
				</tr>
			</thead>
			<tbody>
				<?php foreach ( $empresas as $e ) : ?>
					<tr>
						<td class="lms-table__title">
							<span class="lms-coninfo">
								<?php if ( ! empty( $e->logo_url ) ) : ?>
									<img class="lms-logo" src="<?php echo esc_url( $e->logo_url ); ?>" alt="Logo de <?php echo esc_attr( $e->name ); ?>">
								<?php else : ?>
									<span class="lms-logo lms-logo--ph"><?php echo esc_html( strtoupper( mb_substr( $e->name, 0, 1 ) ) ); ?></span>
								<?php endif; ?>
								<span><?php echo esc_html( $e->name ); ?></span>
							</span>
						</td>
						<td>
							<?php if ( ! empty( $e->contact_email ) ) : ?>
								<a href="mailto:<?php echo esc_attr( $e->contact_email ); ?>"><?php echo esc_html( $e->contact_email ); ?></a>
							<?php else : ?>
								<span class="lms-muted">—</span>
							<?php endif; ?>
						</td>
						<td>
							<?php if ( (int) $e->active === 1 ) : ?>
								<span class="lms-pill lms-pill--ok">Activa</span>
							<?php else : ?>
								<span class="lms-pill">Inactiva</span>
							<?php endif; ?>
						</td>
						<td class="lms-table__actions">
							<a class="lms-iconbtn" href="#" title="Editar empresa"
							   data-modal-trigger data-modal="empresa" data-mode="edit"
							   data-id="<?php echo (int) $e->id; ?>"
							   data-name="<?php echo esc_attr( $e->name ); ?>"
							   data-email="<?php echo esc_attr( $e->contact_email ); ?>"
							   data-active="<?php echo (int) $e->active; ?>"
							   data-logo="<?php echo esc_url( $e->logo_url ); ?>"><i class="bi bi-pencil"></i></a>
							<a class="lms-iconbtn lms-iconbtn--danger" href="<?php echo esc_url( $borrar_empresa_url( $e->id ) ); ?>" title="Borrar empresa" onclick="return confirm('¿Borrar esta empresa? Esta acción no se puede deshacer.');"><i class="bi bi-trash"></i></a>
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
			<form method="post" action="<?php echo esc_url( $list_url ); ?>" enctype="multipart/form-data">
				<input type="hidden" name="lms_action" value="save_company">
				<input type="hidden" name="company_id" value="0" data-field="id">
				<input type="hidden" name="redirect" value="<?php echo esc_url( $list_url ); ?>">
				<input type="hidden" name="current_logo" value="">
				<?php wp_nonce_field( 'lms_save_company' ); ?>
				<div class="modal-header">
					<h5 class="modal-title" data-modal-title>Nueva empresa</h5>
					<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
				</div>
				<div class="modal-body">
					<div class="lms-field">
						<label>Nombre de la empresa <span class="lms-req">*</span></label>
						<input type="text" name="name" required placeholder="Ej. Acme S.A.">
					</div>
					<div class="lms-field">
						<label>Email de contacto</label>
						<input type="email" name="contact_email" placeholder="contacto@empresa.com">
					</div>
					<div class="lms-field">
						<label>Logo de la empresa</label>
						<div class="lms-logoupload">
							<img data-logo-preview src="" alt="" style="display:none;">
							<input type="file" name="logo_file" accept="image/*">
						</div>
						<p class="lms-help">Opcional (PNG, JPG, SVG). Si editas y no subes uno nuevo, se conserva el actual.</p>
					</div>
					<div class="lms-field lms-field--check">
						<label>
							<input type="checkbox" name="active" value="1" checked>
							Empresa activa (puede inscribir colaboradores)
						</label>
					</div>
				</div>
				<div class="modal-footer">
					<button type="button" class="lms-btn-ghost" data-bs-dismiss="modal">Cancelar</button>
					<button type="submit" class="lms-course__btn"><i class="bi bi-save"></i> Guardar empresa</button>
				</div>
			</form>
		</div>
	</div>
</div>

<script>
( function () {
	var titulos = { empresa: [ 'Nueva empresa', 'Editar empresa' ] };

	function campo( modal, name )  { return modal.querySelector( '[name="' + name + '"]' ); }
	function campoData( modal, f ) { return modal.querySelector( '[data-field="' + f + '"]' ); }

	document.querySelectorAll( '[data-modal-trigger]' ).forEach( function ( t ) {
		t.addEventListener( 'click', function ( e ) {
			if ( ! window.bootstrap ) { return; } // sin JS de Bootstrap: no hacemos nada (respaldo).
			e.preventDefault();
			var d      = t.dataset;
			var esEdit = ( d.mode === 'edit' );
			var modal  = document.getElementById( 'lms-modal-' + d.modal );
			if ( ! modal ) { return; }

			modal.querySelector( '[data-modal-title]' ).textContent = titulos[ d.modal ][ esEdit ? 1 : 0 ];

			var fId    = campoData( modal, 'id' );             if ( fId )    { fId.value = d.id || '0'; }
			var fName  = campo( modal, 'name' );               if ( fName )  { fName.value = d.name || ''; }
			var fEmail = campo( modal, 'contact_email' );      if ( fEmail ) { fEmail.value = d.email || ''; }
			var fAct   = campo( modal, 'active' );             if ( fAct )   { fAct.checked = esEdit ? ( d.active === '1' ) : true; }

			// Logo: guardar el actual, limpiar el input de archivo y mostrar/ocultar la vista previa.
			var fLogo = campo( modal, 'current_logo' ); if ( fLogo ) { fLogo.value = d.logo || ''; }
			var fFile = campo( modal, 'logo_file' );    if ( fFile ) { fFile.value = ''; }
			var prev  = modal.querySelector( '[data-logo-preview]' );
			if ( prev ) {
				if ( d.logo ) { prev.src = d.logo; prev.style.display = ''; }
				else { prev.removeAttribute( 'src' ); prev.style.display = 'none'; }
			}

			bootstrap.Modal.getOrCreateInstance( modal ).show();
		} );
	} );
} )();
</script>
