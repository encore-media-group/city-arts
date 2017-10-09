<?php
/**
 * Homepage partial template.
 *
 * @package understrap
 */

$articles = [];
$used_ids = [];

$slot_a = new WP_Query( [
    'posts_per_page' => 1,
    'no_found_rows' => true,
    'post_status'=> 'publish',
    'orderby' => 'modified',
    'tax_query' => [
      'relation' => 'AND', [
          'taxonomy' => 'hp',
          'field'    => 'slug',
          'terms'    =>  'a_slot'
        ]
      ]
    ]);

while( $slot_a->have_posts() ) : $slot_a->the_post();
  $used_ids[] = $post->ID;
  $articles['slot_a'] = get_post();
endwhile;
wp_reset_query();

$slot_b = new WP_Query( [
    'posts_per_page' => 1,
    'no_found_rows' => true,
    'post_status'=> 'publish',
    'orderby' => 'modified',
    'tax_query' => [
      'relation' => 'AND', [
          'taxonomy' => 'hp',
          'field'    => 'slug',
          'terms'    =>  'b_slot'
        ]
      ]
    ]);

while( $slot_b->have_posts() ) : $slot_b->the_post();
  $used_ids[] = $post->ID;
  $articles['slot_b'] = get_post();
endwhile;
wp_reset_query();

$slot_c= new WP_Query( [
    'posts_per_page' => 3,
    'no_found_rows' => true,
    'post_status'=> 'publish',
    'orderby' => 'rand',
    'tax_query' => [
      'relation' => 'AND', [
          'taxonomy' => 'hp',
          'field'    => 'slug',
          'terms'    =>  'c_slot'
        ]
      ]
    ]);

while( $slot_c->have_posts() ) : $slot_c->the_post();
  $used_ids[] = $post->ID;
  $articles['slot_c'] = get_post();
endwhile;
wp_reset_postdata();

$see_it_this_week = new WP_Query([
      'posts_per_page' => 1,
      'orderby' => 'modified',
      'no_found_rows' => true,
      'post_status'=> 'publish',
      'post__not_in' => $used_ids,
      'tax_query' => [
            [
                'taxonomy' => 'category',
                'field'    => 'slug',
                'terms'    =>  ['see-it-this-week'],
            ],
        ],
      ]);

while( $see_it_this_week->have_posts() ) : $see_it_this_week->the_post();
  $used_ids[] = $post->ID;
  $articles['see_it_this_week'][] = get_post();
endwhile;
wp_reset_postdata();

$remaining_articles = new WP_Query([
  'posts_per_page' => 12,
  'orderby' => 'modified',
  'no_found_rows' => true,
  'post__not_in' => $used_ids,
  'post_status'=> 'publish',
  ]);
wp_reset_postdata();

?>


<div class="wrapper pb-0" id="page-wrapper">
  <main class="site-main" id="main">
    <div class="container mb-4" id="content" tabindex="-1">
      <div class="row pt-4">
        <div class="col-12 col-md-7 col-lg-8 px-lg-0" id="primary">
          <div class="row mx-0">
            <?php
            if( array_key_exists('slot_a', $articles )):
              global $post;
              $post = $articles['slot_a'];
              setup_postdata( $post );
              get_template_part( 'item-templates/item', '730x487-vertical' );
              wp_reset_postdata();
            endif;
            ?>
          </div>
          <div class="row mx-0 pt-4 justify-content-between item-730x487-width">
            <?php

            //show posts 1 & 2
            while( $remaining_articles->have_posts() && $remaining_articles->current_post < 1 ) : $remaining_articles->the_post();
            ?>
              <div class="col-12 col-lg-6 mb-4 mb-md-0">
                <?php get_template_part( 'item-templates/item', '160x107' ); ?>
              </div>
            <?php
            endwhile;
            $remaining_articles->rewind_posts();
            ?>
          </div>
        </div>
        <div class="col-12 col-md-5 col-lg-4" id="homepage-sidebar">
          <?php if ( is_active_sidebar( 'homepage-right-1' ) ) : ?>
            <div id="homepage-right-1" class="primary-sidebar widget-area" role="complementary">
              <?php dynamic_sidebar( 'homepage-right-1' ); ?>
            </div><!-- #homepage-right-1 -->
          <?php endif; ?>
        </div>
      </div>
    </div>
    <div class="container-fluid ad-container mb-4">
      <div class="row no-gutters">
        <div class="col-xl-12 py-2 text-center">
          <?= ad_728xlandscape_shortcode(); ?>
        </div>
      </div>
    </div>
    <div class="container mb-4 px-sm-0">
      <div class="row">
        <div class="col-12 col-lg-6">
          <div class="row">
          <?php
            //show posts 3 & 4
            $remaining_articles->current_post = 1;
            while( $remaining_articles->have_posts() && $remaining_articles->current_post < 3) : $remaining_articles->the_post();
            ?>
            <div class="col-sm-6">
              <?php
                set_query_var( 'show_excerpt', true );
                get_template_part( 'item-templates/item', '255x170' );
              ?>
             </div>
          <?php
           endwhile;
           $remaining_articles->rewind_posts();
          ?>
          </div>
          <div class="row">
            <div class="col mx-auto">
              <?php  get_template_part( 'item-templates/item', 'mailchimp' ); ?>
            </div>
          </div>
        </div>
        <div class="col-12 col-lg-6">
          <?php
          if( array_key_exists('slot_b', $articles )):
            global $post;
            $post = $articles['slot_b'];
            setup_postdata( $post );
            get_template_part( 'item-templates/item', '540x360-vertical' );
            wp_reset_postdata();
          endif;
          ?>
        </div>
      </div>
    </div>
    <div class="container mb-4">
      <div class="row d-flex justify-content-between">
        <?php
          //show posts 5, 6, 7, 8
            $remaining_articles->current_post = 3;
            while( $remaining_articles->have_posts() && $remaining_articles->current_post < 7 ) : $remaining_articles->the_post();
        ?>
              <div class="col-12 col-sm-6 pl-sm-0 col-lg-3 mb-4 mb-md-0">
                <?php
                  set_query_var( 'show_excerpt', false );
                  get_template_part( 'item-templates/item', '255x170' );
                ?>
              </div>
        <?php
            endwhile;
            $remaining_articles->rewind_posts();
        ?>
      </div>
    </div>
    <div class="container mb-4">
      <div class="row">
        <?php
          if( array_key_exists('slot_c', $articles )):
            $posts = $articles['slot_c'];
            foreach ( $posts as $local_post ):
              global $post;
              $post = $local_post;
              setup_postdata( $post );
              get_template_part( 'item-templates/item', '540x360-horizontal' );
              wp_reset_postdata();
            endforeach;
          endif;
        ?>
      </div>
    </div>
    <div class="container mb-4 ">
      <div class="row">
        <div class="col-9 col-sm-10 col-md-4 mx-auto mr-md-4 py-md-4 px-md-3" style="min-height: 100%;">
           <?php get_template_part( 'item-templates/item', 'current' ); ?>
        </div>
        <div class="col-12 col-md">
          <div class="row pt-4 see-it-this-week ">
            <?php
              if( array_key_exists('see-it-this-week', $articles )):
                global $post;
                $post = $articles['see-it-this-week'];
                setup_postdata( $post );
                set_query_var( 'alt_version', true );
                get_template_part( 'item-templates/item', '730x487-vertical' );
                wp_reset_postdata();
              endif;
            ?>
          </div>
        </div>
      </div>
    </div>
    <div class="container mb-4">
      <div class="row d-flex justify-content-between">
        <?php
          //show posts 7, 8, 9, 10
            $remaining_articles->current_post = 7;
            while( $remaining_articles->have_posts() && $remaining_articles->current_post < 11 ) : $remaining_articles->the_post();
            ?>

            <div class="col-12 col-sm-6 pl-sm-0 col-lg-3 mb-4 mb-md-0">
              <?php
                set_query_var( 'show_excerpt', false );
                get_template_part( 'item-templates/item', '255x170' );
              ?>
          </div>
        <?php
          endwhile;
          wp_reset_postdata();
        ?>
      </div>
    </div>
  </main>
</div>
