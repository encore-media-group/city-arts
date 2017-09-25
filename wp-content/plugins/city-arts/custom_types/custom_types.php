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
    'key' => 'group_59b19f8ed138b',
    'title' => 'Cover Image Group',
    'fields' => array (
      array (
        'key' => 'field_59b19f9944886',
        'label' => 'Cover Image',
        'name' => 'cover_image',
        'type' => 'image',
        'instructions' => '',
        'required' => 0,
        'conditional_logic' => 0,
        'wrapper' => array (
          'width' => '',
          'class' => '',
          'id' => '',
        ),
        'return_format' => 'array',
        'preview_size' => 'full',
        'library' => 'all',
        'min_width' => '',
        'min_height' => '',
        'min_size' => '',
        'max_width' => '',
        'max_height' => '',
        'max_size' => '',
        'mime_types' => '',
      ),
      array (
        'key' => 'field_59b1a13222435',
        'label' => 'Issue Publish Date',
        'name' => 'issue_publish_date',
        'type' => 'date_picker',
        'instructions' => '',
        'required' => 0,
        'conditional_logic' => 0,
        'wrapper' => array (
          'width' => '',
          'class' => '',
          'id' => '',
        ),
        'display_format' => 'm/Y',
        'return_format' => 'm/Y',
        'first_day' => 1,
      ),
    ),
    'location' => array (
      array (
        array (
          'param' => 'post_category',
          'operator' => '==',
          'value' => 'category:cover-story',
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

acf_add_local_field_group(array (
  'key' => 'group_59c8aa677f994',
  'title' => 'Writer Metadata',
  'fields' => array (
    array (
      'key' => 'field_59c8aa83fd64c',
      'label' => 'writer image',
      'name' => 'writer_image',
      'type' => 'image',
      'instructions' => '',
      'required' => 0,
      'conditional_logic' => 0,
      'wrapper' => array (
        'width' => '',
        'class' => '',
        'id' => '',
      ),
      'return_format' => 'array',
      'preview_size' => 'thumbnail',
      'library' => 'all',
      'min_width' => '',
      'min_height' => '',
      'min_size' => '',
      'max_width' => '',
      'max_height' => '',
      'max_size' => '',
      'mime_types' => '',
    ),
    array (
      'key' => 'field_59c926f04287a',
      'label' => 'Twitter Profile Slug',
      'name' => 'writer_twitter_slug',
      'type' => 'text',
      'instructions' => '',
      'required' => 0,
      'conditional_logic' => 0,
      'wrapper' => array (
        'width' => '',
        'class' => '',
        'id' => '',
      ),
      'default_value' => '',
      'placeholder' => '',
      'prepend' => '',
      'append' => '',
      'maxlength' => '',
    ),
    array (
      'key' => 'field_59c927234287b',
      'label' => 'Facebook Profile Slug',
      'name' => 'writer_facebook_slug',
      'type' => 'text',
      'instructions' => '',
      'required' => 0,
      'conditional_logic' => 0,
      'wrapper' => array (
        'width' => '',
        'class' => '',
        'id' => '',
      ),
      'default_value' => '',
      'placeholder' => '',
      'prepend' => '',
      'append' => '',
      'maxlength' => '',
    ),
  ),
  'location' => array (
    array (
      array (
        'param' => 'taxonomy',
        'operator' => '==',
        'value' => 'writer',
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


















