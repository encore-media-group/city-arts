<?php
function understrap_remove_scripts() {
    wp_dequeue_style( 'understrap-styles' );
    wp_deregister_style( 'understrap-styles' );

    wp_dequeue_script( 'understrap-scripts' );
    wp_deregister_script( 'understrap-scripts' );

    // Removes the parent themes stylesheet and scripts from inc/enqueue.php
}
add_action( 'wp_enqueue_scripts', 'understrap_remove_scripts', 20 );

add_action( 'wp_enqueue_scripts', 'theme_enqueue_styles' );
function theme_enqueue_styles() {

	// Get the theme data
	$the_theme = wp_get_theme();

    wp_enqueue_style( 'child-understrap-styles', get_stylesheet_directory_uri() . '/css/child-theme.min.css', array(), $the_theme->get( 'Version' ) );
    wp_enqueue_script( 'child-understrap-scripts', get_stylesheet_directory_uri() . '/js/child-theme.min.js', array(), $the_theme->get( 'Version' ), true );
}

/**************************************************************/
/*
  The goal of this function is to sync the relationships between posts that have mutual joins for the "relationship" group. Primarly created to support multiple authors, but can actually be used to create larger relation groupings if need be.
*/
/**************************************************************/
function bidirectional_acf_update_value( $value, $post_id, $field  ) {

  // vars
  $field_name = $field['name'];
  $field_key = $field['key'];
  $global_name = 'is_updating_' . $field_name;


  // bail early if this filter was triggered from the update_field() function called within the loop below
  // - this prevents an inifinte loop
  if( !empty($GLOBALS[ $global_name ]) ) return $value;


  // set global variable to avoid inifite loop
  // - could also remove_filter() then add_filter() again, but this is simpler
  $GLOBALS[ $global_name ] = 1;


  // loop over selected posts and add this $post_id
  if( is_array($value) ) {

    foreach( $value as $post_id2 ) {

      // load existing related posts
      $value2 = get_field($field_name, $post_id2, false);


      // allow for selected posts to not contain a value
      if( empty($value2) ) {

        $value2 = array();

      }


      // bail early if the current $post_id is already found in selected post's $value2
      if( in_array($post_id, $value2) ) continue;


      // append the current $post_id to the selected post's 'related_posts' value
      $value2[] = $post_id;


      // update the selected post's value (use field's key for performance)
      update_field($field_key, $value2, $post_id2);

    }

  }


  // find posts which have been removed
  $old_value = get_field($field_name, $post_id, false);

  if( is_array($old_value) ) {

    foreach( $old_value as $post_id2 ) {

      // bail early if this value has not been removed
      if( is_array($value) && in_array($post_id2, $value) ) continue;

      // load existing related posts
      $value2 = get_field($field_name, $post_id2, false);

      // bail early if no value
      if( empty($value2) ) continue;

      // find the position of $post_id within $value2 so we can remove it
      $pos = array_search($post_id, $value2);

      // remove
      unset( $value2[ $pos] );

      // update the un-selected post's value (use field's key for performance)
      update_field($field_key, $value2, $post_id2);

    }

  }

  // reset global varibale to allow this filter to function as per normal
  $GLOBALS[ $global_name ] = 0;

  // return
    return $value;
}

add_filter('acf/update_value/name=relationship', 'bidirectional_acf_update_value', 10, 3);


/* This changes the default ordered of queires to be by modified date */
function ca_alter_query( $query )
{
    if ( $query->is_main_query() && ( $query->is_home() || $query->is_search() || $query->is_archive() )  )
    {
        $query->set( 'orderby', 'modified' );
        $query->set( 'order', 'asc' );
    }
}
add_action( 'pre_get_posts', 'ca_alter_query' );


/* override function in parent */
if ( ! function_exists( 'understrap_posted_on' ) ) :
/**
 * Prints HTML with meta information for the current post-date/time and author.
 */
function understrap_posted_on() {
  $time_string = '<time class="entry-date published updated" datetime="%1$s">%2$s</time>';
  /* let's only show the published date.
  if ( get_the_time( 'U' ) !== get_the_modified_time( 'U' ) ) {
    $time_string = '<time class="entry-date published" datetime="%1$s">%2$s</time><time class="updated" datetime="%3$s">%4$s</time>';
  }
  */
  $time_string = sprintf( $time_string,
    esc_attr( get_the_date( 'c' ) ),
    esc_html( get_the_date('l, F jS, Y') ),
    esc_attr( get_the_modified_date('c') ),
    esc_html( get_the_modified_date('l, F jS, Y') )
  );
  $posted_on = sprintf(
    esc_html_x( '%s', 'post date', 'understrap' ),
    '<a href="' . esc_url( get_permalink() ) . '" rel="bookmark">' . $time_string . '</a>'
  );
  $byline = get_contributors();
  echo '<span class="posted-on">' . $posted_on . '</span><span class="byline"> ' . $byline . '</span>'; // WPCS: XSS OK.
}
endif;


if ( ! function_exists( 'get_contributors' ) ) :
function get_contributors(){
  $relationships = get_field('relationship');
  $html = '';
  if( $relationships ) {
      $count = 0;
      foreach( $relationships as $relationship ) {
        if(get_post_type($relationship->ID) == 'contributor') {
          if($count == 0) {
          $html .= sprintf(esc_html_x( 'by %s', 'post author', 'understrap' ),
          '<span class="author vcard"><a class="url fn n" href="' . esc_url( get_permalink( $relationship->ID ) ) . '">' . esc_html( get_the_title( $relationship->ID ) ) . '</a></span>'
          );
          } else{
            $html .= sprintf(esc_html_x( ' and %s', 'post author', 'understrap' ),
            '<span class="author vcard"><a class="url fn n" href="' . esc_url( get_permalink( $relationship->ID ) ) . '">' . esc_html( get_the_title( $relationship->ID ) ) . '</a></span>'
            );

          }
          $count++;
        }
      }
  }

  return $html;
}
endif;
