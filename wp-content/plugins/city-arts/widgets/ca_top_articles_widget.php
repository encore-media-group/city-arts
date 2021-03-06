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
    $use_custom_articles_checkbox = isset( $instance[ 'use_custom_articles_checkbox' ] ) ? true : false;
    $post_show_count = isset( $instance['post_show_count'] ) ? $instance['post_show_count'] : 0;
    $show_images_checkbox = isset( $instance[ 'show_images_checkbox' ] ) ? true : false;
    $show_numbers_checkbox = isset( $instance[ 'show_numbers_checkbox' ] ) ? true : false;
    $center_text_checkbox = isset( $instance[ 'center_text_checkbox' ] ) ? true : false;

    $used_ids = [];
    if( wp_cache_get( 'homepage-articles' ) != false ) :
      $used_ids = wp_cache_get( 'homepage-articles' );
    endif;

    echo $args['before_widget'];

    if ( ! empty( $title ) )
    echo $args['before_title'];

    echo '<div class="row "><div class="col-12 px-lg-3 "><h2 class="sidelines sidebar">' . $title . '</h2></div></div>';
    echo $args['after_title'];

    $current_post = get_queried_object();
    $post_id = $current_post ? $current_post->ID : null;
    $used_ids[] = $post_id;

    $query = [
        'posts_per_page' => $post_show_count,
        'no_found_rows' => true,
        'orderby' => ['date' => 'desc'],
        'post_status'    => 'publish',
        'post__not_in' => $used_ids,
        'meta_query' => Calendar::meta_query_hide_calendar_posts(),
      ];

      if( $use_custom_articles_checkbox ):
        $editor_articles = $this->get_editor_selected_posts();
        if( is_numeric($editor_articles)) :
          if( count($editor_articles >= 1) ) :
          $query['post__in'] =  $editor_articles;
          $query['orderby'] = 'post__in';
          endif;
        endif;
      endif;


    $recent_posts = new WP_Query( $query );

    $row_num  = 1;

    while( $recent_posts->have_posts() ) : $recent_posts->the_post();
      echo __('<div class="row px-3 mb-2">');
        set_query_var( 'row_num', $row_num );
        set_query_var( 'show_numbers' , $show_numbers_checkbox);
        set_query_var( 'use_custom_articles' , $use_custom_articles_checkbox);
        set_query_var( 'show_thumbnails' , $show_images_checkbox);
        set_query_var( 'center_text' , $center_text_checkbox);
        get_template_part( 'item-templates/item', '320x213-ordered' );
      echo __('</div>');
       $row_num++;
    endwhile;

    unset($row_num);
    unset($post_show_count);
    unset($use_custom_articles_checkbox);
    unset($show_images_checkbox);
    unset($show_numbers_checkbox);
    unset($center_text_checkbox);
    wp_reset_postdata();

    echo $args['after_widget'];
  }

  // Widget Backend
  public function form( $instance ) {
    $defaults = array(
      'title' => __( 'Top Stories', 'ca_widget_domain' ),
      'use_custom_articles_checkbox' => 'off',
      'show_images_checkbox' => 'off',
      'show_numbers_checkbox' => 'off',
      'post_show_count' => 0,
      'center_text_checkbox' => 'off'
      );

    $instance = wp_parse_args( ( array ) $instance, $defaults );
    $title = $instance[ 'title' ];
    $post_show_count = $instance['post_show_count'];


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
          type="checkbox" <?php checked( $instance[ 'use_custom_articles_checkbox' ], 'on' ); ?>
          id="<?php echo $this->get_field_id( 'use_custom_articles_checkbox' ); ?>"
          name="<?php echo $this->get_field_name( 'use_custom_articles_checkbox' ); ?>" />
        <label for="<?php echo $this->get_field_id( 'use_custom_articles_checkbox' ); ?>">Use Editor Selected Articles?</label>
      </p>
      <p>
        <label for="<?php echo $this->get_field_id( 'post_show_count' ); ?>"><?php _e( '# Posts:' ); ?></label>
        <input class="widefat"
        id="<?php echo $this->get_field_id( 'post_show_count' ); ?>"
        name="<?php echo $this->get_field_name( 'post_show_count' ); ?>"
        type="text"
        value="<?php echo esc_attr( $post_show_count ); ?>" />
      </p
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
      <p>
        <input
          class="checkbox"
          type="checkbox" <?php checked( $instance[ 'center_text_checkbox' ], 'on' ); ?>
          id="<?php echo $this->get_field_id( 'center_text_checkbox' ); ?>"
          name="<?php echo $this->get_field_name( 'center_text_checkbox' ); ?>" />
        <label for="<?php echo $this->get_field_id( 'center_text_checkbox' ); ?>">Center Text?</label>
      </p>
    <?php
  }

  public function update( $new_instance, $old_instance ) {
    $instance = array();
    $instance = $old_instance;

    $instance[ 'use_custom_articles_checkbox' ] = $new_instance[ 'use_custom_articles_checkbox' ];
    $instance[ 'show_numbers_checkbox' ] = $new_instance[ 'show_numbers_checkbox' ];
    $instance[ 'show_images_checkbox' ] = $new_instance[ 'show_images_checkbox' ];
    $instance[ 'center_text_checkbox' ] = $new_instance[ 'center_text_checkbox' ];

    $instance[ 'post_show_count' ] = $new_instance[ 'post_show_count' ];
    $instance[ 'title' ] = ( ! empty( $new_instance['title'] ) ) ? strip_tags( $new_instance['title'] ) : '';

    return $instance;
  }

  function get_editor_selected_posts() {
     return get_field('featured_articles', 'option');
  }
} // Class ca_top_articles_widget ends here
