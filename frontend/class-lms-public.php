<?php
/**
 * CONTROLADOR del frontend del LMS.
 *
 * Responsabilidad ÚNICA: infraestructura + ruteo.
 *   - Registra el shortcode, los assets y la plantilla a pantalla completa.
 *   - Detecta el rol del usuario.
 *   - Decide QUÉ vista cargar y le pasa los datos.
 *
 * NO contiene HTML: cada pantalla vive en su propio archivo dentro de
 * frontend/views/. Así el código queda separado y modular.
 *
 * @package TeammsLMS
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class LMS_Public {

	public function __construct() {
		add_shortcode( 'teamms_capacitaciones', array( $this, 'render' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_assets' ) );
		// Prioridad alta (9999) para correr DESPUÉS de que el tema y otros
		// plugins hayan encolado sus estilos, y así poder quitarlos.
		add_action( 'wp_enqueue_scripts', array( $this, 'isolate_styles' ), 9999 );
		// Red de seguridad: suprime el <link>/<script> de cualquier asset ajeno
		// al imprimir, sin importar cómo o cuándo se haya encolado.
		add_filter( 'style_loader_tag', array( $this, 'suppress_foreign_styles' ), 10, 2 );
		add_filter( 'script_loader_tag', array( $this, 'suppress_foreign_scripts' ), 10, 2 );
		add_filter( 'template_include', array( $this, 'load_app_template' ) );
	}

	/* ====================================================================
	 *  INFRAESTRUCTURA (assets + plantilla)
	 * ==================================================================== */

	private function page_has_shortcode() {
		if ( is_admin() ) {
			return false;
		}
		global $post;
		return ( $post instanceof WP_Post ) && has_shortcode( $post->post_content, 'teamms_capacitaciones' );
	}

	public function enqueue_assets() {
		if ( ! $this->page_has_shortcode() ) {
			return;
		}
		// Desactivar la conversión de emojis de WordPress en nuestra página:
		// algunos temas le quitan el estilo que los achica y salen gigantes.
		// Así el emoji se muestra como texto nativo, pequeño y normal.
		remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
		remove_action( 'wp_print_styles', 'print_emoji_styles' );
		remove_filter( 'the_content', 'wp_staticize_emoji' );
		remove_filter( 'the_content_feed', 'wp_staticize_emoji' );

		wp_enqueue_style( 'teamms-bootstrap', 'https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css', array(), '5.3.3' );
		wp_enqueue_style( 'teamms-bootstrap-icons', 'https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css', array(), '1.11.3' );
		wp_enqueue_style( 'teamms-lms-public', TEAMMS_LMS_URL . 'diseno/css/lms-public.css', array( 'teamms-bootstrap' ), TEAMMS_LMS_VERSION );
		wp_enqueue_script( 'teamms-bootstrap', 'https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js', array(), '5.3.3', true );

		// Editor de texto enriquecido (Quill) para el contenido tipo "texto".
		// El JS se carga en el HEAD (in_footer = false) para que ya esté disponible
		// cuando corre el script en línea del modal/formulario.
		wp_enqueue_style( 'lms-quill', 'https://cdn.jsdelivr.net/npm/quill@1.3.7/dist/quill.snow.css', array(), '1.3.7' );
		wp_enqueue_script( 'lms-quill', 'https://cdn.jsdelivr.net/npm/quill@1.3.7/dist/quill.min.js', array(), '1.3.7', false );
	}

	public function load_app_template( $template ) {
		if ( $this->page_has_shortcode() ) {
			return TEAMMS_LMS_PATH . 'frontend/templates/app-fullscreen.php';
		}
		return $template;
	}

	/**
	 * AÍSLA la app: en la página del LMS (pantalla completa y autosuficiente),
	 * quita los estilos del tema y de otros plugins que descuadran el layout.
	 * Conserva solo los nuestros y los del sistema (admin-bar, dashicons).
	 */
	public function isolate_styles() {
		if ( ! $this->page_has_shortcode() ) {
			return;
		}
		$conservar = array(
			'teamms-bootstrap',
			'teamms-bootstrap-icons',
			'teamms-lms-public',
			'lms-quill',
			'admin-bar',
			'dashicons',
		);
		foreach ( (array) wp_styles()->queue as $handle ) {
			if ( ! in_array( $handle, $conservar, true ) ) {
				wp_dequeue_style( $handle );
			}
		}
	}

	/**
	 * Red de seguridad: al imprimir cada hoja de estilo, deja pasar solo las
	 * nuestras (y las del sistema). Cualquier otra se suprime devolviendo ''.
	 * Esto atrapa estilos que se cuelan aunque el dequeue no los alcance.
	 */
	public function suppress_foreign_styles( $tag, $handle ) {
		if ( ! $this->page_has_shortcode() ) {
			return $tag;
		}
		$conservar = array(
			'teamms-bootstrap',
			'teamms-bootstrap-icons',
			'teamms-lms-public',
			'lms-quill',
			'admin-bar',
			'dashicons',
		);
		return in_array( $handle, $conservar, true ) ? $tag : '';
	}

	/**
	 * Igual que la anterior, pero para los <script>: deja pasar solo los
	 * nuestros y los del sistema. Así el JS de otros temas/plugins (que puede
	 * manipular el DOM y romper el layout) no se ejecuta en la página del LMS.
	 */
	public function suppress_foreign_scripts( $tag, $handle ) {
		if ( ! $this->page_has_shortcode() ) {
			return $tag;
		}
		$conservar = array(
			'teamms-bootstrap',
			'lms-quill',
			'jquery',
			'jquery-core',
			'jquery-migrate',
			'admin-bar',
		);
		return in_array( $handle, $conservar, true ) ? $tag : '';
	}

	/* ====================================================================
	 *  AYUDANTE PARA CARGAR VISTAS
	 * ==================================================================== */

	/**
	 * Incluye un archivo de vista y le pasa variables.
	 * Uso: $this->view( 'student/courses', array( 'cursos' => $cursos ) );
	 */
	private function view( $file, $vars = array() ) {
		if ( ! empty( $vars ) ) {
			extract( $vars, EXTR_SKIP ); // phpcs:ignore -- variables controladas por nosotros.
		}
		$ruta = TEAMMS_LMS_PATH . 'frontend/views/' . $file . '.php';
		if ( file_exists( $ruta ) ) {
			include $ruta;
		}
	}

	/* ====================================================================
	 *  ROLES / PERFIL
	 * ==================================================================== */

	private function get_perfil() {
		// El rol se deduce de la cuenta con sesión iniciada (ya no hay selector demo).
		if ( current_user_can( 'manage_options' ) || current_user_can( 'lms_manage' ) ) {
			return 'admin';
		}
		return 'estudiante';
	}

	private function get_nav( $perfil ) {
		if ( 'admin' === $perfil ) {
			return array(
				'dashboard' => array( 'Panel',           'bi-grid-1x2-fill' ),
				'cursos'    => array( 'Cursos',          'bi-book' ),
				'usuarios'  => array( 'Usuarios',        'bi-people' ),
				'preguntas' => array( 'Banco preguntas', 'bi-patch-question' ),
				'reportes'  => array( 'Reportes',        'bi-bar-chart' ),
			);
		}
		return array(
			'dashboard'    => array( 'Inicio',       'bi-grid-1x2-fill' ),
			'cursos'       => array( 'Mis cursos',   'bi-book' ),
			'certificados' => array( 'Certificados', 'bi-award' ),
			'insignias'    => array( 'Insignias',    'bi-patch-check-fill' ),
		);
	}

	private static function etiqueta_perfil( $perfil ) {
		$map = array( 'admin' => 'Administrador', 'estudiante' => 'Estudiante' );
		return isset( $map[ $perfil ] ) ? $map[ $perfil ] : 'Usuario';
	}

	/* ====================================================================
	 *  RENDER PRINCIPAL (ruteo)
	 * ==================================================================== */

	public function render( $atts ) {
		$vista = isset( $_GET['vista'] ) ? sanitize_key( wp_unslash( $_GET['vista'] ) ) : 'dashboard';

		// Verificación PÚBLICA de un certificado (sin login, sin sidebar).
		if ( 'verificar' === $vista ) {
			$codigo = isset( $_GET['codigo'] ) ? sanitize_text_field( wp_unslash( $_GET['codigo'] ) ) : '';
			ob_start();
			$this->view( 'public/verify', array(
				'cert'   => LMS_Certificate::details_by_code( $codigo ),
				'codigo' => $codigo,
			) );
			return ob_get_clean();
		}

		// Certificado imprimible (standalone, sin sidebar).
		if ( 'certificado' === $vista ) {
			$codigo     = isset( $_GET['codigo'] ) ? sanitize_text_field( wp_unslash( $_GET['codigo'] ) ) : '';
			$verify_url = add_query_arg(
				array( 'vista' => 'verificar', 'codigo' => $codigo ),
				remove_query_arg( array( 'vista', 'id', 'perfil', 'codigo' ) )
			);
			ob_start();
			$this->view( 'student/certificate', array(
				'cert'       => LMS_Certificate::details_by_code( $codigo ),
				'verify_url' => $verify_url,
				'back_url'   => $this->student_url( array( 'vista' => 'certificados' ) ),
			) );
			return ob_get_clean();
		}

		// A partir de aquí se requiere SESIÓN. Mostramos LOGIN o CREAR CUENTA
		// (pantallas separadas): se elige con ?vista=registro; por defecto, login.
		if ( ! is_user_logged_in() ) {
			$base   = remove_query_arg( array( 'vista', 'id', 'perfil', 'err', 'lms_action', 'codigo', 'accion', 'msg', 'invite', 'invite_err' ) );
			$invite = isset( $_GET['invite'] ) ? sanitize_text_field( wp_unslash( $_GET['invite'] ) ) : '';
			$curso  = $invite ? LMS_Course::find_by_invite_token( $invite ) : null;
			if ( $invite && ! $curso ) {
				$invite = ''; // código inválido: login/registro normal.
			}
			$datos = array( 'base' => $base, 'invite' => $invite, 'curso' => $curso );
			ob_start();
			if ( 'registro' === $vista ) {
				$this->view( 'auth/register', $datos );
			} else {
				$this->view( 'auth/login', $datos );
			}
			return ob_get_clean();
		}

		// Ya con sesión: si pidió la vista 'login', lo llevamos a su panel.
		if ( 'login' === $vista ) {
			$vista = 'dashboard';
		}

		$perfil = $this->get_perfil();
		$nav    = $this->get_nav( $perfil );

		// Validar la vista pedida.
		$internas = array( 'curso', 'evaluacion' );
		if ( ! isset( $nav[ $vista ] ) && ! in_array( $vista, $internas, true ) ) {
			$vista = 'dashboard';
		}

		// Datos del usuario para la barra superior.
		$user      = wp_get_current_user();
		$nombre    = ( $user && $user->display_name ) ? $user->display_name : 'Invitado';
		$partes    = preg_split( '/\s+/', trim( $nombre ) );
		$iniciales = strtoupper( substr( $partes[0], 0, 1 ) . ( isset( $partes[1] ) ? substr( $partes[1], 0, 1 ) : '' ) );
		$label     = self::etiqueta_perfil( $perfil );

		// URL para cerrar sesión (vuelve a la pantalla de login del LMS).
		$pagina     = get_permalink( get_the_ID() );
		$logout_url = add_query_arg(
			array( 'lms_action' => 'lms_logout', 'redirect' => rawurlencode( $pagina ) ),
			$pagina
		);

		ob_start();
		echo '<div class="lms-app">';
		$this->view( 'layout/sidebar', array(
			'perfil'       => $perfil,
			'perfil_label' => $label,
			'nav'          => $nav,
			'vista_actual' => $vista,
		) );
		echo '<div class="lms-main">';
		$this->view( 'layout/topbar', array(
			'perfil_label' => $label,
			'perfil'       => $perfil,
			'nombre'       => $nombre,
			'primer'       => $partes[0],
			'iniciales'    => $iniciales,
			'logout_url'   => $logout_url,
		) );
		echo '<main class="lms-content">';
		$this->render_content( $perfil, $vista );
		echo '</main></div></div>';
		return ob_get_clean();
	}

	/**
	 * Decide qué vista de CONTENIDO cargar según rol y vista.
	 */
	private function render_content( $perfil, $vista ) {
		if ( 'admin' === $perfil ) {
			$this->content_admin( $vista );
			return;
		}
		$this->content_estudiante( $vista );
	}

	private function content_admin( $vista ) {
		// Sección Cursos: tiene lógica propia (lista + formulario).
		if ( 'cursos' === $vista ) {
			$this->content_admin_cursos();
			return;
		}

		$secciones = array(
			'usuarios'  => array( 'Usuarios', 'bi-people', 'Aquí crearás usuarios y enviarás invitaciones por email.' ),
			'preguntas' => array( 'Banco de preguntas', 'bi-patch-question', 'Aquí administrarás las preguntas de cada módulo.' ),
			'reportes'  => array( 'Reportes', 'bi-bar-chart', 'Aquí verás métricas globales y exportarás reportes.' ),
		);

		if ( isset( $secciones[ $vista ] ) ) {
			list( $titulo, $icono, $texto ) = $secciones[ $vista ];
			$this->view( 'admin/seccion', compact( 'titulo', 'icono', 'texto' ) );
			return;
		}

		// Panel por defecto: estadísticas reales.
		global $wpdb;
		$p = $wpdb->prefix . 'lms_';
		$this->view( 'admin/panel', array(
			'cursos'        => (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$p}courses" ),
			'modulos'       => (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$p}modules" ),
			'inscripciones' => (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$p}enrollments" ),
			'certificados'  => (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$p}certificates" ),
		) );
	}

	/**
	 * Sección Cursos del admin: lista o formulario (crear/editar).
	 */
	private function content_admin_cursos() {
		$accion   = isset( $_GET['accion'] ) ? sanitize_key( wp_unslash( $_GET['accion'] ) ) : 'lista';
		$msg      = isset( $_GET['msg'] ) ? sanitize_key( wp_unslash( $_GET['msg'] ) ) : '';
		// URL ABSOLUTA de la lista de cursos (esta página + ?vista=cursos).
		$list_url = add_query_arg( 'vista', 'cursos', get_permalink( get_the_ID() ) );

		switch ( $accion ) {

			// --- Formulario de curso (crear / editar) ---
			case 'nuevo':
			case 'editar':
				$curso = ( 'editar' === $accion && isset( $_GET['id'] ) ) ? LMS_Course::find( absint( $_GET['id'] ) ) : null;
				$this->view( 'admin/course-form', array(
					'curso'    => $curso,
					'list_url' => $list_url,
				) );
				return;

			// --- Editor de ESTRUCTURA del curso (árbol módulos→contenidos) ---
			case 'modulos':
				$course_id = isset( $_GET['id'] ) ? absint( $_GET['id'] ) : 0;
				$curso     = LMS_Course::find( $course_id );
				if ( ! $curso ) {
					break; // curso inexistente: caer a la lista de cursos.
				}
				// Armamos el árbol: cada módulo con sus contenidos y sus preguntas.
				$arbol = array();
				foreach ( LMS_Module::all_by_course( $course_id ) as $modulo ) {
					$arbol[] = array(
						'modulo'     => $modulo,
						'contenidos' => LMS_Content::all_by_module( (int) $modulo->id ),
						'preguntas'  => LMS_Question::all_by_module( (int) $modulo->id ),
					);
				}
				$this->view( 'admin/structure', array(
					'curso'    => $curso,
					'arbol'    => $arbol,
					'list_url' => $list_url,
					'msg'      => $msg,
				) );
				return;

			// --- Formulario de módulo (crear / editar) ---
			case 'modulo_form':
				$course_id   = isset( $_GET['curso'] ) ? absint( $_GET['curso'] ) : 0;
				$modulo      = isset( $_GET['id'] ) ? LMS_Module::find( absint( $_GET['id'] ) ) : null;
				$modulos_url = add_query_arg( array( 'accion' => 'modulos', 'id' => $course_id ), $list_url );
				$this->view( 'admin/module-form', array(
					'modulo'      => $modulo,
					'course_id'   => $course_id,
					'modulos_url' => $modulos_url,
					'next_order'  => LMS_Module::next_order( $course_id ),
				) );
				return;

			// --- Formulario de contenido (crear / editar), página de respaldo ---
			case 'contenido_form':
				$module_id      = isset( $_GET['modulo'] ) ? absint( $_GET['modulo'] ) : 0;
				$contenido      = isset( $_GET['id'] ) ? LMS_Content::find( absint( $_GET['id'] ) ) : null;
				$modulo_obj     = LMS_Module::find( $module_id );
				$volver_id      = $modulo_obj ? (int) $modulo_obj->course_id : 0;
				$contenidos_url = add_query_arg( array( 'accion' => 'modulos', 'id' => $volver_id ), $list_url );
				$this->view( 'admin/content-form', array(
					'contenido'      => $contenido,
					'module_id'      => $module_id,
					'contenidos_url' => $contenidos_url,
					'next_order'     => LMS_Content::next_order( $module_id ),
				) );
				return;
		}

		// Por defecto: la lista de cursos.
		// La vista espera $items: cada curso con su conteo de módulos y contenidos.
		$items = array();
		foreach ( LMS_Course::all() as $curso ) {
			$items[] = array(
				'curso'      => $curso,
				'modulos'    => LMS_Module::count_by_course( (int) $curso->id ),
				'contenidos' => LMS_Content::count_by_course( (int) $curso->id ),
			);
		}
		$this->view( 'admin/courses', array(
			'items'     => $items,
			'nuevo_url' => add_query_arg( 'accion', 'nuevo', $list_url ),
			'list_url'  => $list_url,
			'msg'       => $msg,
		) );
	}

	private function content_estudiante( $vista ) {
		if ( 'certificados' === $vista ) {
			$certs = LMS_Certificate::all_for_user( get_current_user_id() );
			$this->view( 'student/certificates', array(
				'certs' => $certs,
				'base'  => $this->student_url( array() ),
			) );
			return;
		}
		if ( 'insignias' === $vista ) {
			$this->view( 'student/empty', array( 'titulo' => 'Mis Insignias', 'icono' => 'bi-patch-check', 'texto' => 'Todavía no has ganado insignias. ¡Completa módulos para desbloquearlas!' ) );
			return;
		}

		if ( 'curso' === $vista ) {
			$this->student_course_viewer();
			return;
		}

		if ( 'evaluacion' === $vista ) {
			$this->student_evaluation();
			return;
		}

		// Solo los cursos en los que el estudiante está INSCRITO (por link).
		$uid    = get_current_user_id();
		$paleta = array( '#2563eb', '#059669', '#d97706', '#7c3aed', '#db2777', '#0891b2' );
		$cursos = array();
		foreach ( LMS_Enrollment::courses_for_user( $uid ) as $i => $curso ) {
			$cursos[] = array(
				'id'       => (int) $curso->id,
				'titulo'   => $curso->title,
				'desc'     => wp_trim_words( wp_strip_all_tags( (string) $curso->description ), 18, '…' ),
				'progreso' => LMS_Progress::course_percent( $uid, (int) $curso->id ),
				'modulos'  => LMS_Module::count_by_course( (int) $curso->id ),
				'color'    => $paleta[ $i % count( $paleta ) ],
			);
		}
		$this->view( 'student/courses', array(
			'cursos'      => $cursos,
			'total'       => count( $cursos ),
			'completados' => count( array_filter( $cursos, fn( $c ) => 100 === $c['progreso'] ) ),
			'en_curso'    => count( array_filter( $cursos, fn( $c ) => $c['progreso'] > 0 && $c['progreso'] < 100 ) ),
		) );
	}

	/**
	 * VISOR de curso para el estudiante (solo lectura): módulos → contenidos.
	 * Reutiliza la misma estructura que el editor del admin.
	 */
	private function student_course_viewer() {
		$course_id = isset( $_GET['id'] ) ? absint( $_GET['id'] ) : 0;
		$curso     = LMS_Course::find( $course_id );

		if ( ! $curso || ! $curso->published ) {
			$this->view( 'student/empty', array(
				'titulo' => 'Curso no disponible',
				'icono'  => 'bi-exclamation-circle',
				'texto'  => 'Este curso no existe o aún no está publicado.',
			) );
			return;
		}

		$uid = get_current_user_id();

		// El estudiante solo entra a cursos en los que está inscrito (por link).
		// El administrador puede previsualizar cualquiera.
		if ( ! LMS_Enrollment::is_enrolled( $uid, $course_id ) && ! current_user_can( 'lms_manage' ) ) {
			$this->view( 'student/empty', array(
				'titulo' => 'No estás inscrito',
				'icono'  => 'bi-lock',
				'texto'  => 'Necesitas el link de invitación de este curso para poder acceder.',
			) );
			return;
		}

		$arbol = array();
		foreach ( LMS_Module::all_by_course( $course_id ) as $modulo ) {
			$arbol[] = array(
				'modulo'      => $modulo,
				'contenidos'  => LMS_Content::all_by_module( (int) $modulo->id ),
				'n_preguntas' => LMS_Question::count_by_module( (int) $modulo->id ),
				'aprobada'    => LMS_Evaluation::passed( $uid, (int) $modulo->id ),
				'eval_url'    => $this->student_url( array( 'vista' => 'evaluacion', 'modulo' => (int) $modulo->id ) ),
			);
		}

		// Datos de progreso del usuario actual.
		$completados = LMS_Progress::completed_ids_for_course( $uid, $course_id );
		$total       = LMS_Progress::total_contents_in_course( $course_id );
		$hechos      = count( $completados );

		$this->view( 'student/course', array(
			'curso'       => $curso,
			'arbol'       => $arbol,
			'volver_url'  => $this->student_url( array( 'vista' => 'cursos' ) ),
			'completados' => $completados,
			'hechos'      => $hechos,
			'total'       => $total,
			'percent'     => $total ? (int) round( $hechos / $total * 100 ) : 0,
			'viewer_url'  => $this->student_url( array( 'vista' => 'curso', 'id' => $course_id ) ),
		) );
	}

	/**
	 * Construye una URL de la app conservando el perfil demo (?perfil=...).
	 * Sin esto, al navegar/volver el sistema trataría al estudiante como admin.
	 */
	private function student_url( $args ) {
		$perfil = isset( $_GET['perfil'] ) ? sanitize_key( wp_unslash( $_GET['perfil'] ) ) : '';
		if ( $perfil ) {
			$args['perfil'] = $perfil;
		}
		return add_query_arg( $args, get_permalink( get_the_ID() ) );
	}

	/**
	 * EVALUACIÓN del módulo: muestra el examen (modo rendir) o el resultado
	 * del último intento (modo estado). Aplica las reglas: 2 intentos, nota 7.
	 */
	private function student_evaluation() {
		$module_id = isset( $_GET['modulo'] ) ? absint( $_GET['modulo'] ) : 0;
		$modulo    = LMS_Module::find( $module_id );
		if ( ! $modulo ) {
			$this->view( 'student/empty', array(
				'titulo' => 'Evaluación no disponible',
				'icono'  => 'bi-exclamation-circle',
				'texto'  => 'Este módulo no existe.',
			) );
			return;
		}

		$curso     = LMS_Course::find( (int) $modulo->course_id );
		$uid       = get_current_user_id();
		$preguntas = LMS_Question::all_by_module( $module_id );
		$intentos  = LMS_Evaluation::attempts( $uid, $module_id );
		$can_take  = LMS_Evaluation::can_take( $uid, $module_id ) && ! empty( $preguntas );
		$rendir    = isset( $_GET['rendir'] ) && '1' === $_GET['rendir'];

		$back_url   = $this->student_url( array( 'vista' => 'curso', 'id' => (int) $modulo->course_id ) );
		$status_url = $this->student_url( array( 'vista' => 'evaluacion', 'modulo' => $module_id ) );
		$rendir_url = $this->student_url( array( 'vista' => 'evaluacion', 'modulo' => $module_id, 'rendir' => 1 ) );

		// MODO EXAMEN: solo si pidió rendir y todavía puede.
		if ( $rendir && $can_take ) {
			shuffle( $preguntas );
			foreach ( $preguntas as $q ) {
				shuffle( $q->options );
			}
			$this->view( 'student/evaluation', array(
				'curso'          => $curso,
				'modulo'         => $modulo,
				'preguntas'      => $preguntas,
				'attempt_number' => count( $intentos ) + 1,
				'max_intentos'   => LMS_Evaluation::MAX_INTENTOS,
				'nota_minima'    => LMS_Evaluation::NOTA_MINIMA,
				'form_url'       => $status_url,
				'back_url'       => $back_url,
			) );
			return;
		}

		// MODO ESTADO/RESULTADO.
		$ultimo  = ! empty( $intentos ) ? $intentos[0] : null;
		$sel_map = $ultimo ? LMS_Evaluation::selected_map( (int) $ultimo->id ) : array();
		$this->view( 'student/evaluation-status', array(
			'curso'        => $curso,
			'modulo'       => $modulo,
			'preguntas'    => $preguntas,
			'n_intentos'   => count( $intentos ),
			'max_intentos' => LMS_Evaluation::MAX_INTENTOS,
			'nota_minima'  => LMS_Evaluation::NOTA_MINIMA,
			'aprobada'     => LMS_Evaluation::passed( $uid, $module_id ),
			'can_take'     => $can_take,
			'ultimo'       => $ultimo,
			'sel_map'      => $sel_map,
			'rendir_url'   => $rendir_url,
			'back_url'     => $back_url,
		) );
		
	}

}

															