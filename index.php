<?php
/**
 * Fallback: journal / posts index.
 *
 * @package Gridline
 */

defined( 'ABSPATH' ) || exit;

get_header();
?>
<section class="gp-section">
	<div class="gp-wrap gp-section__grid">
		<header class="gp-section__head"><h1 class="gp-section__title"><?php esc_html_e( 'Journal', 'gridline' ); ?></h1></header>
		<?php if ( have_posts() ) : ?>
			<ol class="gp-journal" role="list">
				<?php
				while ( have_posts() ) :
					the_post();
					?>
					<li <?php post_class(); ?>><time datetime="<?php echo esc_attr( get_the_date( 'c' ) ); ?>"><?php echo esc_html( get_the_date() ); ?></time><h2><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2><?php the_excerpt(); ?></li>
				<?php endwhile; ?>
			</ol>
			<?php the_posts_pagination(); ?>
		<?php else : ?>
			<p><?php esc_html_e( 'Nothing here yet.', 'gridline' ); ?></p>
		<?php endif; ?>
	</div>
</section>
<?php
get_footer();
