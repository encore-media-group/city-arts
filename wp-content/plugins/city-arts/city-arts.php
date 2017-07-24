<?php

/*
Plugin Name: City Arts
Plugin URI:
Description: Core and Custom functions for City Arts Web
Author: Aaron Starkey
Version: 1.0
Author URI: http://factorybeltproductions.org
*/

add_action('admin_menu', 'city_arts_website_menu');

function city_arts_website_menu(){
  add_menu_page('City Arts', 'City Arts ', 'manage_options', 'city-arts-plugin', 'city_arts_admin_page');

}

function city_arts_admin_page() {
  if (!current_user_can('manage_options'))  {
    wp_die( __('You do not have sufficient pilchards to access this page.')    );
  }

  echo '<div class="wrap">';
  echo '<h2>City Arts Website</h2>';
  /*
  // Check whether the button has been pressed AND also check the nonce
  if (isset($_POST['do_something_button']) && check_admin_referer('do_something_button_button_clicked')) {
   // the button has been pressed AND we've passed the security check
   //
  }

  echo '<form action="options-general.php?page=city-arts-plugin" method="post">';

  // this is a WordPress security feature - see: https://codex.wordpress.org/WordPress_Nonces
  */
  /*
  wp_nonce_field('do_something_button_button_clicked');
  echo '<input type="hidden" value="true" name="do_something_button" />';
  submit_button('Do Something');
  echo '</form>';
  */
  echo '</div>';
}


/**
 * Register custom article sidebar
 *
 */
function article_widgets_init() {

  register_sidebar( array(
    'name'          => 'Article sidebar',
    'id'            => 'article-left-1',
    'before_widget' => '<div>',
    'after_widget'  => '</div>',
    'before_title'  => '<h2 class="rounded">',
    'after_title'   => '</h2>',
  ) );

}
add_action( 'widgets_init', 'article_widgets_init' );



