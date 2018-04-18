<?php
/**
 * The parent template for displaying older article posts.
 *
 * @package understrap
 */

$this_post = get_query_var('this_post');
?>
<!-- past -->
<div class="wrapper" id="single-wrapper">
  <main class="site-main" id="main">
  	<div class="container-fluid ad-container">
  		<?= ad_728xlandscape_shortcode(); ?>
  	</div>
  	<?php
    while ( have_posts() ) : the_post();

    if ( $this_post ) : //this means something other than the normal loop as loaded the page
        global $post;
        $post = $this_post;
        setup_postdata( $post );
      endif;
      get_template_part( 'loop-templates/content', 'article-past' );
    endwhile;
      wp_reset_postdata();
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
