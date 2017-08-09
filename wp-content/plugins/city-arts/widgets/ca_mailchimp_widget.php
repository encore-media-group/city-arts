<?php

/*
Widget: City Arts Mailchimp Widget
*/
class ca_mailchimp_widget extends WP_Widget {

  function __construct() {
    parent::__construct(
    'ca_mailchimp_widget',     // Base ID of your widget
    __('CA MailChimp Widget', 'ca_widget_domain'),     // Widget name will appear in UI
    array( 'description' => __( 'Display mailchimp signup.', 'ca_widget_domain' ), )     // Widget description
    );
  }

  // Creating widget front-end
  public function widget( $args, $instance ) {
    $title = apply_filters( 'widget_title', $instance['title'] );

    echo $args['before_widget'];
    if ( ! empty( $title ) )
    echo $args['before_title'];

    echo $args['after_title'];

    echo '<div class="row"><div class="col mb-4">';
      get_template_part( 'item-templates/item', 'mailchimp' );
    echo '</div></div>';


    echo $args['after_widget'];
  }

  // Widget Backend
  public function form( $instance ) {
    if ( isset( $instance[ 'title' ] ) ) {
      $title = $instance[ 'title' ];
    }
    else {
      $title = __( 'New title', 'ca_widget_domain' );
    }
    // Widget admin form
    ?>
      <p>
      <label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title:' ); ?></label>
      <input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>" />
      </p>
    <?php
  }

  // Updating widget replacing old instances with new
  public function update( $new_instance, $old_instance ) {
    $instance = array();
    $instance['title'] = ( ! empty( $new_instance['title'] ) ) ? strip_tags( $new_instance['title'] ) : '';
    return $instance;
  }
}

