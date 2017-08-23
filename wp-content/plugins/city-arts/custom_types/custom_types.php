<?php

function cptui_register_my_taxes_writer() {

  /**
   * Taxonomy: Writers.
   */

  $labels = array(
    "name" => __( "Writers", "understrap-child" ),
    "singular_name" => __( "Writer", "understrap-child" ),
  );

  $args = array(
    "label" => __( "Writers", "understrap-child" ),
    "labels" => $labels,
    "public" => true,
    "hierarchical" => false,
    "label" => "Writers",
    "show_ui" => true,
    "show_in_menu" => true,
    "show_in_nav_menus" => true,
    "query_var" => true,
    "rewrite" => array( 'slug' => 'writer', 'with_front' => true, ),
    "show_admin_column" => true,
    "show_in_rest" => false,
    "rest_base" => "",
    "show_in_quick_edit" => true,
  );
  register_taxonomy( "writer", array( "post" ), $args );
}


function cptui_register_my_cpts() {

  /**
   * Post Type: Contributors.
   */

  $labels = array(
    "name" => __( "Contributors", "understrap-child" ),
    "singular_name" => __( "Contributor", "understrap-child" ),
  );

  $args = array(
    "label" => __( "Contributors", "understrap-child" ),
    "labels" => $labels,
    "description" => "",
    "public" => true,
    "publicly_queryable" => true,
    "show_ui" => true,
    "show_in_rest" => false,
    "rest_base" => "",
    "has_archive" => "contributors",
    "show_in_menu" => true,
    "exclude_from_search" => false,
    "capability_type" => "post",
    "map_meta_cap" => true,
    "hierarchical" => false,
    "rewrite" => array( "slug" => "contributor", "with_front" => true ),
    "query_var" => true,
    "supports" => array( "title", "editor", "thumbnail", "excerpt", "custom-fields", "author", "post-formats" ),
    "taxonomies" => array( "category" ),
  );

  register_post_type( "contributor", $args );
}

function cptui_register_my_taxes() {
  /**
   * Taxonomy: Article Formats.
   */

  $labels = array(
    "name" => __( "Article Formats", "understrap-child" ),
    "singular_name" => __( "Article Format", "understrap-child" ),
  );

  $args = array(
    "label" => __( "Article Formats", "understrap-child" ),
    "labels" => $labels,
    "public" => false,
    "hierarchical" => false,
    "label" => "Article Formats",
    "show_ui" => true,
    "show_in_menu" => true,
    "show_in_nav_menus" => true,
    "query_var" => true,
    "rewrite" => array( 'slug' => 'article_format', 'with_front' => true, ),
    "show_admin_column" => false,
    "show_in_rest" => false,
    "rest_base" => "",
    "show_in_quick_edit" => false,
  );
  register_taxonomy( "article_format", array( "post" ), $args );
}

function register_acf_field_group() {
  if( function_exists('acf_add_local_field_group') ):

  acf_add_local_field_group(array (
    'key' => 'group_595dcb3b5899c',
    'title' => 'Post Relationships',
    'fields' => array (
      array (
        'key' => 'field_5960730a4bb42',
        'label' => 'Relationship',
        'name' => 'relationship',
        'type' => 'relationship',
        'instructions' => '',
        'required' => 0,
        'conditional_logic' => 0,
        'wrapper' => array (
          'width' => '',
          'class' => '',
          'id' => '',
        ),
        'post_type' => array (
        ),
        'taxonomy' => array (
        ),
        'filters' => array (
          0 => 'search',
          1 => 'post_type',
          2 => 'taxonomy',
        ),
        'elements' => '',
        'min' => '',
        'max' => '',
        'return_format' => 'object',
      ),
    ),
    'location' => array (
      array (
        array (
          'param' => 'post_type',
          'operator' => '==',
          'value' => 'post',
        ),
      ),
      array (
        array (
          'param' => 'post_type',
          'operator' => '==',
          'value' => 'contributor',
        ),
      ),
    ),
    'menu_order' => 0,
    'position' => 'normal',
    'style' => 'default',
    'label_placement' => 'top',
    'instruction_placement' => 'label',
    'hide_on_screen' => '',
    'active' => 1,
    'description' => '',
  ));

  acf_add_local_field_group(array (
    'key' => 'group_598b523f58656',
    'title' => 'Post Styles',
    'fields' => array (
      array (
        'key' => 'field_598b526fe8434',
        'label' => 'Post Style',
        'name' => 'post_style',
        'type' => 'taxonomy',
        'instructions' => '',
        'required' => 0,
        'conditional_logic' => 0,
        'wrapper' => array (
          'width' => '',
          'class' => '',
          'id' => '',
        ),
        'taxonomy' => 'article_format',
        'field_type' => 'radio',
        'allow_null' => 0,
        'add_term' => 0,
        'save_terms' => 1,
        'load_terms' => 1,
        'return_format' => 'id',
        'multiple' => 0,
      ),
    ),
    'location' => array (
      array (
        array (
          'param' => 'post_type',
          'operator' => '==',
          'value' => 'post',
        ),
      ),
    ),
    'menu_order' => 0,
    'position' => 'side',
    'style' => 'default',
    'label_placement' => 'top',
    'instruction_placement' => 'label',
    'hide_on_screen' => '',
    'active' => 1,
    'description' => '',
  ));
  endif;
}

function populate_article_format_tax(){

  $default_articles = array(
    array( 'slug' => 'article-current', 'name' => 'Article Current', 'description' => '', 'parent' => 0) ,
    array( 'slug' => 'article-enhanced', 'name' => 'Article Enhanced', 'description' => '', 'parent' => 0) ,
    array( 'slug' => 'article-past', 'name' => 'Article Past', 'description' => '', 'parent' => 0),
    array( 'slug' => 'article-simple', 'name' => 'Article Simple', 'description' => '', 'parent' => 0)
  );

  foreach($default_articles as $article) {

    if(term_exists( $article['slug'] ) == 0 ) {
      echo "creating new entry for " . $article['slug'] . "<br>" ;
      wp_insert_term( $article['name'], 'article_format', $article );
    }
    else {
      echo "already exists: " .  $article['slug'] . "<br>" ;
    }
  }
}


















