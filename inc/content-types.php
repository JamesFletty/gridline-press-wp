<?php
/**
 * Content model: gp_book + gp_event post types, gp_imprint taxonomy, and
 * native meta boxes driven by one schema per post type.
 *
 * @package Gridline
 */

defined( 'ABSPATH' ) || exit;

add_action( 'init', 'gridline_register_content_types' );

/**
 * Register post types, taxonomy and meta.
 */
function gridline_register_content_types(): void {
	register_post_type(
		'gp_book',
		array(
			'labels'        => array(
				'name'           => _x( 'Books', 'post type general name', 'gridline' ),
				'singular_name'  => _x( 'Book', 'post type singular name', 'gridline' ),
				'add_new_item'   => __( 'Add new book', 'gridline' ),
				'edit_item'      => __( 'Edit book', 'gridline' ),
				'all_items'      => __( 'Catalogue', 'gridline' ),
				'featured_image' => __( 'Cover', 'gridline' ),
			),
			'public'        => true,
			'show_in_rest'  => true,
			'has_archive'   => 'catalogue',
			'rewrite'       => array(
				'slug'       => 'books',
				'with_front' => false,
			),
			'menu_icon'     => 'dashicons-book-alt',
			'menu_position' => 20,
			'supports'      => array( 'title', 'editor', 'excerpt', 'thumbnail', 'revisions' ),
		)
	);

	register_post_type(
		'gp_event',
		array(
			'labels'        => array(
				'name'          => _x( 'Events', 'post type general name', 'gridline' ),
				'singular_name' => _x( 'Event', 'post type singular name', 'gridline' ),
				'add_new_item'  => __( 'Add new event', 'gridline' ),
				'edit_item'     => __( 'Edit event', 'gridline' ),
			),
			'public'        => true,
			'show_in_rest'  => true,
			'has_archive'   => 'events',
			'rewrite'       => array(
				'slug'       => 'events',
				'with_front' => false,
			),
			'menu_icon'     => 'dashicons-calendar-alt',
			'menu_position' => 21,
			'supports'      => array( 'title', 'editor', 'excerpt', 'thumbnail', 'revisions' ),
		)
	);

	register_taxonomy(
		'gp_imprint',
		array( 'gp_book', 'gp_event' ),
		array(
			'labels'            => array(
				'name'          => _x( 'Imprints', 'taxonomy general name', 'gridline' ),
				'singular_name' => _x( 'Imprint', 'taxonomy singular name', 'gridline' ),
			),
			'hierarchical'      => true,
			'show_in_rest'      => true,
			'show_admin_column' => true,
			'query_var'         => false, // The catalogue uses its own validated ?imprint= var.
			'rewrite'           => array( 'slug' => 'imprint' ),
		)
	);

	foreach ( array( 'gp_book', 'gp_event' ) as $type ) {
		foreach ( gridline_meta_schema( $type ) as $key => $field ) {
			register_post_meta(
				$type,
				$key,
				array(
					'type'              => 'integer' === $field['type'] ? 'integer' : 'string',
					'single'            => true,
					'show_in_rest'      => true,
					'sanitize_callback' => $field['sanitize'],
					'auth_callback'     => static fn(): bool => current_user_can( 'edit_posts' ),
				)
			);
		}
	}
}

/**
 * Meta schema per post type.
 *
 * @param string $post_type Post type.
 * @return array<string, array{label: string, type: string, input: string, sanitize: callable, placeholder?: string, help?: string}>
 */
function gridline_meta_schema( string $post_type ): array {
	if ( 'gp_event' === $post_type ) {
		return array(
			'_gp_event_date' => array(
				'label'    => __( 'Date', 'gridline' ),
				'type'     => 'string',
				'input'    => 'date',
				'sanitize' => 'gridline_sanitize_date',
				'help'     => __( 'Events on or after today appear in "Upcoming".', 'gridline' ),
			),
			'_gp_event_time' => array(
				'label'       => __( 'Time', 'gridline' ),
				'type'        => 'string',
				'input'       => 'text',
				'sanitize'    => 'sanitize_text_field',
				'placeholder' => '18:30 – 20:00',
			),
			'_gp_venue'      => array(
				'label'       => __( 'Venue', 'gridline' ),
				'type'        => 'string',
				'input'       => 'text',
				'sanitize'    => 'sanitize_text_field',
				'placeholder' => 'Gallery 1',
			),
			'_gp_kind'       => array(
				'label'       => __( 'Kind', 'gridline' ),
				'type'        => 'string',
				'input'       => 'text',
				'sanitize'    => 'sanitize_text_field',
				'placeholder' => 'Launch / Talk / Exhibition',
			),
		);
	}
	return array(
		'_gp_author'  => array(
			'label'       => __( 'Author(s)', 'gridline' ),
			'type'        => 'string',
			'input'       => 'text',
			'sanitize'    => 'sanitize_text_field',
			'placeholder' => 'Ines Varga',
		),
		'_gp_isbn'    => array(
			'label'       => __( 'ISBN-13', 'gridline' ),
			'type'        => 'string',
			'input'       => 'text',
			'sanitize'    => 'gridline_sanitize_isbn',
			'placeholder' => '978-1-234567-89-7',
			'help'        => __( 'Checksum-validated. An invalid ISBN is not saved.', 'gridline' ),
		),
		'_gp_edition' => array(
			'label'       => __( 'Edition', 'gridline' ),
			'type'        => 'string',
			'input'       => 'text',
			'sanitize'    => 'sanitize_text_field',
			'placeholder' => 'First edition',
		),
		'_gp_pages'   => array(
			'label'    => __( 'Page count', 'gridline' ),
			'type'     => 'integer',
			'input'    => 'number',
			'sanitize' => 'absint',
		),
		'_gp_year'    => array(
			'label'    => __( 'Year', 'gridline' ),
			'type'     => 'integer',
			'input'    => 'number',
			'sanitize' => 'absint',
		),
		'_gp_format'  => array(
			'label'       => __( 'Format', 'gridline' ),
			'type'        => 'string',
			'input'       => 'text',
			'sanitize'    => 'sanitize_text_field',
			'placeholder' => 'Softcover, 230 × 300 mm',
		),
		'_gp_price'   => array(
			'label'       => __( 'Price (USD)', 'gridline' ),
			'type'        => 'string',
			'input'       => 'text',
			'sanitize'    => 'gridline_sanitize_price',
			'placeholder' => '48',
		),
	);
}

/**
 * Validate an ISBN-13 checksum. Returns the input (digits and hyphens) or ''.
 *
 * @param mixed $value Raw.
 */
function gridline_sanitize_isbn( $value ): string {
	$clean  = preg_replace( '/[^0-9-]/', '', (string) $value );
	$digits = str_replace( '-', '', (string) $clean );
	if ( 13 !== strlen( $digits ) ) {
		return '';
	}
	$sum = 0;
	for ( $i = 0; $i < 12; $i++ ) {
		$sum += (int) $digits[ $i ] * ( 0 === $i % 2 ? 1 : 3 );
	}
	return ( ( 10 - $sum % 10 ) % 10 ) === (int) $digits[12] ? (string) $clean : '';
}

/**
 * Strict Y-m-d date or ''.
 *
 * @param mixed $value Raw.
 */
function gridline_sanitize_date( $value ): string {
	$date = DateTimeImmutable::createFromFormat( '!Y-m-d', (string) $value );
	return $date && $date->format( 'Y-m-d' ) === $value ? $value : '';
}

/**
 * Digits and a decimal point.
 *
 * @param mixed $value Raw.
 */
function gridline_sanitize_price( $value ): string {
	$clean = preg_replace( '/[^0-9.]/', '', (string) $value );
	return is_numeric( $clean ) ? (string) round( (float) $clean, 2 ) : '';
}

add_action( 'add_meta_boxes', 'gridline_add_meta_boxes', 10, 1 );

/**
 * Register meta boxes for both post types.
 *
 * @param string $post_type Current post type.
 */
function gridline_add_meta_boxes( string $post_type ): void {
	if ( 'gp_book' === $post_type ) {
		add_meta_box( 'gridline-meta', __( 'Bibliographic details', 'gridline' ), 'gridline_render_meta_box', 'gp_book', 'normal', 'high' );
	} elseif ( 'gp_event' === $post_type ) {
		add_meta_box( 'gridline-meta', __( 'Event details', 'gridline' ), 'gridline_render_meta_box', 'gp_event', 'side', 'high' );
	}
}

/**
 * Shared meta box UI.
 *
 * @param WP_Post $post Post.
 */
function gridline_render_meta_box( WP_Post $post ): void {
	wp_nonce_field( 'gridline_save_meta', 'gridline_meta_nonce' );
	echo '<div class="gridline-meta">';
	foreach ( gridline_meta_schema( $post->post_type ) as $key => $field ) {
		$id = 'gridline' . $key;
		printf(
			'<p><label for="%1$s"><strong>%2$s</strong></label><br><input class="widefat" type="%3$s" id="%1$s" name="%1$s" value="%4$s" placeholder="%5$s"%6$s>',
			esc_attr( $id ),
			esc_html( $field['label'] ),
			esc_attr( $field['input'] ),
			esc_attr( (string) get_post_meta( $post->ID, $key, true ) ),
			esc_attr( $field['placeholder'] ?? '' ),
			'number' === $field['input'] ? ' min="0" step="1"' : ''
		);
		if ( ! empty( $field['help'] ) ) {
			printf( '<span class="description">%s</span>', esc_html( $field['help'] ) );
		}
		echo '</p>';
	}
	echo '</div>';
}

add_action( 'save_post_gp_book', 'gridline_save_meta', 10, 2 );
add_action( 'save_post_gp_event', 'gridline_save_meta', 10, 2 );

/**
 * Save meta with nonce, autosave, revision and capability checks.
 *
 * @param int     $post_id Post ID.
 * @param WP_Post $post    Post.
 */
function gridline_save_meta( int $post_id, WP_Post $post ): void {
	$nonce = isset( $_POST['gridline_meta_nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['gridline_meta_nonce'] ) ) : '';
	if ( ! $nonce || ! wp_verify_nonce( $nonce, 'gridline_save_meta' ) ) {
		return;
	}
	if ( ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) || wp_is_post_revision( $post_id ) || ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}

	foreach ( gridline_meta_schema( $post->post_type ) as $key => $field ) {
		$name = 'gridline' . $key;
		if ( ! isset( $_POST[ $name ] ) ) {
			continue;
		}
		// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- sanitized by schema.
		$value = call_user_func( $field['sanitize'], wp_unslash( $_POST[ $name ] ) );
		if ( '' === $value || 0 === $value ) {
			delete_post_meta( $post_id, $key );
		} else {
			update_post_meta( $post_id, $key, $value );
		}
	}
}
