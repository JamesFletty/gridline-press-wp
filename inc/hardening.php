<?php
/**
 * Hardening and head cleanup.
 *
 * Removes markup and endpoints a brochure/portfolio site never needs and
 * sends a conservative set of security headers. Everything is hooked, so a
 * child theme or mu-plugin can unhook any single line.
 *
 * @package Gridline
 */

defined( 'ABSPATH' ) || exit;

add_action( 'init', 'gridline_cleanup_head' );

/**
 * Strip emoji scripts/styles, generator tags and legacy discovery links.
 */
function gridline_cleanup_head(): void {
	// Emoji detection script + inline styles (~16 KB on every page).
	remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
	remove_action( 'admin_print_scripts', 'print_emoji_detection_script' );
	remove_action( 'wp_print_styles', 'print_emoji_styles' );
	remove_action( 'admin_print_styles', 'print_emoji_styles' );
	remove_filter( 'the_content_feed', 'wp_staticize_emoji' );
	remove_filter( 'comment_text_rss', 'wp_staticize_emoji' );
	remove_filter( 'wp_mail', 'wp_staticize_emoji_for_email' );
	add_filter( 'emoji_svg_url', '__return_false' );

	// Discovery and fingerprinting tags.
	remove_action( 'wp_head', 'rsd_link' );
	remove_action( 'wp_head', 'wlwmanifest_link' );
	remove_action( 'wp_head', 'wp_generator' );
	remove_action( 'wp_head', 'wp_shortlink_wp_head' );
	remove_action( 'wp_head', 'adjacent_posts_rel_link_wp_head' );
	add_filter( 'the_generator', '__return_empty_string' );
}

add_filter( 'tiny_mce_plugins', 'gridline_disable_tinymce_emoji' );

/**
 * Drop the classic editor's emoji plugin as well.
 *
 * @param array $plugins TinyMCE plugins.
 */
function gridline_disable_tinymce_emoji( $plugins ): array {
	return is_array( $plugins ) ? array_diff( $plugins, array( 'wpemoji' ) ) : array();
}

// XML-RPC: disable the API entirely and stop advertising it.
add_filter( 'xmlrpc_enabled', '__return_false' );
add_filter( 'xmlrpc_methods', '__return_empty_array' );
add_filter( 'wp_headers', 'gridline_remove_pingback_header' );

/**
 * Remove the X-Pingback header.
 *
 * @param array $headers Response headers.
 */
function gridline_remove_pingback_header( array $headers ): array {
	unset( $headers['X-Pingback'] );
	return $headers;
}

add_filter( 'rest_endpoints', 'gridline_restrict_user_endpoints' );

/**
 * Block anonymous user enumeration via /wp/v2/users.
 *
 * @param array $endpoints Registered REST endpoints.
 */
function gridline_restrict_user_endpoints( array $endpoints ): array {
	if ( ! is_user_logged_in() ) {
		unset( $endpoints['/wp/v2/users'], $endpoints['/wp/v2/users/(?P<id>[\d]+)'] );
	}
	return $endpoints;
}

add_action( 'send_headers', 'gridline_security_headers' );

/**
 * Conservative security headers. A strict CSP belongs at the server/CDN
 * layer where plugin requirements are known, so it is filterable here.
 */
function gridline_security_headers(): void {
	if ( headers_sent() ) {
		return;
	}

	$headers = apply_filters(
		'gridline_security_headers',
		array(
			'X-Content-Type-Options' => 'nosniff',
			'X-Frame-Options'        => 'SAMEORIGIN',
			'Referrer-Policy'        => 'strict-origin-when-cross-origin',
			'Permissions-Policy'     => 'camera=(), microphone=(), geolocation=(), interest-cohort=()',
		)
	);

	foreach ( $headers as $name => $value ) {
		header( sprintf( '%s: %s', $name, $value ) );
	}
}
