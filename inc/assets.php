<?php
/**
 * Assets: filemtime() cache busting, defer strategy, no jQuery.
 *
 * @package Gridline
 */

defined( 'ABSPATH' ) || exit;

/**
 * Cache-busting version for a theme-relative file.
 *
 * @param string $relative Relative path.
 */
function gridline_asset_version( string $relative ): string {
	$path = GRIDLINE_DIR . '/' . ltrim( $relative, '/' );
	return file_exists( $path ) ? (string) filemtime( $path ) : GRIDLINE_VERSION;
}

add_action( 'wp_enqueue_scripts', 'gridline_enqueue_assets' );

/**
 * Enqueue styles and scripts.
 */
function gridline_enqueue_assets(): void {
	$deps = array();
	foreach ( array( 'base', 'layout', 'components' ) as $name ) {
		$relative = "assets/css/{$name}.css";
		wp_enqueue_style( "gridline-{$name}", get_theme_file_uri( $relative ), $deps, gridline_asset_version( $relative ) );
		$deps = array( "gridline-{$name}" );
	}

	$args = array(
		'strategy'  => 'defer',
		'in_footer' => true,
	);
	wp_enqueue_script( 'gridline-main', get_theme_file_uri( 'assets/js/main.js' ), array(), gridline_asset_version( 'assets/js/main.js' ), $args );

	// Catalogue enhancement: registered here, enqueued by the catalogue index part.
	wp_register_script( 'gridline-catalog', get_theme_file_uri( 'assets/js/catalog.js' ), array(), gridline_asset_version( 'assets/js/catalog.js' ), $args );

	wp_dequeue_style( 'classic-theme-styles' );
}

add_action( 'wp_head', 'gridline_preload_fonts', 3 );

/**
 * Preload the self-hosted variable font (no third-party font CDN).
 */
function gridline_preload_fonts(): void {
	foreach ( array( 'inter-tight-wght.woff2' ) as $font ) {
		printf( "<link rel=\"preload\" href=\"%s\" as=\"font\" type=\"font/woff2\" crossorigin>\n", esc_url( get_theme_file_uri( 'assets/fonts/' . $font ) ) );
	}
}
