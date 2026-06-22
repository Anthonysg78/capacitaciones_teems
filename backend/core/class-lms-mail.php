<?php
/**
 * Remitente global de los correos del LMS.
 *
 * Sustituye el remitente POR DEFECTO de WordPress por uno de marca:
 *
 *   wordpress@<dominio>  →  noreply@<dominio>
 *   "WordPress"          →  "Capacitaciones Teamms"
 *
 * El dominio NO se quema: se deduce del sitio donde corre WordPress, así
 * funciona igual en local y en producción sin tocar nada.
 *
 * Cubre cualquier wp_mail() que no fije su propio "From" (incluye el código de
 * confirmación de registro). RESPETA los remitentes explícitos: si un plugin
 * define su From (WooCommerce, etc.), no se toca — por eso comparamos contra
 * los valores por defecto antes de sustituir.
 *
 * @package TeammsLMS
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class LMS_Mail {

	/** Nombre visible del remitente. */
	const FROM_NAME = 'Capacitaciones Teamms';

	public function __construct() {
		add_filter( 'wp_mail_from', array( $this, 'from_email' ) );
		add_filter( 'wp_mail_from_name', array( $this, 'from_name' ) );
	}

	/**
	 * Remitente: solo reemplaza el default de WP (wordpress@…) o el correo del
	 * admin usado como genérico. Cualquier otro "From" explícito se respeta.
	 */
	public function from_email( $email ) {
		if ( 0 === strpos( $email, 'wordpress@' ) || $email === get_option( 'admin_email' ) ) {
			return 'noreply@' . $this->dominio();
		}
		return $email;
	}

	/** Nombre: solo reemplaza el literal "WordPress". */
	public function from_name( $name ) {
		return ( 'WordPress' === $name ) ? self::FROM_NAME : $name;
	}

	/** Dominio del sitio, sin esquema ni "www." (ej. zelva.ec). */
	private function dominio() {
		$host = (string) wp_parse_url( home_url(), PHP_URL_HOST );
		$host = preg_replace( '/^www\./i', '', $host );
		return $host ? $host : 'localhost';
	}
}
