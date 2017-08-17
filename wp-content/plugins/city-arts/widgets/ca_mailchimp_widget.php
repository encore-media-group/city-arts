<?php

/*
Widget: City Arts Mailchimp Widget
*/
class ca_mailchimp_widget extends WP_Widget {

  function __construct() {
    parent::__construct(
    'ca_mailchimp_widget',     // Base ID of your widget
    __('CA MailChimp Widget', 'ca_widget_domain'),     // Widget name will appear in UI
    array( 'description' => __( 'Display mailchimp signup.', 'ca_widget_domain' ), )
    );
  }

  // Creating widget front-end
  public function widget( $args, $instance ) {
    get_template_part( 'item-templates/item', 'mailchimp' );
  }

  // Widget Backend
  public function form( $instance ) {
      echo "<p></p>";
  }

  // Updating widget replacing old instances with new
  public function update( $new_instance, $old_instance ) {
    $instance = array();
    return $instance;
  }
}

