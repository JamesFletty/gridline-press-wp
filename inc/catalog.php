<?php
/**
 * Catalogue querying: validated ?imprint= and ?sort= query vars, and one
 * function that turns them into WP_Query args. The archive (pre_get_posts),
 * the REST endpoint and the front-page index all share it.
 *
 * @package Gridline
 */

defined( 'ABSPATH' ) || exit;

add_filter( 'query_vars', 'gridline_query_vars' );

/**
 * Register public query vars.
 *
 * @param string[] $vars Vars.
 * @return string[]
 */
function gridline_query_vars( array $vars ): array {
	$vars[] = 'imprint';
	$vars[] = 'sort';
	return $vars;
}

/**
 * Whitelisted sort orders.
 *
 * @return array<string, array{label: string, orderby: string, order: string, meta_key?: string}>
 */
function gridline_sort_options(): array {
	return array(
		'newest' => array(
			'label'    => __( 'Newest', 'gridline' ),
			'orderby'  => 'meta_value_num',
			'meta_key' => '_gp_year', // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
			'order'    => 'DESC',
		),
		'oldest' => array(
			'label'    => __( 'Oldest', 'gridline' ),
			'orderby'  => 'meta_value_num',
			'meta_key' => '_gp_year', // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
			'order'    => 'ASC',
		),
		'title'  => array(
			'label'   => __( 'A–Z', 'gridline' ),
			'orderby' => 'title',
			'order'   => 'ASC',
		),
		'pages'  => array(
			'label'    => __( 'Thickest', 'gridline' ),
			'orderby'  => 'meta_value_num',
			'meta_key' => '_gp_pages', // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
			'order'    => 'DESC',
		),
	);
}

/**
 * Validate an imprint slug against existing terms.
 *
 * @param mixed $raw Raw value.
 */
function gridline_validate_imprint( $raw ): string {
	$slug = sanitize_key( (string) $raw );
	if ( '' === $slug ) {
		return '';
	}
	$terms = get_terms(
		array(
			'taxonomy'   => 'gp_imprint',
			'hide_empty' => false,
		)
	);
	return is_array( $terms ) && in_array( $slug, wp_list_pluck( $terms, 'slug' ), true ) ? $slug : '';
}

/**
 * Validate a sort key; falls back to "newest".
 *
 * @param mixed $raw Raw value.
 */
function gridline_validate_sort( $raw ): string {
	$sort = sanitize_key( (string) $raw );
	return array_key_exists( $sort, gridline_sort_options() ) ? $sort : 'newest';
}

/**
 * Current request's imprint / sort.
 */
function gridline_current_imprint(): string {
	return gridline_validate_imprint( get_query_var( 'imprint' ) );
}

/**
 * Current sort.
 */
function gridline_current_sort(): string {
	return gridline_validate_sort( get_query_var( 'sort' ) );
}

/**
 * Build WP_Query args for the catalogue.
 *
 * @param string $imprint Validated imprint slug or ''.
 * @param string $sort    Validated sort key.
 * @param array  $extra   Extra args merged last.
 */
function gridline_catalog_args( string $imprint = '', string $sort = 'newest', array $extra = array() ): array {
	$order = gridline_sort_options()[ $sort ] ?? gridline_sort_options()['newest'];
	$args  = array(
		'post_type'      => 'gp_book',
		'post_status'    => 'publish',
		'posts_per_page' => 100,
		'orderby'        => $order['orderby'],
		'order'          => $order['order'],
		'no_found_rows'  => true,
	);
	if ( isset( $order['meta_key'] ) ) {
		$args['meta_key'] = $order['meta_key']; // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
	}
	if ( $imprint ) {
		$args['tax_query'] = array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query
			array(
				'taxonomy' => 'gp_imprint',
				'field'    => 'slug',
				'terms'    => $imprint,
			),
		);
	}
	return array_merge( $args, $extra );
}

add_action( 'pre_get_posts', 'gridline_catalog_main_query' );

/**
 * Apply filter + sort to the catalogue archive's main query.
 *
 * @param WP_Query $query Query.
 */
function gridline_catalog_main_query( WP_Query $query ): void {
	if ( is_admin() || ! $query->is_main_query() ) {
		return;
	}
	if ( $query->is_post_type_archive( 'gp_book' ) ) {
		foreach ( gridline_catalog_args( gridline_current_imprint(), gridline_current_sort() ) as $key => $value ) {
			$query->set( $key, $value );
		}
	} elseif ( $query->is_post_type_archive( 'gp_event' ) ) {
		$query->set( 'meta_key', '_gp_event_date' ); // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
		$query->set( 'orderby', 'meta_value' );
		$query->set( 'order', 'DESC' );
	}
}

/**
 * URL for a catalogue view, keeping only non-default params.
 *
 * @param string $imprint Imprint slug.
 * @param string $sort    Sort key.
 */
function gridline_catalog_url( string $imprint, string $sort ): string {
	$args = array_filter(
		array(
			'imprint' => $imprint,
			'sort'    => 'newest' === $sort ? '' : $sort,
		)
	);
	$base = get_post_type_archive_link( 'gp_book' );
	return $args ? add_query_arg( $args, $base ) : $base;
}
