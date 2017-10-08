<?php
/**
 * Related Articles Template.
 *
 * @package understrap
 */

  $post_id = isset( $post_id ) ? $post_id : null ;

?>
  <div class="row">
    <div class="col-12">
      <h3 class="sidelines sidebar py-4">RELATED ARTICLES</h3>
      <div class="row">
      <?php
      $related_articles = get_related_articles( $post_id );

      $count = 0;
      while( $related_articles->have_posts() ) : $related_articles->the_post();
        $count++;
      ?>
        <div class="col-12 col-lg-6 mb-4">
          <?php get_template_part( 'item-templates/item', '255x170-horizontal' ); ?>
        </div>

      <?php endwhile;
        wp_reset_postdata();
        unset($count);
      ?>
      </div>
    </div>
  </div>

