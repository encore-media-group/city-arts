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
    $show_images_checkbox = isset( $instance[ 'show_images_checkbox' ] ) ? true : false;
    $show_numbers_checkbox = isset( $instance[ 'show_numbers_checkbox' ] ) ? true : false;

    echo $args['before_widget'];
    if ( ! empty( $title ) )
    echo $args['before_title'];

    echo '<div class="row "><div class="col-12 px-lg-3 "><h2 class="sidelines sidebar">' . $title . '</h2></div></div>';
    echo $args['after_title'];

    $current_post = get_queried_object();
    $post_id = $current_post ? $current_post->ID : null;

    $recent_posts = new WP_Query(array(
        'posts_per_page' => 10,
     //   'offset' => 10,
        'post_status'    => 'publish',
        'post__not_in' => array($post_id),
        'orderby'        => 'date',
        'post_status'    => 'publish',
    //    'meta_query' => array(array('key' => '_thumbnail_id' ))
      )
    );

    $row_num  = 1;

    while( $recent_posts->have_posts() ) : $recent_posts->the_post();
      echo __('<div class="row px-3 mb-2">');
        set_query_var( 'row_num', $row_num );
        set_query_var( 'show_numbers' , $show_numbers_checkbox);
        set_query_var( 'show_thumbnails' , $show_images_checkbox);
        get_template_part( 'item-templates/item', '320x213-ordered' );
      echo __('</div>');
       $row_num++;
    endwhile;

    unset($row_num);
    unset($show_images_checkbox);
    unset($show_numbers_checkbox);
    wp_reset_postdata();

    echo $args['after_widget'];
  }

  // Widget Backend
  public function form( $instance ) {
    $defaults = array(
      'title' => __( 'Top Stories', 'ca_widget_domain' ),
      'show_images_checkbox' => 'off',
      'show_numbers_checkbox' => 'on'
      );

    $instance = wp_parse_args( ( array ) $instance, $defaults );
    $title = $instance[ 'title' ];

    ?>
      <p>
        <label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title:' ); ?></label>
        <input class="widefat"
        id="<?php echo $this->get_field_id( 'title' ); ?>"
        name="<?php echo $this->get_field_name( 'title' ); ?>"
        type="text"
        value="<?php echo esc_attr( $title ); ?>" />
      </p>
      <p>
        <input
          class="checkbox"
          type="checkbox" <?php checked( $instance[ 'show_numbers_checkbox' ], 'on' ); ?>
          id="<?php echo $this->get_field_id( 'show_numbers_checkbox' ); ?>"
          name="<?php echo $this->get_field_name( 'show_numbers_checkbox' ); ?>" />
        <label for="<?php echo $this->get_field_id( 'show_numbers_checkbox' ); ?>">Show Numbers?</label>
      </p>
      <p>
        <input
          class="checkbox"
          type="checkbox" <?php checked( $instance[ 'show_images_checkbox' ], 'on' ); ?>
          id="<?php echo $this->get_field_id( 'show_images_checkbox' ); ?>"
          name="<?php echo $this->get_field_name( 'show_images_checkbox' ); ?>" />
        <label for="<?php echo $this->get_field_id( 'show_images_checkbox' ); ?>">Show Article Thumbnail?</label>
      </p>
    <?php
  }

  public function update( $new_instance, $old_instance ) {
    $instance = array();
    $instance = $old_instance;

    $instance[ 'show_images_checkbox' ] = $new_instance[ 'show_images_checkbox' ];
    $instance[ 'show_numbers_checkbox' ] = $new_instance[ 'show_numbers_checkbox' ];
    $instance['title'] = ( ! empty( $new_instance['title'] ) ) ? strip_tags( $new_instance['title'] ) : '';

    return $instance;
  }
} // Class ca_top_articles_widget ends here
