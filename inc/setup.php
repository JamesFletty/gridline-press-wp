<?php
/**
 * Theme setup.
 *
 * @package Gridline
 */

defined( 'ABSPATH' ) || exit;

add_action( 'after_setup_theme', 'gridline_setup' );

/**
 * Register theme features.
 */
function gridline_setup(): void {
	load_theme_textdomain( 'gridline', GRIDLINE_DIR . '/languages' );
	add_theme_support( 'title-tag' );
	add_theme_support( 'post-thumbnails' );
	add_theme_support( 'responsive-embeds' );
	add_theme_support( 'editor-styles' );
	add_theme_support( 'html5', array( 'search-form', 'gallery', 'caption', 'style', 'script', 'navigation-widgets' ) );
	remove_theme_support( 'core-block-patterns' );
	add_editor_style( array( 'assets/css/base.css' ) );

	register_nav_menus(
		array(
			'primary' => __( 'Primary navigation', 'gridline' ),
			'footer'  => __( 'Footer navigation', 'gridline' ),
		)
	);

	add_image_size( 'gp-cover', 800, 1200, true );
	add_image_size( 'gp-wide', 2000, 1000, true );
}

add_filter( 'excerpt_length', static fn(): int => 24, 20 );

add_action( 'wp_head', 'gridline_head_meta', 2 );

/**
 * Meta description + favicon (yields to SEO plugins / Site Icon).
 */
function gridline_head_meta(): void {
	if ( ! defined( 'WPSEO_VERSION' ) && ! defined( 'RANK_MATH_VERSION' ) ) {
		$description = is_singular() && has_excerpt() ? get_the_excerpt() : get_bloginfo( 'description' );
		if ( $description ) {
			printf( "<meta name=\"description\" content=\"%s\">\n", esc_attr( wp_strip_all_tags( $description ) ) );
		}
	}
	if ( ! ( function_exists( 'has_site_icon' ) && has_site_icon() ) ) {
		$svg = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 32 32"><rect width="32" height="32" fill="#fff"/><rect x="2" y="2" width="28" height="28" fill="none" stroke="#0A0A0A" stroke-width="2"/><rect x="16" y="2" width="14" height="14" fill="#0038FF"/></svg>';
		printf( "<link rel=\"icon\" href=\"data:image/svg+xml,%s\">\n", rawurlencode( $svg ) );
	}
}
