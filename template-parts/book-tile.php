<?php
/**
 * Book tile (cover + caption). Expects $args['post'], optional $args['size'].
 *
 * @package Gridline
 */

defined( 'ABSPATH' ) || exit;

$gridline_book = $args['post'] ?? null;
if ( ! $gridline_book ) {
	return;
}
$gridline_size    = $args['size'] ?? 'm';
$gridline_imprint = gridline_imprint( $gridline_book->ID );
?>
<article class="gp-tile gp-tile--<?php echo esc_attr( $gridline_size ); ?>">
	<a class="gp-tile__link" href="<?php echo esc_url( get_permalink( $gridline_book ) ); ?>">
		<div class="gp-tile__cover"><?php echo gridline_cover_svg( $gridline_book->ID, 'tile-' . $gridline_size ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in helper. ?></div>
		<div class="gp-tile__caption">
			<p class="gp-tile__imprint"><?php echo esc_html( ( $gridline_imprint->name ?? '' ) . ' — ' . get_post_meta( $gridline_book->ID, '_gp_year', true ) ); ?></p>
			<h3 class="gp-tile__title"><?php echo esc_html( get_the_title( $gridline_book ) ); ?></h3>
			<p class="gp-tile__author"><?php echo esc_html( (string) get_post_meta( $gridline_book->ID, '_gp_author', true ) ); ?></p>
			<?php if ( 'l' === $gridline_size ) : ?>
				<p class="gp-tile__excerpt"><?php echo esc_html( get_the_excerpt( $gridline_book ) ); ?></p>
			<?php endif; ?>
		</div>
	</a>
</article>
