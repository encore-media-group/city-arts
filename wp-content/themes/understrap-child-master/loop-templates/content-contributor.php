<?php
/**
 * Single contributor partial template.
 *
 * @package understrap
 */

?>
<article <?php post_class(); ?> id="post-<?php the_ID(); ?>">

	<header class="entry-header">

		<?php the_title( '<h1 class="entry-title">', '</h1>' ); ?>

	</header><!-- .entry-header -->
	<div class="row">
		<?php echo get_the_post_thumbnail( $post->ID, 'large' ); ?>

		<div class="col entry-content">

			<?php the_content(); ?>

		</div><!-- .entry-content -->
	</div>

</article><!-- #post-## -->
