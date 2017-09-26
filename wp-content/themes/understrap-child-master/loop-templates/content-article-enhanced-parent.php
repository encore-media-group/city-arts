<?php
/**
 * The parent template for displaying article enhanced posts.
 *
 * @package understrap
 */

?>

<div class="wrapper" id="single-enhanced-wrapper">
	<?php while ( have_posts() ) : the_post(); ?>

		<?php get_template_part( 'loop-templates/content', 'article-enhanced' ); ?>

		<?php // understrap_post_nav(); ?>

	<?php endwhile; // end of the loop.
  ?>

</div><!-- Wrapper end -->

<?php get_footer(); ?>
