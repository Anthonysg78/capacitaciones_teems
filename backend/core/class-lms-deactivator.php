<?php
/**
 * Desactivador del plugin.
 *
 * Se ejecuta al desactivar el plugin desde wp-admin.
 *
 * REGLA DE NEGOCIO: al desactivar, el sitio debe quedar igual que antes.
 * Por eso NO borramos tablas ni datos aquí. Eso solo se haría en una rutina
 * de desinstalación (uninstall.php), si algún día el usuario lo decide.
 *
 * @package TeammsLMS
 */

// Guarda de seguridad.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class LMS_Deactivator {

	/**
	 * Punto de entrada del desactivador.
	 */
	public static function deactivate() {
		// Solo limpiamos la caché de reglas de URL. Nada de datos.
		flush_rewrite_rules();
	}
}
