<?php
/**
 * Acciones de Empresas (crear / editar / borrar) desde el panel del admin.
 *
 * Se procesan en 'template_redirect', igual que el resto de acciones del LMS.
 * Por tocar datos de gestión, se exige la capacidad 'lms_manage' además del nonce.
 *
 * Seguridad: nonce (CSRF) + permiso (lms_manage) + sanitización.
 *
 * @package TeammsLMS
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class LMS_Company_Actions {

	public function __construct() {
		add_action( 'template_redirect', array( $this, 'handle' ) );
	}

	public function handle() {
		if ( is_admin() ) {
			return;
		}
		if ( isset( $_POST['lms_action'] ) && 'save_company' === $_POST['lms_action'] ) {
			$this->save();
			return;
		}
		if ( isset( $_GET['lms_action'] ) && 'delete_company' === $_GET['lms_action'] ) {
			$this->delete();
		}
	}

	/** Crear o editar una empresa. */
	private function save() {
		if ( ! current_user_can( 'lms_manage' ) ) {
			$this->redirigir( 'denied' );
		}
		$nonce = isset( $_POST['_wpnonce'] ) ? sanitize_text_field( wp_unslash( $_POST['_wpnonce'] ) ) : '';
		if ( ! wp_verify_nonce( $nonce, 'lms_save_company' ) ) {
			$this->redirigir( 'expired' );
		}

		$id   = isset( $_POST['company_id'] ) ? absint( $_POST['company_id'] ) : 0;
		$name = sanitize_text_field( wp_unslash( $_POST['name'] ?? '' ) );

		// El nombre es obligatorio.
		if ( '' === $name ) {
			$this->redirigir( 'datos' );
		}

		if ( $id ) {
			LMS_Company::update( $id, $name );
		} else {
			LMS_Company::create( $name );
		}
		$this->redirigir( 'saved' );
	}

	/** Borrar una empresa (y desasignarla de sus estudiantes). */
	private function delete() {
		if ( ! current_user_can( 'lms_manage' ) ) {
			$this->redirigir( 'denied' );
		}
		$id    = isset( $_GET['id'] ) ? absint( $_GET['id'] ) : 0;
		$nonce = isset( $_GET['_wpnonce'] ) ? sanitize_text_field( wp_unslash( $_GET['_wpnonce'] ) ) : '';
		if ( ! $id || ! wp_verify_nonce( $nonce, 'lms_delete_company_' . $id ) ) {
			$this->redirigir( 'expired' );
		}
		LMS_Company::delete( $id );
		$this->redirigir( 'deleted' );
	}

	/** URL de la lista a la que volvemos (viene en el campo 'redirect'). */
	private function target() {
		$campo = $_REQUEST['redirect'] ?? '';
		return $campo ? esc_url_raw( wp_unslash( $campo ) ) : home_url( '/' );
	}

	/** Redirige con un mensaje y termina. */
	private function redirigir( $msg ) {
		wp_safe_redirect( add_query_arg( 'msg', $msg, $this->target() ) );
		exit;
	}
}
