<?php

function create_private_homepage_tax() {
    register_taxonomy(
        'hp',
        'post',
        array(
            'label' => __( 'Placement' ),
            'public' => false,
            'rewrite' => false,
            'hierarchical' => true,
            'query_var' => false,
            'show_ui' => true
        )
    );
}

function cptui_register_my_taxes_contributor() {

  /**
   * Taxonomy: Contributor.
   */

  $labels = array(
    "name" => __( "Contributors", "understrap-child" ),
    "singular_name" => __( "Contributor", "understrap-child" ),
  );

  $args = array(
    "label" => __( "Contributors", "understrap-child" ),
    "labels" => $labels,
    "public" => true,
    "hierarchical" => false,
    "label" => "Contributors",
    "show_ui" => true,
    "show_in_menu" => true,
    "show_in_nav_menus" => true,
    "query_var" => true,
    "rewrite" => array( 'slug' => 'contributor', 'with_front' => true, ),
    "show_admin_column" => true,
    "show_in_rest" => false,
    "rest_base" => "",
    "show_in_quick_edit" => true,
  );
  register_taxonomy( "contributor", array( "post" ), $args );
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
          'display_format' => 'm/d/Y',
          'return_format' => 'm/d/Y',
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
      'title' => 'Contributor Metadata',
      'fields' => array (
      array (
        'key' => 'field_59c8aa83fd64c',
        'label' => 'contributor image',
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
        array (
          array (
            'param' => 'taxonomy',
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
      'key' => 'group_59cbf002eb226',
      'title' => 'Article Enchanced',
      'fields' => array (
        array (
          'key' => 'field_59cbf02b11265',
          'label' => 'Secondary Feature Image',
          'name' => 'secondary_feature_image',
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
          'preview_size' => 'ca-540x360',
          'library' => 'all',
          'min_width' => 540,
          'min_height' => '',
          'min_size' => '',
          'max_width' => '',
          'max_height' => '',
          'max_size' => '',
          'mime_types' => '',
        ),
      ),
      'location' => array (
        array (
          array (
            'param' => 'post_taxonomy',
            'operator' => '==',
            'value' => 'article_format:article-enhanced',
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
      'key' => 'group_59f2c9becf485',
      'title' => 'Issue Settings Field Group',
      'fields' => array (
        array (
          'key' => 'field_59f2c9d485b68',
          'label' => 'Active Issue',
          'name' => 'active_issue',
          'type' => 'taxonomy',
          'value' => NULL,
          'instructions' => '',
          'required' => 0,
          'conditional_logic' => 0,
          'wrapper' => array (
            'width' => '',
            'class' => '',
            'id' => '',
          ),
          'taxonomy' => 'category',
          'field_type' => 'select',
          'allow_null' => 1,
          'add_term' => 0,
          'save_terms' => 0,
          'load_terms' => 0,
          'return_format' => 'object',
          'multiple' => 0,
        ),
      ),
      'location' => array (
        array (
          array (
            'param' => 'options_page',
            'operator' => '==',
            'value' => 'current-issue-settings',
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
      'key' => 'group_59f7b52d9460b',
      'title' => 'Top Article Settings',
      'fields' => array (
        array (
          'key' => 'field_59f7b53c3e880',
          'label' => 'Featured Articles',
          'name' => 'featured_articles',
          'type' => 'relationship',
          'value' => NULL,
          'instructions' => '',
          'required' => 0,
          'conditional_logic' => 0,
          'wrapper' => array (
            'width' => '',
            'class' => '',
            'id' => '',
          ),
          'post_type' => array (
            0 => 'post',
          ),
          'taxonomy' => array (
          ),
          'filters' => array (
            0 => 'search',
          ),
          'elements' => '',
          'min' => '',
          'max' => '',
          'return_format' => 'id',
        ),
      ),
      'location' => array (
        array (
          array (
            'param' => 'options_page',
            'operator' => '==',
            'value' => 'current-issue-settings',
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

    acf_add_local_field_group(array(
      'key' => 'group_5ab27ed1315f7',
      'title' => 'Calendar Settings',
      'fields' => array(
        array(
          'key' => 'field_5ab27eda3539a',
          'label' => 'Show in Calendar',
          'name' => 'show_in_calendar',
          'type' => 'select',
          'instructions' => '',
          'required' => 0,
          'conditional_logic' => 0,
          'wrapper' => array(
            'width' => '',
            'class' => '',
            'id' => '',
          ),
          'choices' => array(
            'no' => 'No',
            'yes' => 'Yes',
          ),
          'default_value' => array(
            0 => 'no',
          ),
          'allow_null' => 0,
          'multiple' => 0,
          'ui' => 0,
          'ajax' => 0,
          'return_format' => 'value',
          'placeholder' => '',
        ),
      ),
      'location' => array(
        array(
          array(
            'param' => 'post_category',
            'operator' => '==',
            'value' => 'category:issue',
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
    array( 'slug' => 'article-past', 'name' => 'Article Past', 'description' => '', 'parent' => 0)
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

function populate_default_taxonomies(){

  $default_taxs = array(
    array( 'slug' => 'article-current', 'tax' => 'article_format', 'name' => 'Article Current', 'description' => '', 'parent' => 0) ,
    array( 'slug' => 'article-enhanced', 'tax' => 'article_format', 'name' => 'Article Enhanced', 'description' => '', 'parent' => 0),
    array( 'slug' => 'article-past', 'tax' => 'article_format', 'name' => 'Article Past', 'description' => '', 'parent' => 0),
    array( 'slug' => 'a_slot', 'tax' => 'hp', 'name' => 'Slot A', 'description' => '', 'parent' => 0),
    array( 'slug' => 'b_slot', 'tax' => 'hp', 'name' => 'Slot B', 'description' => '', 'parent' => 0),
    array( 'slug' => 'c_slot', 'tax' => 'hp', 'name' => 'Slot C', 'description' => '', 'parent' => 0),


  );

  foreach($default_taxs as $tax) {

    if(term_exists( $tax['slug'] ) == 0 ) {
      echo "creating new entry for " . $tax['slug'] . "<br>" ;
      wp_insert_term( $tax['name'], $tax['tax'], $tax );
    }
    else {
      echo "already exists: " .  $tax['slug'] . "<br>" ;
    }
  }
}

















