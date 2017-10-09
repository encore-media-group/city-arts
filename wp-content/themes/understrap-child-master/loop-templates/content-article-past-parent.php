<?php
/**
 * The parent template for displaying older article posts.
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
  		<?php get_template_part( 'loop-templates/content', 'article-past' ); ?>
  	<?php endwhile;?>
  	<div class="container-fluid ad-container">
  		<?= ad_728xlandscape_bottom_shortcode(); ?>
  	</div>

    <div class="container mb-4">
      <?php set_query_var ('post_id', $post->ID ); ?>
      <?php get_template_part( 'item-templates/item', 'related-articles' ); ?>
  	</div>
  </main>
</div><!-- Wrapper end -->

<?php get_footer(); ?>
