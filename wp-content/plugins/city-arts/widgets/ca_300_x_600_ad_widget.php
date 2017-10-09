<?php

/*
Widget: City Arts 300 x 600 Ad Widget
*/
class ca_300_x_600_ad_widget extends WP_Widget {

  function __construct() {
    parent::__construct(
    'ca_300_x_600_ad_widget',     // Base ID of your widget
    __('CA 300 x 600 Ad Widget', 'ca_widget_domain'),     // Widget name will appear in UI
    array( 'description' => __( 'Show a 300x600 ad.', 'ca_widget_domain' ), )     // Widget description
    );
  }

  // Creating widget front-end
  public function widget( $args, $instance ) {
    $html =  '<div class="row mb-4"><div class="col-auto mx-auto px-0">';
    $html .= ad_300x600_core();
    $html .= '</div></div>';
    echo $html;
  }

  // Widget Backend
  public function form( $instance ) {

  }

  // Updating widget replacing old instances with new
  public function update( $new_instance, $old_instance ) {
    $instance = array();
    return $instance;
  }
} // Class ca_300_x_250_ad_widget ends here
