<?php
/**
 * Front page.
 *
 * @package Gridline
 */

defined( 'ABSPATH' ) || exit;

get_header();

$gridline_catalog = new WP_Query( gridline_catalog_args( '', 'newest' ) );
$gridline_books   = $gridline_catalog->posts;
$gridline_new     = array_slice( $gridline_books, 0, 3 );
?>

<section class="gp-poster" aria-labelledby="gp-poster-title">
	<div class="gp-wrap gp-poster__grid">
		<h1 id="gp-poster-title" class="gp-poster__title"><span><?php esc_html_e( 'Books on', 'gridline' ); ?><br><?php esc_html_e( 'buildings,', 'gridline' ); ?><br><?php esc_html_e( 'images', 'gridline' ); ?> <span class="gp-blue">&amp;</span><br><?php esc_html_e( 'space.', 'gridline' ); ?></span></h1>
		<aside class="gp-poster__side">
			<p class="gp-label"><?php esc_html_e( 'Catalogue 2026/27', 'gridline' ); ?></p>
			<div class="gp-poster__block">
				<span class="gp-poster__count"><?php echo esc_html( (string) count( $gridline_books ) ); ?></span>
				<span><?php esc_html_e( 'titles in print across three imprints', 'gridline' ); ?></span>
			</div>
			<p class="gp-poster__lead"><?php esc_html_e( 'An independent publisher of architecture, photography and design writing, with a small gallery on the third floor. We make fewer books, and make them carefully.', 'gridline' ); ?></p>
		</aside>
	</div>
	<nav class="gp-wrap gp-poster__index" aria-label="<?php esc_attr_e( 'Sections', 'gridline' ); ?>">
		<a href="#new"><span>01</span><?php esc_html_e( 'New releases', 'gridline' ); ?></a>
		<a href="#catalogue"><span>02</span><?php esc_html_e( 'Catalogue index', 'gridline' ); ?></a>
		<a href="#gallery"><span>03</span><?php esc_html_e( 'Gallery programme', 'gridline' ); ?></a>
		<a href="#about"><span>04</span><?php esc_html_e( 'About the press', 'gridline' ); ?></a>
	</nav>
</section>

<section class="gp-section" id="new" aria-labelledby="gp-new-title">
	<div class="gp-wrap gp-section__grid">
		<header class="gp-section__head">
			<p class="gp-label">01</p>
			<h2 id="gp-new-title" class="gp-section__title"><?php esc_html_e( 'New releases', 'gridline' ); ?></h2>
		</header>
		<div class="gp-new">
			<?php
			foreach ( $gridline_new as $gridline_i => $gridline_book ) {
				get_template_part(
					'template-parts/book-tile',
					null,
					array(
						'post' => $gridline_book,
						'size' => 0 === $gridline_i ? 'l' : 'm',
					)
				);
			}
			?>
		</div>
	</div>
</section>

<section class="gp-section" id="catalogue" aria-labelledby="gp-cat-title">
	<div class="gp-wrap gp-section__grid">
		<header class="gp-section__head">
			<p class="gp-label">02</p>
			<h2 id="gp-cat-title" class="gp-section__title"><?php esc_html_e( 'Catalogue index', 'gridline' ); ?></h2>
			<a class="gp-arrow" href="<?php echo esc_url( get_post_type_archive_link( 'gp_book' ) ); ?>"><?php esc_html_e( 'Full catalogue', 'gridline' ); ?></a>
		</header>
		<?php get_template_part( 'template-parts/catalog-index', null, array( 'posts' => $gridline_books ) ); ?>
	</div>
</section>

<section class="gp-section gp-gallery" id="gallery" aria-labelledby="gp-gallery-title">
	<div class="gp-wrap gp-section__grid">
		<header class="gp-section__head">
			<p class="gp-label">03</p>
			<h2 id="gp-gallery-title" class="gp-section__title"><?php esc_html_e( 'Gallery programme', 'gridline' ); ?></h2>
			<p class="gp-gallery__note"><?php esc_html_e( 'Launches, talks and exhibitions on the third floor. Free entry; launches are first come, first seated.', 'gridline' ); ?></p>
		</header>
		<?php
		echo gridline_render_upcoming_events( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in renderer.
			array(
				'count'   => 4,
				'heading' => '',
			)
		);
		?>
	</div>
</section>

<section class="gp-section" id="about" aria-labelledby="gp-about-title">
	<div class="gp-wrap gp-section__grid">
		<header class="gp-section__head">
			<p class="gp-label">04</p>
			<h2 id="gp-about-title" class="gp-section__title"><?php esc_html_e( 'About the press', 'gridline' ); ?></h2>
		</header>
		<div class="gp-about">
			<p class="gp-about__lead"><?php esc_html_e( 'Gridline was started by two architects and a typesetter who wanted architecture books that were as considered as the buildings in them.', 'gridline' ); ?></p>
			<div class="gp-about__cols">
				<p><?php esc_html_e( 'Every title is designed in-house on the same twelve-column grid, printed offset on uncoated stock, and sewn so it lies flat on a drafting table. We publish around eight books a year across three imprints: Architecture, Image, and Field Notes.', 'gridline' ); ?></p>
				<p><?php esc_html_e( 'The gallery shows the work behind the books: drawings, models, contact sheets and proofs. Submissions are open twice a year; we read everything.', 'gridline' ); ?></p>
			</div>
			<dl class="gp-stats">
				<div><dt><?php esc_html_e( 'Founded', 'gridline' ); ?></dt><dd>2014</dd></div>
				<div><dt><?php esc_html_e( 'Titles published', 'gridline' ); ?></dt><dd>86</dd></div>
				<div><dt><?php esc_html_e( 'Exhibitions', 'gridline' ); ?></dt><dd>41</dd></div>
			</dl>
		</div>
	</div>
</section>

<?php
get_footer();
