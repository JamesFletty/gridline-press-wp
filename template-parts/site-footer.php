<?php
/**
 * Footer.
 *
 * @package Gridline
 */

defined( 'ABSPATH' ) || exit;
?>
<footer class="gp-footer">
	<div class="gp-wrap">
		<p class="gp-footer__mark" aria-hidden="true"><?php bloginfo( 'name' ); ?></p>
		<div class="gp-footer__grid">
			<div>
				<h2 class="gp-footer__h"><?php esc_html_e( 'Press & gallery', 'gridline' ); ?></h2>
				<address><?php esc_html_e( '18 Halden Street, 3rd floor', 'gridline' ); ?><br><?php esc_html_e( 'Chicago, IL 60607', 'gridline' ); ?></address>
			</div>
			<div>
				<h2 class="gp-footer__h"><?php esc_html_e( 'Contact', 'gridline' ); ?></h2>
				<p><a href="mailto:office@gridline-press.example">office@gridline-press.example</a><br><a href="tel:+13125550142">(312) 555-0142</a></p>
			</div>
			<div>
				<h2 class="gp-footer__h"><?php esc_html_e( 'Gallery hours', 'gridline' ); ?></h2>
				<p><?php esc_html_e( 'Wed–Sun 11:00–18:00', 'gridline' ); ?><br><?php esc_html_e( 'Thu until 20:00', 'gridline' ); ?></p>
			</div>
			<nav aria-label="<?php esc_attr_e( 'Footer', 'gridline' ); ?>">
				<h2 class="gp-footer__h"><?php esc_html_e( 'Index', 'gridline' ); ?></h2>
				<?php
				wp_nav_menu(
					array(
						'theme_location' => 'footer',
						'container'      => false,
						'menu_class'     => 'gp-footer__menu',
						'depth'          => 1,
						'fallback_cb'    => 'gridline_fallback_menu',
					)
				);
				?>
			</nav>
		</div>
		<p class="gp-footer__base"><span>&copy; <?php echo esc_html( wp_date( 'Y' ) ); ?> <?php esc_html_e( 'Gridline Press (demo)', 'gridline' ); ?></span><span><?php esc_html_e( 'Set in Inter Tight on a 12-column grid.', 'gridline' ); ?></span></p>
	</div>
</footer>
