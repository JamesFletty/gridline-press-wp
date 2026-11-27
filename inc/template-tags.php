<?php
/**
 * Template helpers and generated geometric book covers.
 *
 * @package Gridline
 */

defined( 'ABSPATH' ) || exit;

/**
 * First imprint term of a post.
 *
 * @param int $post_id Post ID.
 */
function gridline_imprint( int $post_id ): ?object {
	$terms = get_the_terms( $post_id, 'gp_imprint' );
	return is_array( $terms ) && $terms ? $terms[0] : null;
}

/**
 * Book fact rows (label ⇒ value), skipping empties.
 *
 * @param int $post_id Post ID.
 * @return array<string, string>
 */
function gridline_book_facts( int $post_id ): array {
	$rows = array();
	foreach ( gridline_meta_schema( 'gp_book' ) as $key => $field ) {
		$value = (string) get_post_meta( $post_id, $key, true );
		if ( '' === $value || '_gp_author' === $key ) {
			continue;
		}
		if ( '_gp_price' === $key ) {
			$value = '$' . number_format_i18n( (float) $value );
		} elseif ( '_gp_pages' === $key ) {
			/* translators: %s page count */
			$value = sprintf( _n( '%s page', '%s pages', (int) $value, 'gridline' ), number_format_i18n( (int) $value ) );
		}
		$rows[ $field['label'] ] = $value;
	}
	return $rows;
}

/**
 * Split a title into lines of roughly $width characters.
 *
 * @param string $text  Text.
 * @param int    $width Target width.
 * @return string[]
 */
function gridline_wrap_lines( string $text, int $width = 13 ): array {
	return explode( "\n", wordwrap( $text, $width, "\n", true ) );
}

/**
 * Deterministic, Swiss-style geometric cover for a book (used when there is
 * no cover image). The pattern and colourway derive from the post itself, so
 * every book is distinct and stable across renders.
 *
 * @param int    $post_id Post ID.
 * @param string $context Context for unique IDs.
 */
function gridline_cover_svg( int $post_id, string $context = 'tile' ): string {
	if ( has_post_thumbnail( $post_id ) ) {
		return (string) get_the_post_thumbnail( $post_id, 'gp-cover', array( 'loading' => 'lazy', 'decoding' => 'async' ) );
	}

	$title  = get_the_title( $post_id );
	$seed   = crc32( $title . $post_id );
	$rand   = static function ( int $n ) use ( &$seed ): int {
		$seed = ( $seed * 1103515245 + 12345 ) & 0x7fffffff;
		return $seed % max( 1, $n );
	};
	$ways   = array(
		array( '#FFFFFF', '#0A0A0A', '#0038FF' ),
		array( '#0038FF', '#FFFFFF', '#0A0A0A' ),
		array( '#0A0A0A', '#FFFFFF', '#0038FF' ),
		array( '#F2F2F2', '#0038FF', '#0A0A0A' ),
	);
	[ $bg, $fg, $ac ] = $ways[ $rand( count( $ways ) ) ];
	$pattern = $post_id % 5;
	$art     = '';

	switch ( $pattern ) {
		case 0: // Big circle on a modular grid.
			for ( $i = 1; $i < 4; $i++ ) {
				$art .= sprintf( '<line x1="%1$d" y1="0" x2="%1$d" y2="300" stroke="%2$s" stroke-width="1" opacity=".35"/>', $i * 50, $fg );
			}
			$art .= sprintf( '<circle cx="%d" cy="%d" r="%d" fill="%s"/>', 100 + ( $rand( 3 ) - 1 ) * 40, 110, 70 + $rand( 20 ), $ac );
			$art .= sprintf( '<rect x="100" y="110" width="100" height="100" fill="%s"/>', $fg );
			break;
		case 1: // Stacked bars, Müller-Brockmann rhythm.
			for ( $i = 0; $i < 9; $i++ ) {
				$x    = 20 + ( 0 === $i % 3 ? 0 : $rand( 40 ) );
				$w    = min( 40 + $rand( 140 ), 180 - $x );
				$art .= sprintf( '<rect x="%d" y="%d" width="%d" height="10" fill="%s"/>', $x, 24 + $i * 20, $w, 4 === $i ? $ac : $fg );
			}
			break;
		case 2: // Quarter-circle tiles.
			for ( $r = 0; $r < 4; $r++ ) {
				for ( $c = 0; $c < 3; $c++ ) {
					$x    = 20 + $c * 54;
					$y    = 20 + $r * 54;
					$rot  = $rand( 4 ) * 90;
					$fill = 0 === $rand( 5 ) ? $ac : $fg;
					$art .= sprintf( '<path d="M%1$d %2$d h54 a54 54 0 0 1 -54 54 z" fill="%3$s" transform="rotate(%4$d %5$d %6$d)"/>', $x, $y, $fill, $rot, $x + 27, $y + 27 );
				}
			}
			break;
		case 3: // Diagonal band over a dot field.
			for ( $r = 0; $r < 10; $r++ ) {
				for ( $c = 0; $c < 7; $c++ ) {
					$art .= sprintf( '<circle cx="%d" cy="%d" r="2.2" fill="%s"/>', 25 + $c * 25, 20 + $r * 22, $fg );
				}
			}
			$art .= sprintf( '<path d="M0 %1$d L200 %2$d L200 %3$d L0 %4$d z" fill="%5$s"/>', 150 + $rand( 40 ), 40 + $rand( 30 ), 90 + $rand( 30 ), 200 + $rand( 40 ), $ac );
			break;
		default: // Offset squares.
			for ( $i = 0; $i < 5; $i++ ) {
				$s    = 160 - $i * 30;
				$art .= sprintf( '<rect x="%1$d" y="%2$d" width="%3$d" height="%3$d" fill="none" stroke="%4$s" stroke-width="%5$d"/>', 20 + $i * 9, 20 + $i * 18, $s, 0 === $i ? $ac : $fg, 0 === $i ? 8 : 2 );
			}
			break;
	}

	$lines = '';
	foreach ( array_slice( gridline_wrap_lines( $title, 14 ), 0, 3 ) as $i => $line ) {
		$lines .= sprintf( '<tspan x="16" dy="%s">%s</tspan>', 0 === $i ? '0' : '1.05em', esc_html( $line ) );
	}
	$author  = (string) get_post_meta( $post_id, '_gp_author', true );
	$imprint = gridline_imprint( $post_id );

	return sprintf(
		'<svg class="gp-cover" viewBox="0 0 200 300" role="img" aria-label="%1$s" focusable="false" data-context="%9$s"><rect width="200" height="300" fill="%2$s"/>%3$s<rect x="0" y="226" width="200" height="74" fill="%2$s"/><line x1="16" y1="226" x2="184" y2="226" stroke="%4$s" stroke-width="2"/><text class="gp-cover__title" x="16" y="244" fill="%4$s">%5$s</text><text class="gp-cover__meta" x="16" y="290" fill="%4$s">%6$s</text><text class="gp-cover__meta" x="184" y="290" fill="%4$s" text-anchor="end">%7$s</text><rect x="172" y="12" width="16" height="16" fill="%8$s"/></svg>',
		esc_attr( sprintf( /* translators: %s title */ __( 'Cover of %s', 'gridline' ), $title ) ),
		esc_attr( $bg ),
		$art,
		esc_attr( $fg ),
		$lines,
		esc_html( $author ),
		esc_html( $imprint->name ?? '' ),
		esc_attr( $ac ),
		esc_attr( $context )
	);
}

/**
 * Fallback menu.
 *
 * @param array $args wp_nav_menu args.
 */
function gridline_fallback_menu( array $args = array() ): void {
	$items = array(
		__( 'Catalogue', 'gridline' ) => get_post_type_archive_link( 'gp_book' ),
		__( 'New', 'gridline' )       => home_url( '/#new' ),
		__( 'Gallery', 'gridline' )   => home_url( '/#gallery' ),
		__( 'About', 'gridline' )     => home_url( '/#about' ),
	);
	$class = $args['menu_class'] ?? 'gp-menu';
	echo '<ul class="' . esc_attr( $class ) . '">';
	foreach ( $items as $label => $url ) {
		printf( '<li><a href="%s">%s</a></li>', esc_url( $url ), esc_html( $label ) );
	}
	echo '</ul>';
}
