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

//add custom city arts shortcodes
add_shortcode( 'insert_cover_story', 'insert_cover_story_shortcode' );
add_shortcode( 'insert_300x250_ad', 'ad_300x250_shortcode' );
add_shortcode( 'insert_728xlandscape_ad', 'ad_728xlandscape_shortcode' );
add_shortcode( 'insert_secondary_feature_image', 'secondary_feature_image_shortcode' );



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

function insert_cover_story_shortcode() {
  $id = get_the_ID();
  $cover = get_single_issue_cover( $id );
  $a_tag = '<a href="%1$s">%2$s</a>';
  $list_html = '<h4 class="mb-0">%1$s</h4><div class="contributors pb-1">%2$s</div>';
  $article_list_html = '';

  $articles = get_feature_issue_articles( [ $cover['slug'] ], [ $id ], 3 );

  while( $articles->have_posts() ) : $articles->the_post();

    $ahref = sprintf($a_tag, '/' . get_post_field( 'post_name', get_post() ), get_post_field( 'post_title', get_post() ) ) ;
    $contributor = get_contributors_by_id( get_post_field( 'ID', get_post() ) );

    $article_list_html .= sprintf( $list_html, $ahref, $contributor );

  endwhile;
  wp_reset_postdata();


  $html = '<div class="cover-story-image-wrapper ml-md-4">';
  $html .= sprintf( $a_tag, '/isssue/' . $cover['slug'], $cover['cover_src'] );
  $html .= '<div class="col item-content-container p-2">';
  $html .= '<div class="issue-title">Also from ' . sprintf( $a_tag, '/issue/' . $cover['slug'], $cover['name'] ) . '</div>';
  $html .= sprintf( '<div class="item-160x107">%1$s</div>', $article_list_html );
  $html .= '</div>';
  $html .= '</div>';

  return $html;
}


function secondary_feature_image_shortcode() {
    $id = get_the_ID();
    $image = get_field('secondary_feature_image', $id);
    $size = 'ca-540x360';
    $cover_image = [];
    $output = '';
    $wrapper = '<div class="item-540x360-in-post pl-lg-4 pb-lg-4 pt-2 float-lg-right mx-auto">%1$s<div class="caption p-2">%2$s</div></div>';

    if( $image ) {
      $caption = $image['caption']; // image caption
      $description = $image['description']; // image description
      $cover_image['src'] = wp_get_attachment_image_url( $image['id'], $size );
      $cover_image['srcset'] = wp_get_attachment_image_srcset( $image['id'], $size );
      $cover_image['sizes'] = '(max-width: 46em) 100vw, 540px';

      $output = sprintf( $wrapper, build_img_tag( $cover_image ) , $caption . " " . $description );
    }
  return $output;
}

function get_single_issue_cover( $post_id ) {
  $cats = [];
  $cover_image = [];

  $post_cats = wp_get_post_categories( $post_id );
  $issue_parent_cat_id = get_cached_cat_id_by_slug( 'issue' );

  foreach ($post_cats as $cat) :
    if( cat_is_ancestor_of( $issue_parent_cat_id, $cat ) ) :
      $c = get_category( $cat );
      $cats = ['slug'=> ($c->slug), 'name'=> ($c->name) ];
      break 1;
    endif;
  endforeach;

  $issue_name = $cats['name'];
  $cover_story = get_cover_stories( $cats['slug'] );

  while( $cover_story->have_posts() ) : $cover_story->the_post();
    $image = get_field('cover_image');
    $size = 'ca-350x454';

    if( $image ) {
      $cover_image['src'] = wp_get_attachment_image_url( $image['id'], $size );
      $cover_image['srcset'] = wp_get_attachment_image_srcset( $image['id'], $size );
      $cover_image['class'] = 'cover-story-image';
      $cover_image['sizes'] = '(max-width: 46em) 100vw, 231px';
      $cover_image['alt'] = $issue_name;
    }

  endwhile;
  wp_reset_postdata();

  return ['name'=> $issue_name,'slug'=> $cats['slug'], 'cover_src' => build_img_tag($cover_image) ];

}


function ad_300x250_shortcode() {
  $html = '<div class="ad_300x250_sc_container float-md-right ml-md-4">' . ad_300x250_core() . "</div>";
  return $html;
}

function ad_300x250_core() {
  $html = '<div class="ad-300x250 mx-auto my-auto">';
  $html .= '<!-- /21626118154/cityarts_mediumrectangle -->';
 // $html .= '<div id=\'div-gpt-ad-1503686755285-0\' style=\'height:250px; width:300px;\'>';
  $html .= '<div id=\'div-gpt-ad-1506555798490-1\' style=\'height:250px; width:300px;\'>';
  $html .= '<script>';
  $html .= 'googletag.cmd.push(function() { googletag.display(\'div-gpt-ad-1506555798490-1\'); });';
  $html .= '</script></div></div>';
  return $html;
}

function ad_728xlandscape_shortcode() {
  $html = '<div class="row no-gutters"><div class="col pb-3 text-center"><span style="font-size:.5em;">ADVERTISEMENT</span>';
  $html .= '<div class=" mx-auto " style="max-width:728px;height:90px;">%1$s</div></div></div>';
  return sprintf($html, ad_728xlandscape_shortcode_core() );
}

function ad_728xlandscape_shortcode_core() {
//style=\'height:90px; width:728px;\'
  $html = '<!-- /21626118154/cityarts_leaderboards -->';
  $html .= '<div id=\'div-gpt-ad-1506555798490-0\' >';
  $html .= '<script>googletag.cmd.push(function() { googletag.display(\'div-gpt-ad-1506555798490-0\'); });</script>';
  $html .= '</div>';

  return $html;
}

function build_img_tag( $image ) {

  $output = '';

  if( $image ) {

    $srcset = ( isset( $image['srcset'] )) ? $image['srcset'] : ' ';
    $class = ( isset( $image['class'] )) ? $image['class'] : ' ';
    $sizes = ( isset( $image['sizes'] )) ? $image['sizes'] : '';
    $style = ( isset( $image['style'] )) ? $image['style'] : ' max-width: 100%; height:auto; ';
    $alt = ( isset( $image['alt'] )) ? $image['alt'] : '';

    $img_tag = '<img
      src="%1$s"
      srcset="%2$s"
      class="img-fluid %3$s"
      sizes="%4$s"
      style="%5$s"
      alt="%6$s">';

    $output = sprintf(
      $img_tag,
      esc_url( $image['src'] ),
      esc_attr( $srcset ),
      esc_attr( $class ),
      esc_attr( $sizes ),
      esc_attr( $style ),
      esc_attr( $alt )
    );
  }

 return $output;
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
  add_image_size( 'ca-2000x1333', 2000, 1333, true );
  add_image_size( 'ca-1140-760', 1140, 760, true );
  add_image_size( 'ca-730-487', 730, 487, true);
  add_image_size( 'ca-730xauto', 730);
  add_image_size( 'ca-540x360', 540, 360, true );
  add_image_size( 'ca-350x454', 350, 454, true );
  add_image_size( 'ca-175x227', 175, 227, true );
  add_image_size( 'ca-320x213', 320, 213, true );
  add_image_size( 'ca-255x170', 255, 170, true );
  add_image_size( 'ca-160x107', 160, 107, true );
}


// Register the useful image sizes for use in Add Media modal
function wpshout_custom_sizes( $sizes ) {
    return array_merge( $sizes, array(
        'ca-2000x1333' => __( 'ca-2000x1333' ),
        'ca-1140-760' => __( 'ca-1140-760' ),
        'ca-730-487' => __( 'ca-730-487' ),
        'ca-730xauto' => __( 'ca-730xauto' ),
        'ca-540x360' => __( 'ca-540x360' ),
        'ca-350x454' => __( 'ca-350x454' ),
        'ca-175x227' => __( 'ca-175x227' ),
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
function understrap_posted_on( $date_only = false ) {
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
  if ( !$date_only ) {
    $byline = get_contributors();
    echo '<span class="byline"> ' . $byline . '</span> ';
  }

  echo '<span class="posted-on">' . $posted_on . '</span>'; // WPCS: XSS OK.
}
endif;

function get_contributors_by_id( $post_id) {
  $html = '';
  $writers = get_the_terms( $post_id, 'writer');

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

if ( ! function_exists( 'get_contributors' ) ) :
  function get_contributors(){
    return get_contributors_by_id( get_the_ID() );
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

function get_cover_stories( $issue_slugs = [] ) {
  $posts_per_page = ( sizeof( $issue_slugs ) > 0 ) ? sizeof( $issue_slugs ) : 1;

  return new WP_Query( [
    'posts_per_page' => $posts_per_page,
    'no_found_rows' => true,
    'post_status'=> 'publish',
    'ignore_sticky_posts' => true,
    'tax_query' => [
      'relation' => 'AND', [
          'taxonomy' => 'category',
          'field'    => 'slug',
          'terms'    =>  $issue_slugs
        ],
        [
          'taxonomy' => 'category',
          'field'    => 'slug',
          'terms'    =>  array( 'cover-story' )
        ]
      ]
    ]
  );
}

function get_feature_issue_articles( $issue_slug = [], $ignore_posts = [], $posts_per_page = 1 ) {

  return new WP_Query( [
    'posts_per_page' => $posts_per_page,
    'no_found_rows' => true,
    'post__not_in' => $ignore_posts,
    'post_status'=> 'publish',
    'ignore_sticky_posts' => true,
    'tax_query' => [
      'relation' => 'AND', [
          'taxonomy' => 'category',
          'field'    => 'slug',
          'terms'    =>  'july-2017' //$issue_slug
        ],
        [
          'taxonomy' => 'category',
          'field'    => 'slug',
          'terms'    =>  array( 'feature' )
        ]
      ]
    ]
  );
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

function set_first_letter_of_post( $post ) {
  $article_body = apply_filters('the_content', $post->post_content);

  $first_word =  wp_trim_words( $article_body, 1, '' );
  $first_letter = substr($first_word, 0, 1);

  $new_first_word = substr_replace($first_word, '<span class="the-first-letter">' . $first_letter . '</span>', 0, 1);

  $pos = strpos($article_body, $first_word);

  if ($pos !== false) {
       $article_body = substr_replace(
        $article_body,
        $new_first_word,
        $pos,
        strlen($first_word)
      );
  }
return  $article_body;
}


function get_category_label() {

  $categories = get_the_category();

  $url = "/";
  $name = "City Arts";

  $cover_story_cat_id = get_cached_cat_id_by_slug('cover-story');
  $issue_cat_id = get_cached_cat_id_by_slug('issue');

  if ( ! empty($categories) ) :
    $disciplines = get_disciplines();
    $small_categories = $categories;

    foreach ($categories as $key=>$category) {
      $match_index = array_search( $category->slug, $disciplines);

      if( $match_index !== false ) {
        unset($small_categories[$key]); //remove any categories for this post that match the core disciplines
      }
      if ( $category->term_id === $cover_story_cat_id) {
        unset($small_categories[$key]);
      }
      if ( $category->parent === $issue_cat_id) {
        unset($small_categories[$key]);
      }

    }

    $small_categories = array_values($small_categories);

    if( sizeof($small_categories) > 0
      ? $category_output = $small_categories[0]
      : $category_output = $categories[0]
      );

    $url =  esc_url( get_category_link( $category_output->term_id ) );
    $name = esc_html(  $category_output->name );

  endif;

return [ 'url' => $url, 'name' => $name ];
}





