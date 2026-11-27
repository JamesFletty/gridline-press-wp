<?php
/**
 * Catalogue index: imprint + sort controls and an asymmetric table.
 *
 * Works without JS (controls are plain links to ?imprint=&sort= on the
 * catalogue archive, filtered by WP_Query). With JS, catalog.js reads the
 * cached REST endpoint once and re-filters / re-orders rows in place.
 *
 * Expects $args['posts'] (WP_Post[]), optional $args['imprint'], $args['sort'].
 *
 * @package Gridline
 */

defined( 'ABSPATH' ) || exit;

$gridline_posts   = $args['posts'] ?? array();
$gridline_current = $args['imprint'] ?? '';
$gridline_sort    = $args['sort'] ?? 'newest';
$gridline_terms   = get_terms(
	array(
		'taxonomy'   => 'gp_imprint',
		'hide_empty' => true,
	)
);
$gridline_terms   = is_array( $gridline_terms ) ? $gridline_terms : array();
wp_enqueue_script( 'gridline-catalog' );
?>
<div class="gp-catalog" data-gp-catalog data-endpoint="<?php echo esc_url( rest_url( 'gridline/v1/catalog' ) ); ?>" data-base="<?php echo esc_url( get_post_type_archive_link( 'gp_book' ) ); ?>" data-imprint="<?php echo esc_attr( $gridline_current ); ?>" data-sort="<?php echo esc_attr( $gridline_sort ); ?>">
	<div class="gp-catalog__controls">
		<nav class="gp-catalog__filter" aria-label="<?php esc_attr_e( 'Filter by imprint', 'gridline' ); ?>">
			<p class="gp-label"><?php esc_html_e( 'Imprint', 'gridline' ); ?></p>
			<ul role="list">
				<li><a href="<?php echo esc_url( gridline_catalog_url( '', $gridline_sort ) ); ?>" data-imprint=""<?php echo '' === $gridline_current ? ' aria-current="true"' : ''; ?>><?php esc_html_e( 'All', 'gridline' ); ?> <span><?php echo esc_html( (string) count( $gridline_posts ) ); ?></span></a></li>
				<?php foreach ( $gridline_terms as $gridline_term ) : ?>
					<li><a href="<?php echo esc_url( gridline_catalog_url( $gridline_term->slug, $gridline_sort ) ); ?>" data-imprint="<?php echo esc_attr( $gridline_term->slug ); ?>"<?php echo $gridline_term->slug === $gridline_current ? ' aria-current="true"' : ''; ?>><?php echo esc_html( $gridline_term->name ); ?> <span><?php echo esc_html( (string) $gridline_term->count ); ?></span></a></li>
				<?php endforeach; ?>
			</ul>
		</nav>
		<nav class="gp-catalog__sort" aria-label="<?php esc_attr_e( 'Sort', 'gridline' ); ?>">
			<p class="gp-label"><?php esc_html_e( 'Sort', 'gridline' ); ?></p>
			<ul role="list">
				<?php foreach ( gridline_sort_options() as $gridline_key => $gridline_opt ) : ?>
					<li><a href="<?php echo esc_url( gridline_catalog_url( $gridline_current, $gridline_key ) ); ?>" data-sort="<?php echo esc_attr( $gridline_key ); ?>"<?php echo $gridline_key === $gridline_sort ? ' aria-current="true"' : ''; ?>><?php echo esc_html( $gridline_opt['label'] ); ?></a></li>
				<?php endforeach; ?>
			</ul>
		</nav>
		<p class="gp-catalog__count" role="status" aria-live="polite" data-gp-count><?php echo esc_html( sprintf( /* translators: %d count */ _n( '%d title', '%d titles', count( $gridline_posts ), 'gridline' ), count( $gridline_posts ) ) ); ?></p>
	</div>

	<table class="gp-index">
		<caption class="screen-reader-text"><?php esc_html_e( 'Catalogue of titles', 'gridline' ); ?></caption>
		<thead>
			<tr>
				<th scope="col" class="gp-index__no"><?php esc_html_e( 'No.', 'gridline' ); ?></th>
				<th scope="col"><?php esc_html_e( 'Title / Author', 'gridline' ); ?></th>
				<th scope="col" class="gp-index__imprint"><?php esc_html_e( 'Imprint', 'gridline' ); ?></th>
				<th scope="col" class="gp-index__num"><?php esc_html_e( 'Year', 'gridline' ); ?></th>
				<th scope="col" class="gp-index__num gp-index__pages"><?php esc_html_e( 'Pages', 'gridline' ); ?></th>
			</tr>
		</thead>
		<tbody data-gp-rows>
			<?php foreach ( $gridline_posts as $gridline_i => $gridline_book ) : ?>
				<?php $gridline_imprint = gridline_imprint( $gridline_book->ID ); ?>
				<tr data-id="<?php echo esc_attr( (string) $gridline_book->ID ); ?>">
					<td class="gp-index__no" data-gp-no><?php echo esc_html( str_pad( (string) ( $gridline_i + 1 ), 2, '0', STR_PAD_LEFT ) ); ?></td>
					<td class="gp-index__title">
						<a href="<?php echo esc_url( get_permalink( $gridline_book ) ); ?>">
							<span class="gp-index__thumb" aria-hidden="true"><?php echo gridline_cover_svg( $gridline_book->ID, 'row' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
							<span class="gp-index__name"><?php echo esc_html( get_the_title( $gridline_book ) ); ?></span>
							<span class="gp-index__author"><?php echo esc_html( (string) get_post_meta( $gridline_book->ID, '_gp_author', true ) ); ?></span>
						</a>
					</td>
					<td class="gp-index__imprint"><?php echo esc_html( $gridline_imprint->name ?? '' ); ?></td>
					<td class="gp-index__num"><?php echo esc_html( (string) get_post_meta( $gridline_book->ID, '_gp_year', true ) ); ?></td>
					<td class="gp-index__num gp-index__pages"><?php echo esc_html( (string) get_post_meta( $gridline_book->ID, '_gp_pages', true ) ); ?></td>
				</tr>
			<?php endforeach; ?>
		</tbody>
	</table>
	<p class="gp-catalog__empty" data-gp-empty<?php echo $gridline_posts ? ' hidden' : ''; ?>><?php esc_html_e( 'No titles in this imprint yet.', 'gridline' ); ?></p>
</div>
