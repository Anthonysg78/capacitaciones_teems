<?php
/**
 * Plantilla a PANTALLA COMPLETA para las páginas del LMS.
 *
 * No carga el header/footer del tema: solo lo mínimo de WordPress
 * (wp_head y wp_footer, necesarios para que carguen estilos y scripts)
 * más el contenido de la página (nuestro shortcode [teems_lms]).
 *
 * @package TeemsLMS
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
	<?php wp_head(); ?>
</head>
<body <?php body_class( 'lms-fullscreen' ); ?>>
	<?php
	// Recorremos la "consulta" de WordPress y mostramos el contenido de la
	// página (que contiene el shortcode [teems_lms]).
	while ( have_posts() ) {
		the_post();
		the_content();
	}

	// Necesario para que WordPress inyecte sus scripts (y la barra de admin).
	wp_footer();
	?>
</body>
</html>
