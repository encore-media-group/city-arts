<?php
/**
 * The parent template for displaying current article posts.
 *
 * @package understrap
 */


?>

<div class="wrapper" id="single-wrapper">
  <main class="site-main" id="main">
  	<div class="container-fluid ad-container">
  		<?= ad_728xlandscape_shortcode(); ?>
  	</div>
  	<?php while ( have_posts() ) : the_post(); ?>

  		<?php get_template_part( 'loop-templates/content', 'article-current' ); ?>

  	<?php endwhile; // end of the loop.
    ?>

  	<div class="container-fluid ad-container">
  		<?= ad_728xlandscape_bottom_shortcode(); ?>
  	</div>

  	<!-- RELATED ARTICLES -->
    <div class="container mb-4">
      <?php set_query_var ('post_id', $post->ID ); ?>
      <?php get_template_part( 'item-templates/item', 'related-articles' ); ?>
  	</div><!-- RELATED ARTICLES END -->
  </main>
</div><!-- Wrapper end -->

<?php get_footer(); ?>
