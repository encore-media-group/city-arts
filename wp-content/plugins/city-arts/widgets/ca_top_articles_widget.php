<?php

/*
Widget: City Arts Top Articles Widget
*/
class ca_top_articles_widget extends WP_Widget {

  function __construct() {
    parent::__construct(
    'ca_top_articles_widget',     // Base ID of your widget
    __('CA Top Articles Widget', 'ca_widget_domain'),     // Widget name will appear in UI
    array( 'description' => __( 'List top CA articles.', 'ca_widget_domain' ), )     // Widget description
    );
  }

  // Creating widget front-end
  public function widget( $args, $instance ) {
    $title = apply_filters( 'widget_title', $instance['title'] );

    echo $args['before_widget'];
    if ( ! empty( $title ) )
    echo $args['before_title'];
    echo '<div class="row"><div class="col-12"><h3 class="sidelines">' . $title . '</h3></div></div>';
    echo $args['after_title'];

    $recent_posts = new WP_Query(array('posts_per_page' => 3, 'offset' => 10, 'meta_query' => array(array('key' => '_thumbnail_id' ))));
    // This is where you run the code and display the output

    while( $recent_posts->have_posts() ) : $recent_posts->the_post();
      echo __('<div class="col-12">');
        get_template_part( 'item-templates/item', 'very-small' );
      echo __('</div>');
      endwhile;
    wp_reset_postdata();

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
} // Class ca_top_articles_widget ends here
