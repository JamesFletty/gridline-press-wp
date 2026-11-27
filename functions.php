<?php
/**
 * Gridline Press — theme bootstrap. Thin loader; concerns live in inc/.
 *
 * @package Gridline
 */

defined( 'ABSPATH' ) || exit;

define( 'GRIDLINE_VERSION', '1.0.0' );
define( 'GRIDLINE_DIR', get_template_directory() );

foreach ( array( 'setup', 'assets', 'content-types', 'catalog', 'rest', 'blocks', 'hardening', 'template-tags' ) as $gridline_module ) {
	require_once GRIDLINE_DIR . '/inc/' . $gridline_module . '.php';
}
unset( $gridline_module );
