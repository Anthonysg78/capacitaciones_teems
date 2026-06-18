<?php
/**
 * Activador del plugin.
 *
 * Se ejecuta UNA sola vez al activar el plugin desde wp-admin.
 * Responsabilidad: crear todas las tablas de la base de datos.
 *
 * @package TeammsLMS
 */

// Guarda de seguridad: nadie abre este archivo directo.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class LMS_Activator {

	/**
	 * Punto de entrada del activador.
	 * WordPress llama a este método al dar clic en "Activar".
	 */
	public static function activate() {
		self::create_tables();

		// Crear los roles propios del LMS (admin, estudiante).
		if ( class_exists( 'LMS_Roles' ) ) {
			LMS_Roles::register();
		}

		// Guardamos la versión de la base de datos. Nos servirá en el futuro
		// para saber si hay que actualizar tablas cuando saquemos versiones nuevas.
		update_option( 'teamms_lms_db_version', TEAMMS_LMS_VERSION );

		// Limpiamos la caché de reglas de URL.
		flush_rewrite_rules();
	}

	/**
	 * Crea todas las tablas del LMS.
	 *
	 * Usamos dbDelta(): la forma oficial y segura de WordPress para crear
	 * tablas. dbDelta es exigente con el formato del SQL, por eso cada campo
	 * va en su propia línea y "PRIMARY KEY" lleva DOS espacios antes del (.
	 */
	private static function create_tables() {
		global $wpdb;

		// Necesario para que dbDelta() esté disponible.
		require_once ABSPATH . 'wp-admin/includes/upgrade.php';

		$prefix  = $wpdb->prefix . 'lms_';        // ej. wp_lms_
		$collate = $wpdb->get_charset_collate();  // codificación de WordPress

		// Juntamos todas las sentencias en un arreglo y las pasamos a dbDelta.
		$sql = array();

		// 1) Invitaciones (tokens de activación de cuenta).
		$sql[] = "CREATE TABLE {$prefix}invitations (
			id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
			user_id bigint(20) unsigned NOT NULL,
			token varchar(64) NOT NULL,
			expires_at datetime NOT NULL,
			used_at datetime DEFAULT NULL,
			created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
			PRIMARY KEY  (id),
			UNIQUE KEY token (token),
			KEY user_id (user_id)
		) $collate;";

		// 2) Cursos.
		$sql[] = "CREATE TABLE {$prefix}courses (
			id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
			title varchar(255) NOT NULL,
			description longtext DEFAULT NULL,
			thumbnail_url varchar(500) DEFAULT NULL,
			published tinyint(1) NOT NULL DEFAULT 0,
			invite_token varchar(20) DEFAULT NULL,
			created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
			PRIMARY KEY  (id),
			UNIQUE KEY invite_token (invite_token)
		) $collate;";

		// 3) Módulos.
		$sql[] = "CREATE TABLE {$prefix}modules (
			id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
			course_id bigint(20) unsigned NOT NULL,
			title varchar(255) NOT NULL,
			order_index int NOT NULL DEFAULT 0,
			created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
			PRIMARY KEY  (id),
			KEY course_id (course_id)
		) $collate;";

		// 4) Contenidos (texto, video, pdf, recurso). Cuelgan directo del MÓDULO.
		$sql[] = "CREATE TABLE {$prefix}contents (
			id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
			module_id bigint(20) unsigned NOT NULL,
			type varchar(20) NOT NULL,
			title varchar(255) NOT NULL,
			content_text longtext DEFAULT NULL,
			content_url varchar(500) DEFAULT NULL,
			order_index int NOT NULL DEFAULT 0,
			created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
			PRIMARY KEY  (id),
			KEY module_id (module_id)
		) $collate;";

		// 5) Inscripciones.
		$sql[] = "CREATE TABLE {$prefix}enrollments (
			id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
			user_id bigint(20) unsigned NOT NULL,
			course_id bigint(20) unsigned NOT NULL,
			enrolled_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
			completed_at datetime DEFAULT NULL,
			PRIMARY KEY  (id),
			KEY user_id (user_id),
			KEY course_id (course_id)
		) $collate;";

		// 6) Progreso por contenido.
		$sql[] = "CREATE TABLE {$prefix}content_progress (
			id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
			user_id bigint(20) unsigned NOT NULL,
			content_id bigint(20) unsigned NOT NULL,
			completed_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
			PRIMARY KEY  (id),
			KEY user_id (user_id),
			KEY content_id (content_id)
		) $collate;";

		// 7) Banco de preguntas.
		$sql[] = "CREATE TABLE {$prefix}questions (
			id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
			module_id bigint(20) unsigned NOT NULL,
			question_text longtext NOT NULL,
			active tinyint(1) NOT NULL DEFAULT 1,
			created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
			PRIMARY KEY  (id),
			KEY module_id (module_id)
		) $collate;";

		// 8) Opciones de cada pregunta (4 por pregunta).
		$sql[] = "CREATE TABLE {$prefix}question_options (
			id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
			question_id bigint(20) unsigned NOT NULL,
			option_text text NOT NULL,
			is_correct tinyint(1) NOT NULL DEFAULT 0,
			PRIMARY KEY  (id),
			KEY question_id (question_id)
		) $collate;";

		// 9) Intentos de evaluación.
		$sql[] = "CREATE TABLE {$prefix}evaluation_attempts (
			id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
			user_id bigint(20) unsigned NOT NULL,
			module_id bigint(20) unsigned NOT NULL,
			attempt_number tinyint(1) NOT NULL,
			score decimal(4,2) DEFAULT NULL,
			passed tinyint(1) NOT NULL DEFAULT 0,
			started_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
			finished_at datetime DEFAULT NULL,
			PRIMARY KEY  (id),
			KEY user_id (user_id),
			KEY module_id (module_id)
		) $collate;";

		// 10) Respuestas dadas en cada intento.
		$sql[] = "CREATE TABLE {$prefix}attempt_answers (
			id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
			attempt_id bigint(20) unsigned NOT NULL,
			question_id bigint(20) unsigned NOT NULL,
			selected_option_id bigint(20) unsigned NOT NULL,
			PRIMARY KEY  (id),
			KEY attempt_id (attempt_id),
			KEY question_id (question_id)
		) $collate;";

		// 11) Certificados. Se emiten al COMPLETAR un CURSO (uno por curso/usuario).
		$sql[] = "CREATE TABLE {$prefix}certificates (
			id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
			user_id bigint(20) unsigned NOT NULL,
			course_id bigint(20) unsigned NOT NULL,
			issued_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
			unique_code varchar(36) NOT NULL,
			pdf_url varchar(500) DEFAULT NULL,
			public_url varchar(500) DEFAULT NULL,
			PRIMARY KEY  (id),
			UNIQUE KEY unique_code (unique_code),
			KEY user_id (user_id),
			KEY course_id (course_id)
		) $collate;";

		// 12) Insignias (catálogo).
		$sql[] = "CREATE TABLE {$prefix}badges (
			id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
			name varchar(255) NOT NULL,
			icon_url varchar(500) DEFAULT NULL,
			trigger_type varchar(30) NOT NULL,
			trigger_ref_id bigint(20) unsigned DEFAULT NULL,
			PRIMARY KEY  (id)
		) $collate;";

		// 13) Insignias obtenidas por usuario.
		$sql[] = "CREATE TABLE {$prefix}user_badges (
			id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
			user_id bigint(20) unsigned NOT NULL,
			badge_id bigint(20) unsigned NOT NULL,
			earned_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
			PRIMARY KEY  (id),
			KEY user_id (user_id),
			KEY badge_id (badge_id)
		) $collate;";

		// 14) Log de actividad.
		$sql[] = "CREATE TABLE {$prefix}activity_log (
			id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
			user_id bigint(20) unsigned NOT NULL,
			action varchar(255) NOT NULL,
			metadata longtext DEFAULT NULL,
			created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
			PRIMARY KEY  (id),
			KEY user_id (user_id)
		) $collate;";

		// Ejecutamos cada CREATE TABLE con dbDelta.
		foreach ( $sql as $statement ) {
			dbDelta( $statement );
		}
	}
}
