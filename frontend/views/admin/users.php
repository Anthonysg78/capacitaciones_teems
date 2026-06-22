<?php
/**
 * Vista: lista de usuarios (panel admin) en formato de tabla.
 *
 * Variables recibidas:
 *   $items     array   cada item: [ id, nombre, email, perfil, perfil_lbl, alta, cursos ]
 *   $nuevo_url string  URL para crear un usuario nuevo (fallback sin JS)
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
		<h1>Usuarios</h1>
		<p>Cuentas de la plataforma. Crea estudiantes manualmente o gestiónalos.</p>
	</div>
	<a class="lms-course__btn" href="<?php echo esc_url( $nuevo_url ); ?>" data-bs-toggle="modal" data-bs-target="#lms-modal-usuario"><i class="bi bi-person-plus"></i> Nuevo usuario</a>
</div>

<?php if ( 'saved' === $msg ) : ?>
	<div class="lms-notice lms-notice--ok"><i class="bi bi-check-circle"></i> Usuario creado correctamente.</div>
<?php elseif ( 'deleted' === $msg ) : ?>
	<div class="lms-notice lms-notice--ok"><i class="bi bi-trash"></i> Usuario eliminado.</div>
<?php elseif ( 'company' === $msg ) : ?>
	<div class="lms-notice lms-notice--ok"><i class="bi bi-building-check"></i> Empresa del estudiante actualizada.</div>
<?php elseif ( 'existe' === $msg ) : ?>
	<div class="lms-notice lms-notice--err"><i class="bi bi-exclamation-triangle"></i> Ya existe una cuenta con ese correo.</div>
<?php elseif ( 'datos' === $msg ) : ?>
	<div class="lms-notice lms-notice--err"><i class="bi bi-exclamation-triangle"></i> Revisa los datos: nombre, un correo válido y contraseña de al menos 6 caracteres.</div>
<?php elseif ( 'self' === $msg ) : ?>
	<div class="lms-notice lms-notice--err"><i class="bi bi-exclamation-triangle"></i> No puedes eliminar tu propia cuenta.</div>
<?php elseif ( 'denied' === $msg ) : ?>
	<div class="lms-notice lms-notice--err"><i class="bi bi-shield-lock"></i> No tienes permiso para esta acción.</div>
<?php elseif ( 'expired' === $msg ) : ?>
	<div class="lms-notice lms-notice--err"><i class="bi bi-clock-history"></i> La sesión del formulario expiró. Inténtalo de nuevo.</div>
<?php endif; ?>

<?php if ( empty( $items ) ) : ?>
	<div class="lms-empty">
		<i class="bi bi-people"></i>
		<p>Todavía no hay usuarios. ¡Crea el primero!</p>
		<a class="lms-course__btn d-inline-flex" href="<?php echo esc_url( $nuevo_url ); ?>" data-bs-toggle="modal" data-bs-target="#lms-modal-usuario"><i class="bi bi-person-plus"></i> Nuevo usuario</a>
	</div>
<?php else : ?>
	<div class="lms-tablewrap">
		<table class="lms-table">
			<thead>
				<tr>
					<th>Usuario</th>
					<th>Perfil</th>
					<th>Empresa</th>
					<th>Cursos</th>
					<th>Alta</th>
					<th class="lms-table__actions">Acciones</th>
				</tr>
			</thead>
			<tbody>
				<?php foreach ( $items as $u ) : ?>
					<?php
					$borrar_url = wp_nonce_url(
						add_query_arg(
							array(
								'lms_action' => 'delete_user',
								'id'         => (int) $u['id'],
								'redirect'   => rawurlencode( $list_url ),
							),
							$list_url
						),
						'lms_delete_user_' . (int) $u['id']
					);
					$es_admin = ( 'admin' === $u['perfil'] );
					$alta     = $u['alta'] ? date_i18n( 'd/m/Y', strtotime( $u['alta'] ) ) : '—';
					?>
					<tr>
						<td>
							<div class="lms-table__title"><?php echo esc_html( $u['nombre'] ); ?></div>
							<div style="color: var(--muted); font-size: 13px;"><?php echo esc_html( $u['email'] ); ?></div>
						</td>
						<td><span class="lms-tag <?php echo $es_admin ? 'lms-tag--ok' : 'lms-tag--draft'; ?>"><?php echo esc_html( $u['perfil_lbl'] ); ?></span></td>
						<td>
							<?php if ( $es_admin ) : ?>
								<span style="color: var(--muted);">—</span>
							<?php else : ?>
								<form method="post" action="<?php echo esc_url( $list_url ); ?>" style="margin:0;">
									<input type="hidden" name="lms_action" value="set_user_company">
									<input type="hidden" name="id" value="<?php echo (int) $u['id']; ?>">
									<input type="hidden" name="redirect" value="<?php echo esc_url( $list_url ); ?>">
									<?php wp_nonce_field( 'lms_set_user_company_' . (int) $u['id'] ); ?>
									<select name="company_id" onchange="this.form.submit()" class="lms-select">
										<option value="0"><?php esc_html_e( '— Sin empresa —', 'teamms' ); ?></option>
										<?php foreach ( $empresas as $emp ) : ?>
											<option value="<?php echo (int) $emp->id; ?>" <?php selected( (int) $u['company_id'], (int) $emp->id ); ?>><?php echo esc_html( $emp->name ); ?></option>
										<?php endforeach; ?>
									</select>
								</form>
							<?php endif; ?>
						</td>
						<td><span class="lms-pill <?php echo $u['cursos'] ? 'lms-pill--ok' : ''; ?>"><?php echo (int) $u['cursos']; ?></span></td>
						<td><?php echo esc_html( $alta ); ?></td>
						<td class="lms-table__actions">
							<?php if ( (int) $u['id'] === get_current_user_id() ) : ?>
								<span class="lms-pill" title="Tu cuenta">Tú</span>
							<?php else : ?>
								<a class="lms-iconbtn lms-iconbtn--danger" href="<?php echo esc_url( $borrar_url ); ?>" title="Eliminar usuario" onclick="return confirm('¿Eliminar a <?php echo esc_js( $u['nombre'] ); ?>? Se borrarán también sus inscripciones y progreso. Esta acción no se puede deshacer.');"><i class="bi bi-trash"></i></a>
							<?php endif; ?>
						</td>
					</tr>
				<?php endforeach; ?>
			</tbody>
		</table>
	</div>
<?php endif; ?>

<!-- ============================ MODAL: USUARIO ============================ -->
<div class="modal fade lms-modal" id="lms-modal-usuario" tabindex="-1" aria-hidden="true">
	<div class="modal-dialog modal-dialog-centered">
		<div class="modal-content">
			<form method="post" action="<?php echo esc_url( $list_url ); ?>">
				<input type="hidden" name="lms_action" value="save_user">
				<input type="hidden" name="redirect" value="<?php echo esc_url( $list_url ); ?>">
				<?php wp_nonce_field( 'lms_save_user' ); ?>
				<div class="modal-header">
					<h5 class="modal-title">Nuevo usuario</h5>
					<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
				</div>
				<div class="modal-body">
					<div class="lms-field">
						<label>Nombre completo <span class="lms-req">*</span></label>
						<input type="text" name="name" required placeholder="Ej. Ana Pérez">
					</div>
					<div class="lms-field">
						<label>Correo electrónico <span class="lms-req">*</span></label>
						<input type="email" name="email" required placeholder="ana@correo.com">
					</div>
					<div class="lms-field">
						<label>Contraseña <span class="lms-req">*</span></label>
						<input type="text" name="password" required minlength="6" placeholder="Mínimo 6 caracteres">
					</div>
					<div class="lms-field">
						<label>Perfil</label>
						<select name="role">
							<option value="lms_student" selected>Estudiante</option>
							<option value="lms_admin">Administrador</option>
						</select>
					</div>
					<div class="lms-field">
						<label>Empresa <small class="lms-muted">(solo estudiantes)</small></label>
						<select name="company_id">
							<option value="0">— Sin empresa —</option>
							<?php foreach ( $empresas as $emp ) : ?>
								<option value="<?php echo (int) $emp->id; ?>"><?php echo esc_html( $emp->name ); ?></option>
							<?php endforeach; ?>
						</select>
					</div>
				</div>
				<div class="modal-footer">
					<button type="button" class="lms-btn-ghost" data-bs-dismiss="modal">Cancelar</button>
					<button type="submit" class="lms-course__btn"><i class="bi bi-person-plus"></i> Crear usuario</button>
				</div>
			</form>
		</div>
	</div>
</div>
