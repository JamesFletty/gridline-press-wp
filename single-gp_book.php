<?php
/**
 * Single book.
 *
 * @package Gridline
 */

defined( 'ABSPATH' ) || exit;

get_header();

while ( have_posts() ) :
	the_post();
	$gridline_id      = get_the_ID();
	$gridline_imprint = gridline_imprint( $gridline_id );
	$gridline_related = $gridline_imprint ? new WP_Query(
		gridline_catalog_args(
			$gridline_imprint->slug,
			'newest',
			array(
				'posts_per_page' => 3,
				'post__not_in'   => array( $gridline_id ),
			)
		)
	) : null;
	?>
	<article <?php post_class( 'gp-book' ); ?>>
		<div class="gp-wrap gp-book__grid">
			<div class="gp-book__cover"><?php echo gridline_cover_svg( $gridline_id, 'single' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></div>
			<div class="gp-book__info">
				<p class="gp-label"><a href="<?php echo esc_url( gridline_catalog_url( $gridline_imprint->slug ?? '', 'newest' ) ); ?>"><?php echo esc_html( $gridline_imprint->name ?? __( 'Catalogue', 'gridline' ) ); ?></a></p>
				<h1 class="gp-book__title"><?php the_title(); ?></h1>
				<p class="gp-book__author"><?php echo esc_html( (string) get_post_meta( $gridline_id, '_gp_author', true ) ); ?></p>
				<div class="gp-prose"><?php the_content(); ?></div>
				<dl class="gp-facts">
					<?php foreach ( gridline_book_facts( $gridline_id ) as $gridline_label => $gridline_value ) : ?>
						<div><dt><?php echo esc_html( $gridline_label ); ?></dt><dd><?php echo esc_html( $gridline_value ); ?></dd></div>
					<?php endforeach; ?>
				</dl>
				<p class="gp-book__note"><?php esc_html_e( 'Available from independent bookshops and at the gallery desk.', 'gridline' ); ?></p>
			</div>
		</div>
		<?php if ( $gridline_related && $gridline_related->posts ) : ?>
			<section class="gp-section" aria-labelledby="gp-related-title">
				<div class="gp-wrap gp-section__grid">
					<header class="gp-section__head"><h2 id="gp-related-title" class="gp-section__title"><?php esc_html_e( 'Same imprint', 'gridline' ); ?></h2></header>
					<div class="gp-new gp-new--even">
						<?php
						foreach ( $gridline_related->posts as $gridline_book ) {
							get_template_part( 'template-parts/book-tile', null, array( 'post' => $gridline_book ) );
						}
						?>
					</div>
				</div>
			</section>
		<?php endif; ?>
	</article>
	<?php
endwhile;

get_footer();
