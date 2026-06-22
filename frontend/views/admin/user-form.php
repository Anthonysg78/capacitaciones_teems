<?php
/**
 * Vista: formulario de usuario nuevo (página de respaldo, sin JS).
 *
 * El alta normal ocurre en el modal de la lista; esta página es el respaldo
 * para cuando el JS de Bootstrap no esté disponible.
 *
 * Variables recibidas:
 *   $list_url  string  URL de la lista (volver / redirigir)
 *
 * @package TeammsLMS
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div class="lms-pagehead">
	<h1>Nuevo usuario</h1>
	<p>Crea una cuenta manualmente. El estudiante también puede crear la suya con el link de invitación a un curso.</p>
</div>

<form class="lms-form" action="<?php echo esc_url( $list_url ); ?>" method="post">
	<input type="hidden" name="lms_action" value="save_user">
	<input type="hidden" name="redirect" value="<?php echo esc_url( $list_url ); ?>">
	<?php wp_nonce_field( 'lms_save_user' ); ?>

	<div class="lms-field">
		<label for="lms-name">Nombre completo <span class="lms-req">*</span></label>
		<input type="text" id="lms-name" name="name" required placeholder="Ej. Ana Pérez">
	</div>

	<div class="lms-field">
		<label for="lms-email">Correo electrónico <span class="lms-req">*</span></label>
		<input type="email" id="lms-email" name="email" required placeholder="ana@correo.com">
	</div>

	<div class="lms-field">
		<label for="lms-pass">Contraseña <span class="lms-req">*</span></label>
		<input type="text" id="lms-pass" name="password" required minlength="6" placeholder="Mínimo 6 caracteres">
	</div>

	<div class="lms-field">
		<label for="lms-role">Perfil</label>
		<select id="lms-role" name="role">
			<option value="lms_student" selected>Estudiante</option>
			<option value="lms_admin">Administrador</option>
		</select>
	</div>

	<div class="lms-field">
		<label for="lms-company">Empresa <small class="lms-muted">(solo estudiantes)</small></label>
		<select id="lms-company" name="company_id">
			<option value="0">— Sin empresa —</option>
			<?php foreach ( $empresas as $emp ) : ?>
				<option value="<?php echo (int) $emp->id; ?>"><?php echo esc_html( $emp->name ); ?></option>
			<?php endforeach; ?>
		</select>
	</div>

	<div class="lms-form__actions">
		<button type="submit" class="lms-course__btn"><i class="bi bi-person-plus"></i> Crear usuario</button>
		<a class="lms-btn-ghost" href="<?php echo esc_url( $list_url ); ?>">Cancelar</a>
	</div>
</form>
