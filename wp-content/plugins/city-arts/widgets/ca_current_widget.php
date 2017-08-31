<?php

/*
Widget: City Arts Current Widget
*/
class ca_current_widget extends WP_Widget {

  function __construct() {
    parent::__construct(
    'ca_current_widget',
    __('CA Current Widget', 'ca_widget_domain'),
    array( 'description' => __( 'Show Current', 'ca_widget_domain' ), )
    );
  }

  public function widget( $args, $instance ) {
    echo get_template_part( 'item-templates/item', 'current' );
  }

  public function form( $instance ) {

  }

  public function update( $new_instance, $old_instance ) {
    $instance = array();
    return $instance;
  }
} // Class ca_current_widget ends here
