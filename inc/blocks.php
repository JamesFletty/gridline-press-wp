<?php
/**
 * Dynamic block: gridline/upcoming-events.
 *
 * @package Gridline
 */

defined( 'ABSPATH' ) || exit;

add_action( 'init', 'gridline_register_blocks' );

/**
 * Register from block.json with a PHP render callback.
 */
function gridline_register_blocks(): void {
	register_block_type(
		GRIDLINE_DIR . '/blocks/upcoming-events',
		array( 'render_callback' => 'gridline_render_upcoming_events' )
	);
}

/**
 * Upcoming events: date on or after today (site timezone), soonest first.
 *
 * @param int $count Number of events.
 */
function gridline_upcoming_events( int $count = 3 ): array {
	$query = new WP_Query(
		array(
			'post_type'      => 'gp_event',
			'post_status'    => 'publish',
			'posts_per_page' => max( 1, min( 12, $count ) ),
			'meta_key'       => '_gp_event_date', // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
			'orderby'        => 'meta_value',
			'order'          => 'ASC',
			'no_found_rows'  => true,
			'meta_query'     => array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
				array(
					'key'     => '_gp_event_date',
					'value'   => wp_date( 'Y-m-d' ),
					'compare' => '>=',
					'type'    => 'DATE',
				),
			),
		)
	);
	return $query->posts;
}

/**
 * Render callback (also used directly by the front page).
 *
 * @param array $attributes Block attributes.
 */
function gridline_render_upcoming_events( array $attributes = array() ): string {
	$count   = (int) ( $attributes['count'] ?? 3 );
	$heading = (string) ( $attributes['heading'] ?? __( 'Upcoming', 'gridline' ) );
	$events  = gridline_upcoming_events( $count );

	$wrapper = function_exists( 'get_block_wrapper_attributes' ) && class_exists( 'WP_Block_Supports' ) && ! empty( WP_Block_Supports::$block_to_render )
		? get_block_wrapper_attributes( array( 'class' => 'gp-events' ) )
		: 'class="gp-events"';

	ob_start();
	?>
	<section <?php echo $wrapper; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- core-escaped. ?> aria-label="<?php echo esc_attr( $heading ); ?>">
		<?php if ( $heading ) : ?>
			<h2 class="gp-events__heading"><?php echo esc_html( $heading ); ?></h2>
		<?php endif; ?>
		<?php if ( ! $events ) : ?>
			<p class="gp-events__empty"><?php esc_html_e( 'No events scheduled. New dates are announced in the newsletter.', 'gridline' ); ?></p>
		<?php else : ?>
			<ol class="gp-events__list" role="list">
				<?php foreach ( $events as $event ) : ?>
					<?php
					$date = (string) get_post_meta( $event->ID, '_gp_event_date', true );
					$ts   = $date ? strtotime( $date . ' 12:00:00' ) : false;
					?>
					<li class="gp-event">
						<time class="gp-event__date" datetime="<?php echo esc_attr( $date ); ?>">
							<span class="gp-event__day"><?php echo esc_html( $ts ? wp_date( 'd', $ts ) : '' ); ?></span>
							<span class="gp-event__month"><?php echo esc_html( $ts ? wp_date( 'M Y', $ts ) : '' ); ?></span>
						</time>
						<div class="gp-event__body">
							<p class="gp-event__kind"><?php echo esc_html( (string) get_post_meta( $event->ID, '_gp_kind', true ) ); ?></p>
							<h3 class="gp-event__title"><a href="<?php echo esc_url( get_permalink( $event ) ); ?>"><?php echo esc_html( get_the_title( $event ) ); ?></a></h3>
							<p class="gp-event__meta"><?php echo esc_html( implode( ' · ', array_filter( array( (string) get_post_meta( $event->ID, '_gp_event_time', true ), (string) get_post_meta( $event->ID, '_gp_venue', true ) ) ) ) ); ?></p>
						</div>
					</li>
				<?php endforeach; ?>
			</ol>
		<?php endif; ?>
	</section>
	<?php
	return (string) ob_get_clean();
}
