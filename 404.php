<?php
/**
 * 404.
 *
 * @package Gridline
 */

defined( 'ABSPATH' ) || exit;

get_header();
?>
<section class="gp-poster gp-404" aria-labelledby="gp-404-title">
	<div class="gp-wrap gp-poster__grid">
		<h1 id="gp-404-title" class="gp-poster__title"><span>4<span class="gp-blue">0</span>4</span></h1>
		<aside class="gp-poster__side">
			<p class="gp-label"><?php esc_html_e( 'Out of print', 'gridline' ); ?></p>
			<p class="gp-poster__lead"><?php esc_html_e( 'This page is not in the catalogue. It may have moved, or never existed.', 'gridline' ); ?></p>
			<p><a class="gp-arrow" href="<?php echo esc_url( get_post_type_archive_link( 'gp_book' ) ); ?>"><?php esc_html_e( 'Browse the catalogue', 'gridline' ); ?></a></p>
		</aside>
	</div>
</section>
<?php
get_footer();
