<?php

/*
Widget: City Arts Calendar Widget
*/
class ca_calendar_widget extends WP_Widget {

  function __construct() {
    parent::__construct(
    'ca_calendar_widget',
    __('CA Calendar Widget', 'ca_widget_domain'),
    array( 'description' => __( 'Calendar', 'ca_widget_domain' ), )
    );
  }

  public function widget( $args, $instance ) {
    echo get_template_part( 'item-templates/item', 'calendar-sidebar' );
  }

  public function form( $instance ) {

  }

  public function update( $new_instance, $old_instance ) {
    $instance = array();
    return $instance;
  }
} // Class ca_calendar_widget ends here
