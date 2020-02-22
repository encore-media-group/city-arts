<?php

/*
Widget: City Arts Membership User Bar Widget
*/
class ca_membership_user_bar extends WP_Widget {

  function __construct() {
    parent::__construct(
    'ca_membership_user_bar',
    __('CA Membership User Bar Widget', 'ca_widget_domain'),
    array( 'description' => __( 'Show User Membership Bar', 'ca_widget_domain' ), )
    );
  }

  public function widget( $args, $instance ) {
    $membership_menu = '<div class="header-membership-wrapper sidebar p-4 mb-4">';
    //$membership_menu .= do_shortcode('[mepr-show if="loggedin"]<div>Welcome, [mepr-account-info field="full_name"]!</div> [mepr-account-link] [/mepr-show]');
    //$membership_menu .= do_shortcode('[mepr-show if="loggedout"]<a href="/new-membership">Join City Arts! Become a member today!</a> <div class="already-a-member"><i>(already a member? [mepr-login-link] here.)</i></div>[/mepr-show]');
    $membership_menu .=  '</div>';
    echo $membership_menu;
  }

  public function form( $instance ) {

  }

  public function update( $new_instance, $old_instance ) {
    $instance = array();
    return $instance;
  }
} // Class ca_membership_user_bar ends here
