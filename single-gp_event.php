<?php
/**
 * Single event.
 *
 * @package Gridline
 */

defined( 'ABSPATH' ) || exit;

get_header();

while ( have_posts() ) :
	the_post();
	$gridline_id   = get_the_ID();
	$gridline_date = (string) get_post_meta( $gridline_id, '_gp_event_date', true );
	$gridline_ts   = $gridline_date ? strtotime( $gridline_date . ' 12:00:00' ) : false;
	$gridline_past = $gridline_date && $gridline_date < wp_date( 'Y-m-d' );
	?>
	<article <?php post_class( 'gp-event-single' ); ?>>
		<div class="gp-wrap gp-event-single__grid">
			<time class="gp-event-single__date" datetime="<?php echo esc_attr( $gridline_date ); ?>">
				<span><?php echo esc_html( $gridline_ts ? wp_date( 'd', $gridline_ts ) : '' ); ?></span>
				<span><?php echo esc_html( $gridline_ts ? wp_date( 'F Y', $gridline_ts ) : '' ); ?></span>
			</time>
			<div class="gp-event-single__info">
				<p class="gp-label"><?php echo esc_html( (string) get_post_meta( $gridline_id, '_gp_kind', true ) ); ?><?php echo $gridline_past ? ' — ' . esc_html__( 'Past event', 'gridline' ) : ''; ?></p>
				<h1 class="gp-book__title"><?php the_title(); ?></h1>
				<div class="gp-prose"><?php the_content(); ?></div>
				<dl class="gp-facts">
					<div><dt><?php esc_html_e( 'Time', 'gridline' ); ?></dt><dd><?php echo esc_html( (string) get_post_meta( $gridline_id, '_gp_event_time', true ) ); ?></dd></div>
					<div><dt><?php esc_html_e( 'Venue', 'gridline' ); ?></dt><dd><?php echo esc_html( (string) get_post_meta( $gridline_id, '_gp_venue', true ) ); ?></dd></div>
					<div><dt><?php esc_html_e( 'Entry', 'gridline' ); ?></dt><dd><?php esc_html_e( 'Free', 'gridline' ); ?></dd></div>
				</dl>
				<a class="gp-arrow" href="<?php echo esc_url( home_url( '/#gallery' ) ); ?>"><?php esc_html_e( 'All upcoming events', 'gridline' ); ?></a>
			</div>
		</div>
	</article>
	<?php
endwhile;

get_footer();
