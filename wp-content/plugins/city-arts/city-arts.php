<?php

/*
Plugin Name: City Arts
Plugin URI:
Description: Core and Custom functions for City Arts Web
Author: Aaron Starkey
Version: 1.0
Author URI: http://factorybeltproductions.org
*/

/* include required files */
include( plugin_dir_path( __FILE__ ) . 'widgets/ca_top_articles_widget.php');
include( plugin_dir_path( __FILE__ ) . 'widgets/ca_300_x_250_ad_widget.php');
include( plugin_dir_path( __FILE__ ) . 'widgets/ca_mailchimp_widget.php');
include( plugin_dir_path( __FILE__ ) . 'widgets/ca_current_widget.php');
include( plugin_dir_path( __FILE__ ) . 'custom_types/custom_types.php');


/* register this in the admin menu */
add_action( 'admin_menu', 'city_arts_website_menu' );

/* register custom types */
add_action( 'init', 'cptui_register_my_taxes' );
add_action( 'init', 'cptui_register_my_taxes_writer' );
add_action( 'widgets_init', 'register_acf_field_group');
add_action( 'widgets_init', 'ca_load_widgets' );
add_action( 'widgets_init', 'ca_register_sidebars' );

/* enqueue any scripts or css required by the city arts plugin */
add_action( 'wp_enqueue_scripts', 'ca_enqueue_scripts_and_styles' );

/* register eveything else */
add_action( 'after_setup_theme', 'ca_add_image_sizes' );

add_filter( 'image_size_names_choose', 'wpshout_custom_sizes' );

add_action( 'pre_get_posts', 'ca_alter_query' );

add_action( 'pre_get_posts', 'wpsites_query' );

//load child template for issue instead of the default archive page
add_filter( 'template_include', 'load_issue_template', 99 );



function ca_enqueue_scripts_and_styles() {
  wp_enqueue_style( 'better-simple-slideshow-js', '//cdn-images.mailchimp.com/embedcode/horizontal-slim-10_7.css');
}

function city_arts_website_menu(){
  add_menu_page('City Arts', 'City Arts ', 'manage_options', 'city-arts-plugin', 'city_arts_admin_page');
}

function city_arts_admin_page() {
  if (!current_user_can('manage_options'))  {
    wp_die( __('You do not have sufficient pilchards to access this page.')    );
  }

  echo '<div class="wrap">';
  echo '<h2>City Arts Website</h2>';
  echo 'Nothing to see here. This plugin is designed to provide customization to the website without specific theme dependencies.';

  // Check whether the button has been pressed AND also check the nonce
  if (isset($_POST['set_tax_button']) && check_admin_referer('set_tax_button_button_clicked')) {
   // the button has been pressed AND we've passed the security check
   populate_article_format_tax();
  }

  echo '<form action="options-general.php?page=city-arts-plugin" method="post">';

  wp_nonce_field('set_tax_button_button_clicked');
  echo '<input type="hidden" value="true" name="set_tax_button" />';
  submit_button('Set Default Taxonomy');
  echo '</form>';

  echo '</div>';
}

function get_disciplines() {
  return array(
    'music',
    'visual-art',
    'art',
    'film',
    'dance',
    'comedy',
    'books-talks',
    'food-design',
    'food-style'
    );
}

// special function to store oft used category ids from slugs
function get_cached_cat_id_by_slug( $slug ) {
  $cache_key = $slug . "_id";
  $cat_id = wp_cache_get( $cache_key );

  if ( false === $cat_id ) {
    $cat_obj = get_category_by_slug( $slug );
    if( $cat_obj ) {
      $cat_id = $cat_obj->term_id;
      wp_cache_set( $cache_key, $cat_id );
    }
  }
  return $cat_id;
}

function ca_register_sidebars() {
  /*  Register custom article sidebar*/
  register_sidebar( array(
    'name'          => 'Article sidebar',
    'id'            => 'article-right-1',
    'before_widget' => '',
    'after_widget'  => '',
    'before_title'  => '',
    'after_title'   => '',
  ) );

  /* Register homepage widget sidebar */
  register_sidebar( array(
    'name'          => 'Homepage sidebar',
    'id'            => 'homepage-right-1',
    'before_widget' => '',
    'after_widget'  => '',
    'before_title'  => '',
    'after_title'   => '',
  ) );
}

// Register and load top articles widget
function ca_load_widgets() {
  register_widget( 'ca_top_articles_widget' );
  register_widget( 'ca_300_x_250_ad_widget' );
  register_widget( 'ca_mailchimp_widget' );
  register_widget( 'ca_current_widget' );
}

function ca_add_image_sizes() {
  add_image_size( 'ca-1140-760', 1140, 760, true );
  add_image_size( 'ca-730-487', 730, 487, true);
  add_image_size( 'ca-730xauto', 730);
  add_image_size( 'ca-540x360', 540, 360, true );
  add_image_size( 'ca-350x454', 350, 454, true );
  add_image_size( 'ca-320x213', 320, 213, true );
  add_image_size( 'ca-255x170', 255, 170, true );
  add_image_size( 'ca-160x107', 160, 107, true );
}


// Register the useful image sizes for use in Add Media modal
function wpshout_custom_sizes( $sizes ) {
    return array_merge( $sizes, array(
        'ca-1140-760' => __( 'ca-1140-760' ),
        'ca-730-487' => __( 'ca-730-487' ),
        'ca-730xauto' => __( 'ca-730xauto' ),
        'ca-540x360' => __( 'ca-540x360' ),
        'ca-350x454' => __( 'ca-350x454' ),
        'ca-320x213' => __( 'ca-320x213' ),
        'ca-255x170' => __( 'ca-255x170' ),
        'ca-160x107' => __( 'ca-160x107' ),

    ) );
}


/* This changes the default ordered of queries to be by modified date */
function ca_alter_query( $query )
{
    if ( $query->is_main_query() && ( $query->is_home() || $query->is_search() || $query->is_archive() )  )
    {
        $query->set( 'orderby', 'modified' );
        $query->set( 'order', 'asc' );
    }
}


/* override function in parent */
if ( ! function_exists( 'understrap_posted_on' ) ) :
/**
 * Prints HTML with meta information for the current post-date/time and author.
 */
function understrap_posted_on() {
  $time_string = '<time class="entry-date published updated" datetime="%1$s">%2$s</time>';
  /* let's only show the published date.
  if ( get_the_time( 'U' ) !== get_the_modified_time( 'U' ) ) {
    $time_string = '<time class="entry-date published" datetime="%1$s">%2$s</time><time class="updated" datetime="%3$s">%4$s</time>';
  }
  */
  $time_string = sprintf( $time_string,
    esc_attr( get_the_date( 'c' ) ),
    esc_html( get_the_date('F j, Y') ), // August 18, 2017
    esc_attr( get_the_modified_date('c') ),
    esc_html( get_the_modified_date('l, F jS, Y') )
  );
  $posted_on = sprintf(
    esc_html_x( '%s', 'post date', 'understrap' ),
    '<a href="' . esc_url( get_permalink() ) . '" rel="bookmark">' . $time_string . '</a>'
  );
  $byline = get_contributors();
  echo '<span class="byline"> ' . $byline . '</span> <span class="posted-on">' . $posted_on . '</span>'; // WPCS: XSS OK.
}
endif;

if ( ! function_exists( 'get_contributors' ) ) :
  function get_contributors(){
    $writers = get_the_terms(get_the_ID(), 'writer');
      $html = '';

    if( $writers ) {
      $count = 0;
      foreach( $writers as $writer ) {
        if($count == 0) {
          $html .= sprintf(esc_html_x( '%s', 'post author', 'understrap' ),
          '<span class="by">by </span><span class="author vcard"><a class="url fn n" href="' . esc_url( get_category_link( $writer->term_id ) ) . '">' . esc_html( $writer->name ) . '</a></span>'
        );
        } else{
          $html .= sprintf(esc_html_x( ' and %s', 'post author', 'understrap' ),
          '<span class="author vcard"><a class="url fn n" href="' . esc_url( get_category_link( $writer->term_id ) ) . '">' . esc_html( $writer->name ) . '</a></span>'
          );

        }
        $count++;
      }
    }

    return $html;
  }
endif;

/* increase the number of posts on the archive pages */
function wpsites_query( $query ) {
if ( $query->is_archive() && $query->is_main_query() && !is_admin() ) {
 //       $query->set( 'posts_per_page', 12 );
    }
}



function load_issue_template( $template ) {
  if (is_category() && !is_feed()) {
    if (is_category( get_cached_cat_id_by_slug('issue') ) || cat_is_ancestor_of( get_cached_cat_id_by_slug('issue') , get_query_var('cat'))) {
      $new_template = locate_template( array( 'archive-issue.php' ) );
      if ( '' != $new_template ) {
        return $new_template;
      }
    }
  }

 return $template;
}



function issue_display_posts( $the_posts, $args = array() ) {
  if( is_array( $the_posts ) ) :
    foreach ($the_posts as $the_post) :
      issue_display_post( $the_post, $args );
    endforeach;
  endif;
}

function issue_display_post( $the_post, $args = array() ) {
  if( !empty( $the_post ) ) :
    global $post;
    $post = $the_post['the_post'];
    $the_slug = $the_post['the_slug'];
    setup_postdata( $post );

    echo isset( $args['before'] ) ? $args['before'] : '' ;

    if( isset( $args['template'] ) ) :

     if( isset( $args['query_vars'] ) ) :
      foreach( $args['query_vars'] as $query_var ) :
        set_query_var( $query_var['var'], $query_var['val'] );
      endforeach;
     endif;

      set_query_var ('issue_slug', $the_slug );
      get_template_part( $args['template']['path'], $args['template']['file'] );

    endif;

    echo isset( $args['after'] ) ? $args['after'] : '' ;

    wp_reset_postdata();
  endif;
}

function map_post_obj_and_slug($the_post, $the_slug) {
  return [
      'the_post' => $the_post,
      'the_slug' => $the_slug
  ];
}

function compare_by_post_date($a, $b) {
  if ($a["the_post"]->post_date == $b["the_post"]->post_date) {
      return 0;
  }
  return ($a["the_post"]->post_date < $b["the_post"]->post_date) ? -1 : 1;
}






