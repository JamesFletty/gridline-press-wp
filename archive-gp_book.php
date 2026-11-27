<?php
/**
 * Catalogue archive (/catalogue/), filtered + sorted by WP_Query via
 * gridline_catalog_main_query().
 *
 * @package Gridline
 */

defined( 'ABSPATH' ) || exit;

get_header();
global $wp_query;
$gridline_imprint = gridline_current_imprint();
?>
<section class="gp-section gp-archive" aria-labelledby="gp-archive-title">
	<div class="gp-wrap">
		<h1 id="gp-archive-title" class="gp-archive__title"><?php esc_html_e( 'Catalogue', 'gridline' ); ?></h1>
		<?php
		get_template_part(
			'template-parts/catalog-index',
			null,
			array(
				'posts'   => $wp_query->posts,
				'imprint' => $gridline_imprint,
				'sort'    => gridline_current_sort(),
			)
		);
		?>
	</div>
</section>
<?php
get_footer();
