<?php
/**
 * Site header.
 *
 * @package Gridline
 */

defined( 'ABSPATH' ) || exit;

$gridline_menu = array(
	'theme_location' => 'primary',
	'container'      => false,
	'menu_class'     => 'gp-menu',
	'depth'          => 1,
	'fallback_cb'    => 'gridline_fallback_menu',
);
?>
<header class="gp-header">
	<div class="gp-wrap gp-header__inner">
		<a class="gp-brand" href="<?php echo esc_url( home_url( '/' ) ); ?>" rel="home">
			<span class="gp-brand__mark" aria-hidden="true"></span>
			<span class="gp-brand__name"><?php bloginfo( 'name' ); ?></span>
		</a>
		<p class="gp-header__note"><?php esc_html_e( 'Publisher & gallery · Gallery open Wed–Sun, 11–18', 'gridline' ); ?></p>
		<nav class="gp-nav" aria-label="<?php esc_attr_e( 'Primary', 'gridline' ); ?>"><?php wp_nav_menu( $gridline_menu ); ?></nav>
		<button class="gp-toggle" type="button" aria-expanded="false" aria-controls="gp-drawer" data-gp-toggle><?php esc_html_e( 'Menu', 'gridline' ); ?></button>
	</div>
	<div class="gp-drawer" id="gp-drawer" data-gp-drawer hidden>
		<nav aria-label="<?php esc_attr_e( 'Mobile', 'gridline' ); ?>"><?php wp_nav_menu( array_merge( $gridline_menu, array( 'menu_class' => 'gp-drawer__menu' ) ) ); ?></nav>
	</div>
</header>
