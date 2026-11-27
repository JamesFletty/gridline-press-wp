<?php
/**
 * Read-only, cached REST endpoint: GET /wp-json/gridline/v1/catalog
 *
 * Optional params: imprint (slug), sort (newest|oldest|title|pages).
 * Responses are cached in a transient keyed by a "catalogue version" that is
 * bumped whenever a book or imprint changes, so stale data never survives an
 * edit and no cache keys need to be hunted down.
 *
 * @package Gridline
 */

defined( 'ABSPATH' ) || exit;

add_action( 'rest_api_init', 'gridline_register_rest' );

/**
 * Register the route.
 */
function gridline_register_rest(): void {
	register_rest_route(
		'gridline/v1',
		'/catalog',
		array(
			'methods'             => WP_REST_Server::READABLE,
			'callback'            => 'gridline_rest_catalog',
			'permission_callback' => '__return_true', // Public, read-only, published content only.
			'args'                => array(
				'imprint' => array(
					'type'              => 'string',
					'sanitize_callback' => 'sanitize_key',
					'default'           => '',
				),
				'sort'    => array(
					'type'    => 'string',
					'enum'    => array_keys( gridline_sort_options() ),
					'default' => 'newest',
				),
			),
		)
	);
}

/**
 * Endpoint callback.
 *
 * @param WP_REST_Request $request Request.
 */
function gridline_rest_catalog( WP_REST_Request $request ): WP_REST_Response {
	$imprint = gridline_validate_imprint( $request->get_param( 'imprint' ) );
	$sort    = gridline_validate_sort( $request->get_param( 'sort' ) );
	$key     = 'gridline_catalog_' . (int) get_option( 'gridline_catalog_version', 1 ) . '_' . md5( $imprint . '|' . $sort );
	$data    = get_transient( $key );

	if ( false === $data ) {
		$query = new WP_Query( gridline_catalog_args( $imprint, $sort ) );
		$data  = array(
			'imprint'   => $imprint ?: null,
			'sort'      => $sort,
			'generated' => gmdate( 'c' ),
			'count'     => $query->post_count,
			'books'     => array_map( 'gridline_book_to_array', $query->posts ),
		);
		set_transient( $key, $data, HOUR_IN_SECONDS );
	}

	$response = new WP_REST_Response( $data );
	$response->header( 'Cache-Control', 'public, max-age=300' );
	return $response;
}

/**
 * Public representation of a book.
 *
 * @param WP_Post $post Book.
 * @return array<string, mixed>
 */
function gridline_book_to_array( WP_Post $post ): array {
	$imprint = get_the_terms( $post->ID, 'gp_imprint' );
	$imprint = is_array( $imprint ) ? $imprint[0] : null;
	return array(
		'id'      => $post->ID,
		'title'   => get_the_title( $post ),
		'link'    => get_permalink( $post ),
		'author'  => (string) get_post_meta( $post->ID, '_gp_author', true ),
		'year'    => (int) get_post_meta( $post->ID, '_gp_year', true ),
		'pages'   => (int) get_post_meta( $post->ID, '_gp_pages', true ),
		'isbn'    => (string) get_post_meta( $post->ID, '_gp_isbn', true ),
		'edition' => (string) get_post_meta( $post->ID, '_gp_edition', true ),
		'price'   => (string) get_post_meta( $post->ID, '_gp_price', true ),
		'imprint' => $imprint ? array(
			'slug' => $imprint->slug,
			'name' => $imprint->name,
		) : null,
	);
}

/**
 * Invalidate every cached catalogue response.
 */
function gridline_bump_catalog_version(): void {
	update_option( 'gridline_catalog_version', (int) get_option( 'gridline_catalog_version', 1 ) + 1, false );
}
add_action( 'save_post_gp_book', 'gridline_bump_catalog_version' );
add_action( 'edited_gp_imprint', 'gridline_bump_catalog_version' );
add_action( 'created_gp_imprint', 'gridline_bump_catalog_version' );
add_action( 'delete_gp_imprint', 'gridline_bump_catalog_version' );
add_action(
	'deleted_post',
	static function ( int $post_id, $post = null ): void {
		if ( $post instanceof WP_Post && 'gp_book' === $post->post_type ) {
			gridline_bump_catalog_version();
		}
	},
	10,
	2
);
