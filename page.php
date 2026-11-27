<?php
/**
 * Default page.
 *
 * @package Gridline
 */

defined( 'ABSPATH' ) || exit;

get_header();
while ( have_posts() ) :
	the_post();
	?>
	<article <?php post_class( 'gp-section' ); ?>>
		<div class="gp-wrap gp-section__grid">
			<header class="gp-section__head"><h1 class="gp-section__title"><?php the_title(); ?></h1></header>
			<div class="gp-prose"><?php the_content(); ?></div>
		</div>
	</article>
	<?php
endwhile;
get_footer();
