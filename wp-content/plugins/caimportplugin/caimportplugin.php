<?php

/*
Plugin Name: City Arts Drupal Import
Plugin URI:
Description:
Author: Aaron Starkey
Version: 1.5
Author URI: http://factorybeltproductions.org
*/

$wp_uploads = wp_upload_dir();
$upload_dir = wp_normalize_path(realpath($wp_uploads['basedir']));
define("MEDIAFROMFTP_PLUGIN_UPLOAD_DIR", $upload_dir);

add_action('admin_menu', 'cityarts_import_menu');

function cityarts_import_menu(){
  add_menu_page('City Arts Import Page', 'City Arts Import', 'manage_options', 'import-button-slug', 'cityarts_import_admin_page');

}

function cityarts_import_admin_page() {
  if (!current_user_can('manage_options'))  {
    wp_die( __('You do not have sufficient pilchards to access this page.')    );
  }

  echo '<div class="wrap">';
  echo '<h2>City Arts Import Page</h2>';

  // Check whether the button has been pressed AND also check the nonce
  if (isset($_POST['import_button']) && check_admin_referer('import_button_clicked')) {
   // the button has been pressed AND we've passed the security check
  //OLDset_contributors(); //do 1
  //set_articles('article'); //do 2 NOTE: ADD ADDITION OF PAGES- TODO
  //set_writers();
  //set_articles('page'); //do 2 NOTE: ADD ADDITION OF PAGES- TODO

  //set_issues();
//set_features_to_issues();

  //OLD !!!! before you run sync, you have to run an update sql statement against the article table with the post id for that author.
  //OLD sync_posts_to_writers();//do 3 NOTE: are you using the correct ACF value?? make sure you are!!!!

  set_top_categories(); //do 4
  //set_secondary_categories(); //do 5
  //set_parent_child_category_relationship(); //do 6
  //set_excerpts(); //do 8 (this sets the short and long excerpts)
  //sync_wp_post_id_to_image_inline_images(); (this is now an asyn task, do not run this function)
  //update_image_urls_in_posts(); // do 9
  //clean up functions - do last
  /*
    //THESE CANN ALL BE RUN AT ONCE

      remove_current_categories('city_neighborhood');
      remove_current_categories('venue_amenity');
      remove_current_categories('ad_groups');
      remove_current_categories('current_moods');
      remove_current_categories('current_tagging');
      remove_current_categories('current_venue_tags');
      remove_current_categories('event_type');
      remove_current_categories('current_neighborhoods');

      // slug (required), parent-slug (should be "" if no parent or reassigning parent) , Name (required), rename-slug (optional)
      reset_category_parent( 'issue', '', 'Issue' );// create category
      reset_category_parent( 'column', '', 'Column' );// create category
      reset_category_parent( 'feature', '', 'Feature' );// create category
      reset_category_parent( 'lifestyle', '', 'Lifestyle' );// create category
        reset_category_parent( 'style-profile', 'lifestyle', 'Style Profile' );// create category
        reset_category_parent( 'taste-test', 'lifestyle', 'Taste Test' );// create category
      reset_category_parent( 'fiction', '', 'Fiction' );// create category
      reset_category_parent( 'humor', '', 'Humor' );// create category
      reset_category_parent( 'genre-bender', '', 'Genre Bender' );// create category

      reset_category_parent( 'style', 'lifestyle' );
      reset_category_parent( 'around-town', 'feature' );

      */

/*
      reset_category_parent( 'creative-nonfiction', '', 'Creative Non-Fiction' );// create category
      reset_category_parent( 'poetry', '', 'Poetry' );// create category


      reset_category_parent( 'cover-story', 'feature' );
      reset_category_parent( 'photo-essay', 'feature' );
      reset_category_parent( 'open-studio', 'feature' );
      reset_category_parent( 'fabric', 'feature' );
      reset_category_parent( 'band-in-process', 'feature');
      reset_category_parent( 'lit', 'feature');
      reset_category_parent( 'multimedia', 'feature');
      reset_category_parent( 'profile', 'feature');
      reset_category_parent( 'style', 'feature');
      reset_category_parent( 'sketchbook-porn', 'feature');

      reset_category_parent( 'album-of-the-month', 'review' );
      reset_category_parent( 'book-of-the-month', 'review' );
      reset_category_parent( 'singles', 'review', 'Attractive Singles' );
      reset_category_parent( 'scarecrow', 'review', 'Scarecrow Suggests' ); //rename the name of the category
      reset_category_parent( 'review', '' ); //make ths cateogry have no parent

      reset_category_parent( 'epilogue', 'creative-nonfiction');

      reset_category_parent( 'hamil-with-care', 'column' );
      reset_category_parent( 'faded-signs', 'column' );
      reset_category_parent( 'field-notes', 'column' );
      reset_category_parent( 'at-large', 'column' );
      reset_category_parent( 'the-week-in-arts', 'column' );
      reset_category_parent( 'editors-note', 'column' );

      reset_category_parent( 'news-notes', '', 'News' );

      reset_category_parent( 'qa', '' );
      reset_category_parent( 'preview', '' );
      reset_category_parent( 'premiere', '');
      reset_category_parent( 'essay', '');
      reset_category_parent( 'see-it-this-week', '');

      reset_category_parent( 'art-article_type', '', 'Artwork', 'artwork');//give it a new slug
      reset_category_parent( 'creative-writing', 'poetry');
      reset_category_parent( 'food', 'lifestyle');
      reset_category_parent( 'sponsored', '');
      */

    // one_time_migrate_Features_to_feature();
    // delete_category('features');
    // shift_genre_bender();
    // delete_category('genre-bender-2015');
    // delete_category('genre-bender-2016');
    // delete_category('genre-bender-2017');

    /*
    HOW TO IMPORT AND ATTACH IMAGES


      overall image processing:
      - take update from master production database
      - run query to export slideshow
      - run query to export inline
      - then, import those two tables into the production enviroment
      - then run synce_wp_post_id_to_image() which wil update the tables we imported with the related wp_id
      - then do the import and attachment of those imagesavealpha(image, saveflag)

    */

  }

  echo '<form action="options-general.php?page=import-button-slug" method="post">';

  // this is a WordPress security feature - see: https://codex.wordpress.org/WordPress_Nonces
  wp_nonce_field('import_button_clicked');
  echo '<input type="hidden" value="true" name="import_button" />';
  submit_button('Import Data');
  echo '</form>';

  echo '</div>';


}

function update_image_urls_in_posts() {

  $posts = get_all_wp_posts();
  foreach( $posts as $post ) {
    $updated_post = swap_images_from_post( $post );

    if($updated_post['content_is_updated'] == true ) {
      $array_to_update = array(
        'ID' => ($post->ID),
        'post_content' => $updated_post['post_content']
        );
      echo "<pre>updating post: " . ($post->ID) . "</pre>";

      $post_id_out = wp_update_post($array_to_update, true);

      if (is_wp_error($post_id_out)) {
        $errors = $post_id_out->get_error_messages();
        foreach ($errors as $error) {
          echo $error;
        }
      }
    }
  }
}

function get_all_wp_posts() {
  global $wpdb;
  $table = "wpsa_posts";
  $myrows = $wpdb->get_results( "SELECT * FROM " . $table . " where post_content !='' and post_status = 'publish'");// and id=18989");
  // limit 0, 5000000");
  return $myrows;
}
function swap_images_from_post($post) {
  /* parse the contents of the post and extract image urls */

  $ID = $post->ID;
  $content_is_updated = false;
  $post_images = array();
  $attached_images = array();

  $attached_images = get_images_attached_to_this_post($ID);
  $post_thumbnail_id = get_post_thumbnail_id( $ID ); //we want to know what the featured image is.

  libxml_use_internal_errors(true);

  $doc = new DOMDocument();
  $doc->loadHTML('<html>' . mb_convert_encoding($post->post_content, 'HTML-ENTITIES', 'UTF-8') .'</html>', LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
  $doc->encoding = 'UTF-8';

  $xml = simplexml_import_dom($doc);
  $images = $xml->xpath('//img');

  foreach ($images as $img) {
    if(strpos($img['src'], 'wp-content') === false ){

      $img_src = $img['src'];
      echo "postid: " . $post->ID . " - ";
      echo "raw img_src:  " . $img_src . " <br>";

      $img_src = strtok($img_src, '?');
      echo "querystring removed: " . $img_src . "<br>";

      $img_src = str_replace("%20", "", $img_src);
      echo "removed % 20 : " . $img_src . " <br>";

      $img_src = str_replace("%3A", "", $img_src);
      echo "removed % 3A : " . $img_src . " <br>";

      $img_src = str_replace("%2C", "", $img_src);
      echo "removed % 2C : " . $img_src . " <br>";

      $img_src = slug (rawurldecode( basename( $img_src ) ) );
      echo "(slug): " . $img_src . " <br>";

      echo "the attached images to this post id are:<br>";
      echo "<pre>" . var_dump($attached_images) . "</pre>";

      $match_index = array_search( $img_src, $attached_images  );
      if($match_index !== false) {
        if( $match_index >= 0) {
          $img['class'] = " updated-image";
          $img['height'] = "";
          $img['width'] = "";
          $img['src'] = "/wp-content/uploads/" . $attached_images[$match_index];

          echo " and a match found for: " . $img_src . ".<br>";
          $content_is_updated = true;
        } else {
          echo "no match for ". $img_src . "<br>";
        }
      } else {
        echo " but not attached.<br>";
      }
    }
  }

  $trim_off_front = strpos($doc->saveHTML(),'<body>') + 6;
  $trim_off_end = (strrpos($doc->saveHTML(),'</body>')) - strlen($doc->saveHTML());

//  $content_out = substr($doc->saveHTML(), $trim_off_front, $trim_off_end);
   $content_out =  str_replace(array('<html>','</html>') , '' , $doc->saveHTML());
   $content_out_to_strip = $content_out;
//   $content_out = strip_tags($content_out_to_strip, "<p><a><b><i><strong><img><blockquote><em><h1><h2><h3><h4>");
  $content_is_updated = true;

  //  $content_out =  $doc->saveHTML();
  echo "updating: " . $ID . "<br>";
  return array(
    'content_is_updated' => $content_is_updated,
    'post_content' => $content_out
    );
  }

function get_images_attached_to_this_post($post_id) {
  $images = get_attached_media('image', $post_id);

  $image_array = array();
  foreach($images as $image) {
      $image_array[] = basename(wp_get_attachment_image_src($image->ID,'full')[0]);
   }
  return $image_array;
}

function get_all_images() {
  global $wpdb;
  $table = "tmp_inline_image_list";
 // $myrows = $wpdb->get_results( "SELECT * FROM " . $table . ' where new_wp_attachment_id = 0 limit 0, 16000');
   $myrows = $wpdb->get_results( "SELECT * FROM " . $table);
   return $myrows;
}

function delete_all_images_in_the_database() {
  //saving this for when needed. don't actually run from wordpress...run via mysql
  //DELETE FROM wp_postmeta WHERE post_id IN( SELECT id FROM wp_posts WHERE post_type = 'attachment')
  //DELETE FROM wp_posts WHERE post_type = 'attachment'
  //be very careful!

}


function sync_single_image_wp_post_id_to_image_inline_images($myrow){
  //this is used by the async plugin.

  $output = "start: ";
  global $wpdb;

/*
  $table = "tmp_inline_image_list";
  $myrows = $wpdb->get_results( "SELECT * FROM " . $table . ' limit 0, 20 ');
*/
  require_once(ABSPATH . '/wp-admin/includes/file.php');
  require_once(ABSPATH . '/wp-admin/includes/media.php');
  require_once(ABSPATH . '/wp-admin/includes/image.php');
  $upload_dir = MEDIAFROMFTP_PLUGIN_UPLOAD_DIR;


  if ($myrow) {

      $uri = $myrow->uri;
      $file_path_and_name = $upload_dir . '/inline_images/' . str_replace("public://inline_images/","",$uri);

      if(!is_file($file_path_and_name)) {
        $output .=  "FILE NOT FOUND: " . $file_path_and_name . "<br>";
      } else {

        $output .=  "FILE FOUND: " . $file_path_and_name . "<br>";
        $new_wp_post_id = $myrow->new_wp_post_id;
        $inline_image_title = $myrow->field_inline_images_title;
        $delta = $myrow->delta;
        $image_caption = $myrow->field_inline_images_title;
        $image_caption2 = $myrow->field_inline_images_alt;
        if($image_caption == '') { $image_caption = $image_caption2; }

        $array = array( //array to mimic $_FILES
          'name' => basename($file_path_and_name), //isolates and outputs the file name from its absolute path
          'type' => wp_check_filetype($file_path_and_name), // get mime type of image file
          'tmp_name' => $file_path_and_name, //this field passes the actual path to the image
          'error' => 0, //normally, this is used to store an error, should the upload fail. but since this isnt actually an instance of $_FILES we can default it to zero here
          'size' => filesize($file_path_and_name) //returns image filesize in bytes
        );

        $attachment_id = media_handle_sideload($array, $new_wp_post_id); //the actual image processing, that is, move to upload directory, generate thumbnails and image sizes and writing into the database happens here

        if (is_wp_error($attachment_id)) {
            $errors = $attachment_id->get_error_messages();
            foreach ($errors as $error) {
            $output .=  $error . "<br>";
            }
            $output .=  "<p>";
          } else {
            $output .= ' Attachment id: ' . $attachment_id . '<br>';

            if($delta == 0) { set_post_thumbnail( $new_wp_post_id, $attachment_id ); }

            if($image_caption != '') {
              wp_update_post(array('ID' => $attachment_id, 'post_excerpt' => $image_caption));
            }
            $wpdb->query('UPDATE tmp_inline_image_list SET new_wp_attachment_id = ' . $attachment_id .  ' WHERE new_wp_post_id= ' . $new_wp_post_id);
          }
      }
  }
  return $output;
}
/* not used anymore, we use a taxonomy instead.
function update_post_relationship($new_wp_id, $new_wp_contributor_id) {
  //this really only needs to be run once.
  $article_relationship = get_field( 'relationship', $new_wp_id );
  $contributor_relationship = get_field( 'relationship', $new_wp_contributor_id );

  $contributor_post = get_post($new_wp_contributor_id);
  $article_post = get_post($new_wp_id);

  if( !is_array($article_relationship) ):
    $article_relationship = array();
  endif;

  if( !is_array($contributor_relationship) ):
    $contributor_relationship = array();
  endif;

  array_push( $article_relationship, $contributor_post );
  array_push( $contributor_relationship, $article_post );

  update_field( 'field_5960730a4bb42', $article_relationship, $new_wp_id );
  update_field( 'field_5960730a4bb42', $contributor_relationship, $new_wp_contributor_id );

  echo "updating -> ";
}
*/
/* not used anymore, we use a taxonomy instead.
function sync_posts_to_writers() {
  global $wpdb;
  $table = "tmp_article_export_10_16_2017";
  $myrows = $wpdb->get_results( "SELECT * FROM " . $table);
  $count = 0;
      if ($myrows) {
        foreach ( $myrows as $myrow )
        {
          $count++;
          echo "# " . $count . " - ";
          $new_wp_id = $myrow->new_wp_id;
          $new_wp_contributor_id = $myrow->new_wp_contributor_id;
          if($new_wp_contributor_id > 0) {
            update_post_relationship($new_wp_id, $new_wp_contributor_id);
          }
          else { "not updating -> ";
            echo 'new_wp_id: ' . $new_wp_id . " - " . $new_wp_contributor_id . "<br>";
          }
        }
      }

  //update contributor post with list of articles

}
*/
function set_writers() {
/* this is a new version of importing writers (that needs to be renmaed to contributors that makes them a taxonomy vs. a post
  .5 add new column to artist table that captures the taxonomoy id
  0. create temporary writer class
  1. pull from author post table and create new taxonomy
  2. associate those with the posts by running this query:
  update tmp_authors_10_17_17 tap,  tmp_article_export_10_16_2017 tae set  tae.new_wp_writer_cat_id = tap.new_cat_id where  tae.field_author_target_id = tap.nid

  3. rename writer to contributor
*/

/* get the authors */
  global $wpdb;
  $myrows = $wpdb->get_results( "SELECT * FROM tmp_authors_10_17_17");

  if ($myrows) {
    foreach ( $myrows as $myrow ) {
      $drupal_id = $myrow->nid;
      $post_title = wp_strip_all_tags($myrow->post_title);
      $post_slug = slug($myrow->wp_ready_postname);
      $post_content = $myrow->body_value;

      /* set taxonomy */
      $writer = array( 'slug' => $post_slug, 'name' => $post_title, 'description' => $post_content, 'parent' => 0);

      //echo "<pre>" . var_dump($writer) . "</pre>";
      insert_writer($writer, $drupal_id);
    }
  }

  // sycn it all up..
  connect_posts_to_new_writers();

}

function insert_writer( $writer, $drupal_id ) {
  $cat_id = 0;

  if( term_exists( $writer['slug'] ) == 0 ) {
    echo "creating new entry for " . $writer['slug'] . "<br>" ;

    $cid = wp_insert_term( $writer['name'], 'contributor', $writer );

    if ( ! is_wp_error( $cid ) ) {
      echo "preparing to update database with " . var_dump($cid) . "<br>";

      $cat_id = isset( $cid['term_id'] ) ? $cid['term_id'] : 0;
      update_writer_with_cat_id( $cat_id, $drupal_id );

    } else {
       echo $cid->get_error_message();
    }
  }
  else {
    echo "already exists: " .  $writer['slug'] . "<br>" ;
  }
  return $cat_id;
}

function update_writer_with_cat_id( $cat_id, $drupal_id ) {
  if( !empty($cat_id) && !empty($drupal_id) ) {
    global $wpdb;
    $wpdb->query('UPDATE tmp_authors_10_17_17 SET new_cat_id = ' . $cat_id .  ' WHERE nid = ' . $drupal_id);
    echo 'success on insert of drupal_id: ' . $drupal_id . ' and wp_post_id' . $cat_id .'<br>';
  } else {
    echo 'error on insert of drupal_id: ' . $drupal_id . "<br>";
  }
}

function connect_posts_to_new_writers() {

  /* does the article import table have a wordpress id? */
  global $wpdb;
  $table = "tmp_article_export_10_16_2017";

  $wpdb->query("update tmp_authors_10_17_17 tap,  ". $table . " tae set  tae.new_wp_writer_cat_id = tap.new_cat_id where  tae.field_author_target_id = tap.nid");

  $myrows = $wpdb->get_results( "SELECT * FROM " . $table);

  $count = 0;

  if ($myrows) {
    foreach ( $myrows as $myrow ) {
      $count++;
      echo "# " . $count . " - ";
      $new_wp_id = $myrow->new_wp_id;
      $new_wp_writer_cat_id = $myrow->new_wp_writer_cat_id;

      if( $new_wp_writer_cat_id > 0 ) {

        wp_set_object_terms(  $new_wp_id , array( (int)$new_wp_writer_cat_id ), 'contributor' , false );

      } else {
        echo "not updating -> ";
      }
      echo 'new_wp_id: ' . $new_wp_id . " - " . $new_wp_writer_cat_id . "<br>";
    }
  }

}
/* not used anymore, we use a taxonomy instead.
function set_contributors() {

  /*
  this feature has a dependcy on using the pro version of Advanced Custom Fields.
  1. Create a new post type of "contributor" -> there will be a version of this for inclusion in functions.php so that it's trackable and maintainable via code vs. plugin
  2. Create a new field group called "Relationship" of Field Type "Relationship"
  2.1  note, i will eventualy include this definition as a function in functions.php. it'll be faster and trackable via git as well
  3. The rest of the settings are up to you, but i usually leave them as is.
  4. export authors via mysql into table
  5. import them using the this function. :)
  6. run import articles function
  7. run function to then update the table with the correct relationships.
  8. this does the following--> it gets a list of authors from the tmp_author table, then loops through them and for each one
  queries the join table to get the drupal post id, then, it looks up the wp_post_id from the tmp_articles table and updates the
  meta table
  ****
  so for the contrinbutor wp_post_id -> update that meta_value for key "relationship" by adding the article wp_post_ids
  ****
  */
  /*
  global $wpdb;
  $myrows = $wpdb->get_results( "SELECT * FROM tmp_authors_10_17_17");

      if ($myrows) {
        foreach ( $myrows as $myrow )
        {
          $drupal_id = $myrow->nid;
          $post_title = wp_strip_all_tags($myrow->post_title);
          $post_slug = slug($myrow->wp_ready_postname);
          $post_date = $myrow->post_date;
          $post_date_modified = $myrow->post_modified;
          $post_content = $myrow->body_value;
          $guid = 'http://71672.com/uncategorized/' . $post_slug;
          //$post_category = [$writer_cat_id];

          $array_to_insert = array(
              'ID' => 0,
              'comment_status'  =>  'closed',
              'ping_status'   =>  'closed',
              'post_author'   =>  1,
              'guid' => $guid,
              'post_date' => $post_date,
              'post_date_gmt' => $post_date,
              'post_modified' => $post_date_modified,
              'post_modified_gmt' => $post_date_modified,
              'post_category' => $post_category,
              'post_title'    =>  $post_title,
              'post_name'   =>  $post_slug,
              'post_status'   =>  'publish',
              'post_content' => 'temp content', //we do this as some post have empty content..you can't create a post with empty content, but you can update it to be empty.
              'filter' => true,
              'post_type'   =>  'contributor'
            );

        echo '<pre>' . var_dump($array_to_insert) . '</pre>';
        echo "<p>";
        //add_filter( 'wp_insert_post_empty_content', '__return_false', 100 );
        $post_id = wp_insert_post($array_to_insert, true);

        if (is_wp_error($post_id)) {
          $errors = $post_id->get_error_messages();
          foreach ($errors as $error) {
            echo $error . "<br>";
          }
          echo "<p>";
        } else {
          if(!empty($post_id)){
            //update body with correct content
            wp_update_post(array('ID' => $post_id, 'post_content' => $post_content));

            $wpdb->query('UPDATE tmp_authors_10_17_17 SET new_wp_id = ' . $post_id .  ' WHERE nid = ' . $drupal_id);
            echo 'success on insert of drupal_id: ' . $drupal_id . ' and wp_post_id' . $post_id .'<br>';
          } else {
            echo 'error on insert of drupal_id: ' . $drupal_id . "<br>";
          }
        }
      }
    }
}
*/
function set_articles($post_type) {
  global $wpdb;

// RESET FOR ARTICLE OR FOR POST
  $table = "tmp_article_export_10_16_2017";
  $table = "tmp_page_export_10_16_2017";
  $myrows = $wpdb->get_results( "SELECT * FROM " . $table . " where post_type='" . $post_type . "'");
  $count = 0;
      if ($myrows) {
        foreach ( $myrows as $myrow )
        {
          $count++;
          echo "# " . $count . " - ";
          if($myrow->new_wp_id > 0 ) {
            echo $myrow->new_wp_id . "already exists. <br>";
            continue;
          }
          $drupal_id = $myrow->nid;
          $post_title = wp_strip_all_tags($myrow->post_title);
          $post_slug = slug($myrow->wp_ready_postname);
          $post_date = $myrow->post_date;
          $post_date_modified = $myrow->post_modified;
          $post_content = $myrow->post_content; //this is for posts only, not pages.
          $post_status = $myrow->post_status;
          $guid = 'http://cityartsmagazine.com/uncategorized/' . $post_slug;
          if($myrow->post_type == 'page') {
            $post_content = $myrow->body_value; // only for pages
          }

          $array_to_insert = array(
              'ID' => 0,
              'comment_status'  =>  'closed',
              'ping_status'   =>  'closed',
              'post_author'   =>  1,
              'guid' => $guid,
              'post_date' => $post_date,
              'post_date_gmt' => $post_date,
              'post_modified' => $post_date_modified,
              'post_modified_gmt' => $post_date_modified,
              'post_title'    =>  $post_title,
              'post_name'   =>  $post_slug,
              'post_status'   =>  $post_status,
              'post_content' => 'temp content', //we do this as some post have empty content..you can't create a post with empty content, but you can update it to be empty.
              'filter' => true,
              'post_type'   =>  $post_type //'post'
            );

        echo "<pre>" . var_dump($array_to_insert) . "</pre>";

        $post_id = wp_insert_post($array_to_insert, true);

        if (is_wp_error($post_id)) {
          $errors = $post_id->get_error_messages();
          foreach ($errors as $error) {
            echo $error;
            echo '<br>';
          }
        } else {
          if(!empty($post_id)){
            wp_update_post(array('ID' => $post_id, 'post_content' => $post_content));

            $wpdb->query('UPDATE ' . $table .' SET new_wp_id = ' . $post_id .  ' WHERE nid = ' . $drupal_id);
            echo 'success on insert of nid: ' . $drupal_id . ' and new_wp_id: ' . $post_id .'<br>';
          } else {
            echo 'error on setting new_wp_id of nid: ' . $drupal_id . "<br>";
          }
        }
      }
    }
  echo "<br><b>SET ARTICLES FINISHED.<b><br>";
}

function set_issues() {
  global $wpdb;

  $parent_slug = 'issue';

  $cover_story_cat_id_obj = get_category_by_slug('cover-story');
  $cover_story_cat_id = $cover_story_cat_id_obj->term_id;

  $issue_table = "tmp_issue_export_10_16_2017";
  $article_table = "tmp_article_export_10_16_2017";

  $query =  "select  tae.nid,  tae.new_wp_id, tie.field_featured_article_target_id, replace( replace( replace(tie.wp_ready_postname, 'issues-', ''), 'seattle-', ''), 'tacoma-', '') `postnameclean` from " . $issue_table . " tie LEFT OUTER JOIN " . $article_table . " tae on tae.nid = tie.field_featured_article_target_id";

  $myrows = $wpdb->get_results( $query);
  if ($myrows) {
    $count = 0;
    foreach ( $myrows as $myrow ) {
      $count++;
      echo "# " . $count . ": ";
      $nid = $myrow->nid;
      $postnameclean = $myrow->postnameclean;
      $new_wp_id = $myrow->new_wp_id;
      $field_featured_article_target_id = $myrow->field_featured_article_target_id;

      echo  'clean: ' . $postnameclean . " - new_wp_id: " . $new_wp_id . " - ";
      $slug_array = explode("-", $postnameclean);

      if( strlen( $slug_array[0] ) == 4 && is_numeric($slug_array[0]) ) { //it's a year
        $year = $slug_array[0];
        $monthNum = $slug_array[1];

        $dateObj   = DateTime::createFromFormat('!m', $monthNum);
        $monthName = $dateObj->format('F');
      } else {
        $year = $slug_array[1];
        $monthName = $slug_array[0];
      }

      $cat_name = ucfirst($monthName) . " " . $year;
      $cat_slug = strtolower($monthName) . "-" . $year;

      //set new category
      $new_cat_id = set_issue_category( $cat_name, $cat_slug, $parent_slug );

      //update our source table with the new cat id
      $update_sql = 'UPDATE ' . $issue_table  . ' SET new_wp_category_id = ' .  $new_cat_id . ' WHERE field_featured_article_target_id = ' . $field_featured_article_target_id;

      echo $update_sql . "<br>";

      //$wpdb->query( $wpdb->prepare( $update_sql ) );
      $wpdb->query( $update_sql );

      //assign this new category to the posts
      wp_set_post_categories( $new_wp_id, $new_cat_id, true);
      wp_set_post_categories( $new_wp_id, $cover_story_cat_id, true);

      echo $new_cat_id  . " - " . $cat_name . " - " . $cat_slug . "<br>";


    }
  }
}

function set_issue_category($cat_name, $cat_slug, $parent_slug, $new_cat_slug = '') {

  if ( $parent_slug ) {
    $parent_cat_id_obj = get_category_by_slug($parent_slug);
    $parent_cat_id = $parent_cat_id_obj->term_id;
  } else {
    $parent_cat_id = 0;
  }

  $cat_id_obj = get_category_by_slug($cat_slug);
  $cat_id = ($cat_id_obj == true ? $cat_id_obj->term_id : null);

  if ( !empty( $new_cat_slug ) ) {
    $cat_slug = $new_cat_slug;
  }

  $my_cat_args = array(
    // for insert
    'cat_name' => $cat_name,
    'category_description' => '',
    'category_nicename' => $cat_slug,
    'category_parent' => $parent_cat_id,
    //for update
    'name' => $cat_name,
    'slug' => $cat_slug,
    'parent' => $parent_cat_id
  );

  var_dump($my_cat_args);

  if(!$cat_id){
    echo $cat_slug . " <- slug not found, creating new category.<br>";
    echo '<pre>' . var_dump($my_cat_args) . '</pre>';
    $cat_id = wp_insert_category($my_cat_args);
    var_dump($cat_id);

  } else {
    echo "cat " . $cat_id . " exists. so let's update.<br>";
    //wp_update_term( $term_id, $taxonomy, $args )
    //Defaults will set 'alias_of', 'description', 'parent', and 'slug' if not defined in $args already.
    $update_response = wp_update_term( $cat_id, 'category', $my_cat_args );

    if (is_wp_error($update_response)) {
      $errors = $update_response->get_error_messages();
      foreach ($errors as $error) {
        echo $error;
      }
    } else {
      echo var_dump($update_response) . "<br>";
    }
  }

  return $cat_id;
}

function set_features_to_issues() {
  // YOU MUST IMPORT the field_revision_field_features TABLE into production FIRST!!!!!
  global $wpdb;
    $cat_obj = get_category_by_slug( 'feature' );
    $feature_cat_id = $cat_obj->term_id;

  $sql = "select fff.entity_id, fff.field_features_target_id, fff.delta,
  tie.new_wp_category_id, tae.new_wp_id
  from field_revision_field_features fff
  left outer join tmp_issue_export_10_16_2017 tie on tie.nid = fff.entity_id
  left outer join tmp_article_export_10_16_2017 tae on tae.nid = fff.field_features_target_id";

//  $sql = "select * from tmp_attach_features_to_issues";
  echo $sql;
  $myrows = $wpdb->get_results( $sql );
  if ($myrows) {
    $count = 0;
    foreach ( $myrows as $myrow ) {
      $count++;
      echo "# " . $count . ": ";
      echo "<pre> " . var_dump($myrow) . "</pre><p>";
      $new_wp_id = $myrow->new_wp_id;
      $new_wp_category_id = $myrow->new_wp_category_id;
      echo "updating" . $new_wp_id . "<br>";
      wp_set_post_categories( $new_wp_id, $new_wp_category_id, true);
      wp_set_post_categories( $new_wp_id, $feature_cat_id, true);

    }
  }

}


function set_excerpts() {
  global $wpdb;

  $table = "tmp_article_export_10_16_2017";
  $myrows = $wpdb->get_results( "SELECT * FROM " . $table);
  $count = 0;

  if ($myrows) {
    foreach ( $myrows as $myrow ) {
      $count++;
      echo "# " . $count . " - ";
      $post_id = $myrow->new_wp_id;
      $post_excerpt = $myrow->field_long_teaser_value;
      $post_short_teaser = $myrow->field_short_teaser_value;

      $array_to_update = array(
              'ID' => $post_id,
              'post_excerpt' => $post_excerpt
              );
      echo "<pre>" . var_dump($array_to_update) . "</pre>";

      //update post excerpt
      $post_id_out = wp_update_post($array_to_update, true);

      if (is_wp_error($post_id_out)) {
        $errors = $post_id_out->get_error_messages();
        foreach ($errors as $error) {
          echo $error;
        }
      }

      //update custom property
      import_meta_content( $post_id, 'post_short_teaser', $post_short_teaser );

     }
  }

}


function set_top_categories() {
  echo '<div id="message" class="updated fade"><p> set_top_categories. </p></div>';
  global $wpdb;
  $table = 'tmp_export_of_top_level_categories_7_4_17';
  //get categories
  $myrows = $wpdb->get_results( 'SELECT * FROM ' . $table );

  if ($myrows) {
    foreach ( $myrows as $myrow )
    {
      $cat_name = trim($myrow->cat_name);
      $cat_slug = $myrow->path;
      $vid = $myrow->vid;
      $my_cat = array(
        'cat_name' => $cat_name,
        'category_description' => '',
        'category_nicename' =>$cat_slug,
        'category_parent' => '');

      var_dump($my_cat);
      $my_cat_id = wp_insert_category($my_cat);

      set_wp_post_id_of_new_cats( $my_cat_id, $vid);

      echo 'vid: ' . $vid . 'new cat id: ' . $my_cat_id  . ' - ' . 'cat_name: ' . $cat_name . ' cat_slug: ' . $cat_slug . '<br>';
    }
  }
}

function set_wp_post_id_of_new_cats($my_cat_id, $vid) {
  global $wpdb;

  $wpdb->query( $wpdb->prepare( "UPDATE tmp_export_of_top_level_categories_7_4_17
    SET
      new_wp_cat_id = %d
    WHERE
      vid = %d
      ",
          $my_cat_id,
          $vid
    )
  );
}






function set_secondary_categories() {
echo '<div id="message" class="updated fade"><p> set_secondary_categories. </p></div>';
  global $wpdb;
  $table = ' tmp_secondary_categories_table_7_4_17_2';
  $myrows = $wpdb->get_results( 'SELECT * FROM ' . $table);
  $setPostIDArray = array();

  if ($myrows) {
    foreach ( $myrows as $myrow )
    {
      $cat_name = trim($myrow->cat_nice_name);
      $cat_slug = $myrow->clean_path;
      $tid = $myrow->tid;

      $idObj = get_category_by_slug($cat_slug);
      $id = '';
      //echo 'idObj is: ' . idObj . "<br>";
      if(!$idObj){
        echo $cat_slug . " <- slug not found, creating new catagory.<br>";

        $my_cat = array(
          'cat_name' => $cat_name,
          'category_description' => '',
          'category_nicename' =>$cat_slug,
          'category_parent' => '');

        echo '<pre>' . var_dump($my_cat) . '</pre>';
        $my_cat_id = wp_insert_category($my_cat);
        set_wp_post_id_of_new_secondary_cats($my_cat_id, $tid);

        echo 'tid: ' . $tid . 'new cat id: ' . $my_cat_id  . ' - ' . 'cat_name: ' . $cat_name . ' cat_slug: ' . $cat_slug . '<br>';
      }
      else {
        echo 'updating: ' . $tid . ' and ' . $cat_slug . ' with: ' . $idObj->term_id . '<br>';
        set_wp_post_id_of_new_secondary_cats($idObj->term_id, $tid);
//        echo 'tid: ' . $tid . 'new cat id: ' . $my_cat_id  . ' - ' . 'cat_name: ' . $cat_name . ' cat_slug: ' . $cat_slug . '<br>';

      }
    }

  }
}

function set_wp_post_id_of_new_secondary_cats($my_cat_id, $tid) {
  global $wpdb;
  $table = ' tmp_secondary_categories_table_7_4_17_2';
  $wpdb->query(
    $wpdb->prepare( 'UPDATE ' . $table . ' SET new_wp_cat_id = %d WHERE tid = %d ', $my_cat_id, $tid
  ));

}


function set_connection_between_posts_and_categories(){
/*
  first, run this query in production to update the join table with wp posts:
  Update tmp_genre_to_post_map_7_9_2017 gpm, tmp_article_export_10_16_2017 ae
    set gpm.wp_post_id = ae.new_wp_id
    where gpm.nid = ae.nid

  second, then run this to update the cat_id
  Update
    tmp_genre_to_post_map_7_9_2017 gpm,
    tmp_secondary_categories_table_7_4_17_2 sct
    set gpm.wp_cat_id = sct.new_wp_cat_id
    where gpm.tid = sct.tid
*/

  global $wpdb;
  $table = "tmp_genre_to_post_map_7_9_2017";
  $myrows = $wpdb->get_results( "SELECT * FROM " . $table);
  $count = 0;
  if ($myrows) {
    foreach ( $myrows as $myrow ){
      $count++;
      echo "# " . $count . " - ";
      $wp_post_id = $myrow->wp_post_id;
      $wp_cat_id = $myrow->wp_cat_id;

      if(!empty($wp_post_id) && $wp_post_id > 0 ){
        if($wp_cat_id > 0) {
        wp_remove_object_terms( $wp_post_id, 'uncategorized', 'category' ); //if we have a cat, then let's remove it from the uncategorized area
        wp_set_post_categories( $wp_post_id, array( $wp_cat_id ), true );
          echo "wp_post_id is: " . $wp_post_id . " cat_id is: ". $wp_cat_id . " <br>";
        } else {
          echo "wp_post_id is: " . $wp_post_id . " but cat was empty.<br>";
        }
      } else {
        echo "wp_post_id was empty or it was: " . $wp_post_id . ".<br>";
      }

    }
  }
}

function set_parent_child_category_relationship() {
    echo '<div id="message" class="updated fade"><p> set_top_categories. </p></div>';
  global $wpdb;

  //get categories
  $myrows = $wpdb->get_results( "SELECT
      tsc.new_wp_cat_id `child_cat_id`,
      tc.new_wp_cat_id `parent_cat_id`
      FROM tmp_secondary_categories_table_7_4_17_2 tsc
      left OUTER join tmp_export_of_top_level_categories_7_4_17 tc on tc.vid = tsc.vid
      ");

  if ($myrows) {
    foreach ( $myrows as $myrow )
    {
      $child_id = (int)($myrow->child_cat_id);
      $parent_id = (int)($myrow->parent_cat_id);
      $tmp_cat = get_category( $child_id );

      $my_cat = array(
        'cat_ID' => $child_id,
        'cat_name' => $tmp_cat->name,
        'category_parent' => $parent_id,
        'taxonomy' => 'category'
        );

      echo "<pre>" . var_dump($my_cat) . "</pre>";

      $my_cat_id = wp_insert_category($my_cat);

      if (is_wp_error($my_cat_id)) {
        echo "there is an error with " . $child_id . "<br>";
          $errors = $my_cat_id->get_error_messages();
          foreach ($errors as $error) {
            echo $error;
            echo '<br>';
          }
      } else {
        echo $my_cat_id . " now has a parent of " . $parent_id . "<br>";
      }

    }
  }
}


function remove_current_categories( $slug ) {

  $cat_obj = get_category_by_slug( $slug );
  $exists = false;

  if( $cat_obj ) {
    $cat_id = $cat_obj->term_id;
    echo $slug . ' - exists. <br>';
    $categories = get_term_children( $cat_id, 'category' );

    if (is_wp_error($categories)) {
      $errors = $categories->get_error_messages();
      foreach ($errors as $error) {
        echo $error;
      }
    } else {

      foreach ( $categories as $child ) {
        $term = get_term_by( 'id', $child, 'category' );
        echo 'deleting: ' . $term->term_id . ' - ' . $term->name . ' - ' . $term->slug . '<br>';
        wp_delete_category( $term->term_id );
      }
    }

    //thene delete the actual parent
    $exists = wp_delete_category( $cat_id );
  }

  if($exists) {
    echo "category: " . $slug . ' was deleted.<br>';
  } else {
    echo "category: " . $slug . ' doesn\'t exist.<br>';
  }

}

// "Creative Non-Fiction", "creative-nonfiction", ''
//"creative-nonfiction", '', "Creative Non-Fiction"
function reset_category_parent( $slug, $parent_slug, $cat_name = '', $new_slug = '') {

  if(! $cat_name ) {
    $cat_obj = get_category_by_slug( $slug );
    $cat_name = $cat_obj->name;
  }

  set_issue_category( $cat_name, $slug, $parent_slug, $new_slug );

}

function one_time_migrate_Features_to_feature() {
  one_time_migrate_category_to_another_category( 'features', 'feature');

}

function shift_genre_bender() {
  if( !term_exists( 'genre-bender', 'category' ) ) {
    reset_category_parent( 'genre-bender', '', 'Genre Bender' );// create category
  }

  one_time_migrate_category_to_another_category( 'genre-bender-2015', 'genre-bender');
  one_time_migrate_category_to_another_category( 'genre-bender-2016', 'genre-bender');
  one_time_migrate_category_to_another_category( 'genre-bender-2017', 'genre-bender');
}

function one_time_migrate_category_to_another_category( $old, $new ) {
/*
 get posts with category features
 then add feature cateogry
 remove the features category
*/
  $args = array(
    'posts_per_page' => -1,
    'tax_query' => [
      'relation' => 'AND',
        [
          'taxonomy' => 'category',
          'field'    => 'slug',
          'terms'    =>  array( $old ),
          'operator' => 'IN' ],
      ]
  );
  $query = new WP_Query( $args );

  $cat_obj = get_category_by_slug( $new );
  $cat_id = $cat_obj->term_id;

  while( $query->have_posts() ) : $query->the_post();
   // $post = $query->posts;
    echo "id = " . get_the_ID();
    echo "title = " . get_the_title();
    echo " - added: ";
    $setcat = wp_set_post_categories( get_the_ID(), array($cat_id), true);
    if (is_wp_error($setcat)) {
        $errors = $setcat->get_error_messages();
        foreach ($errors as $error) {
          echo $error;
        }
      }

    echo " - removed: ";
    $removecat = wp_remove_object_terms( get_the_ID(), $old, 'category' );
        if (is_wp_error($removecat)) {
        $errors = $removecat->get_error_messages();
        foreach ($errors as $error) {
          echo $error;
        }
      }
    echo " <br> ";

  endwhile;
  wp_reset_postdata();

}

function delete_category( $slug_to_delete) {
  $cat_obj = get_category_by_slug( $slug_to_delete );
  $categ_ID = $cat_obj->term_id;
  if ( wp_delete_category( $categ_ID ) ) {
  echo "Category #$categ_ID was successfully deleted";
} else {
  echo "Impossible to delete category #$categ_ID! Make sure it exists and that it's not the default category";
}
}
/*
This is how issues work:
1. the cover story is also the issue page and has a secondary image for the cover.
2. the url for the issue is the category: /issue/month-year
3. the url for the cover story is: /article-title
4. the category for a cover story is cover-story and was the drupal "Featured"
5. the old "featured" article for an issue, is now a cover story
6. the old "features" as mapped from the drupal issue context will have category "month-year"

the actual import will be from a table that is a subset of articles of type issue:
each one points to a featured drupal id.
1. create table of "issues-to-import-as-categories"
2. select all, then, create categories for each one, if it doesn't exist, then, find the wp_post, based on id, that matches, and add the new category id to it.
3. DONE.

*/

/***************************************************************************

Helper functions below

***************************************************************************/

function getQueryParamValue($url, $the_parameter) {
  $query_str = parse_url($url, PHP_URL_QUERY);
  parse_str($query_str, $query_params);
  return $query_params[$the_parameter];

//Output: Array ( [email] => xyz4@test.com [testin] => 123 )
}

function import_meta_content($wp_post_id, $meta_name, $meta_value) {
  if ( ! add_post_meta( $wp_post_id, $meta_name, $meta_value, true ) ) {
    update_post_meta( $wp_post_id, $meta_name, $meta_value );
  }
}

function slug($string, $length = -1, $separator = '-') {
  // transliterate
  //not for ca $string = transliterate($string);

  // lowercase
  //not for ca $string = strtolower($string);

  // replace non alphanumeric and non underscore charachters by separator
  $string = str_replace(".JPG", ".jpg", $string);
  $string = str_replace("+", "", $string);
  $string = str_replace("&", "", $string);
  $string = str_replace("'", "", $string);
  $string = preg_replace('/^_/', '', $string);
  $string = preg_replace('/[(|)|\[|\]]/i', '', $string);

  //$title = preg_replace('/\.[^.]+$/', '', basename($file));

  $string = preg_replace('/[^a-z0-9\._]/i', $separator, $string);

  // replace multiple occurences of separator by one instance
  $string = preg_replace('/'. preg_quote($separator) .'['. preg_quote($separator) .']*/', $separator, $string);

  // cut off to maximum length
  /*//not for ca
  if ($length > -1 && strlen($string) > $length) {
    $string = substr($string, 0, $length);
  }*/

  // remove separator from start and end of string
  $string = preg_replace('/'. preg_quote($separator) .'$/', '', $string);
  $string = preg_replace('/^'. preg_quote($separator) .'/', '', $string);

  return $string;
}

/**
 * Transliterate a given string.
 *
 * @param $string
 *   The string you want to transliterate.
 * @return
 *   A string representing the transliterated version of the input string.
 */
function transliterate($string) {
  static $charmap;
  if (!$charmap) {
    $charmap = array(
      // Decompositions for Latin-1 Supplement
      chr(195) . chr(128) => 'A', chr(195) . chr(129) => 'A',
      chr(195) . chr(130) => 'A', chr(195) . chr(131) => 'A',
      chr(195) . chr(132) => 'A', chr(195) . chr(133) => 'A',
      chr(195) . chr(135) => 'C', chr(195) . chr(136) => 'E',
      chr(195) . chr(137) => 'E', chr(195) . chr(138) => 'E',
      chr(195) . chr(139) => 'E', chr(195) . chr(140) => 'I',
      chr(195) . chr(141) => 'I', chr(195) . chr(142) => 'I',
      chr(195) . chr(143) => 'I', chr(195) . chr(145) => 'N',
      chr(195) . chr(146) => 'O', chr(195) . chr(147) => 'O',
      chr(195) . chr(148) => 'O', chr(195) . chr(149) => 'O',
      chr(195) . chr(150) => 'O', chr(195) . chr(153) => 'U',
      chr(195) . chr(154) => 'U', chr(195) . chr(155) => 'U',
      chr(195) . chr(156) => 'U', chr(195) . chr(157) => 'Y',
      chr(195) . chr(159) => 's', chr(195) . chr(160) => 'a',
      chr(195) . chr(161) => 'a', chr(195) . chr(162) => 'a',
      chr(195) . chr(163) => 'a', chr(195) . chr(164) => 'a',
      chr(195) . chr(165) => 'a', chr(195) . chr(167) => 'c',
      chr(195) . chr(168) => 'e', chr(195) . chr(169) => 'e',
      chr(195) . chr(170) => 'e', chr(195) . chr(171) => 'e',
      chr(195) . chr(172) => 'i', chr(195) . chr(173) => 'i',
      chr(195) . chr(174) => 'i', chr(195) . chr(175) => 'i',
      chr(195) . chr(177) => 'n', chr(195) . chr(178) => 'o',
      chr(195) . chr(179) => 'o', chr(195) . chr(180) => 'o',
      chr(195) . chr(181) => 'o', chr(195) . chr(182) => 'o',
      chr(195) . chr(182) => 'o', chr(195) . chr(185) => 'u',
      chr(195) . chr(186) => 'u', chr(195) . chr(187) => 'u',
      chr(195) . chr(188) => 'u', chr(195) . chr(189) => 'y',
      chr(195) . chr(191) => 'y',
      // Decompositions for Latin Extended-A
      chr(196) . chr(128) => 'A', chr(196) . chr(129) => 'a',
      chr(196) . chr(130) => 'A', chr(196) . chr(131) => 'a',
      chr(196) . chr(132) => 'A', chr(196) . chr(133) => 'a',
      chr(196) . chr(134) => 'C', chr(196) . chr(135) => 'c',
      chr(196) . chr(136) => 'C', chr(196) . chr(137) => 'c',
      chr(196) . chr(138) => 'C', chr(196) . chr(139) => 'c',
      chr(196) . chr(140) => 'C', chr(196) . chr(141) => 'c',
      chr(196) . chr(142) => 'D', chr(196) . chr(143) => 'd',
      chr(196) . chr(144) => 'D', chr(196) . chr(145) => 'd',
      chr(196) . chr(146) => 'E', chr(196) . chr(147) => 'e',
      chr(196) . chr(148) => 'E', chr(196) . chr(149) => 'e',
      chr(196) . chr(150) => 'E', chr(196) . chr(151) => 'e',
      chr(196) . chr(152) => 'E', chr(196) . chr(153) => 'e',
      chr(196) . chr(154) => 'E', chr(196) . chr(155) => 'e',
      chr(196) . chr(156) => 'G', chr(196) . chr(157) => 'g',
      chr(196) . chr(158) => 'G', chr(196) . chr(159) => 'g',
      chr(196) . chr(160) => 'G', chr(196) . chr(161) => 'g',
      chr(196) . chr(162) => 'G', chr(196) . chr(163) => 'g',
      chr(196) . chr(164) => 'H', chr(196) . chr(165) => 'h',
      chr(196) . chr(166) => 'H', chr(196) . chr(167) => 'h',
      chr(196) . chr(168) => 'I', chr(196) . chr(169) => 'i',
      chr(196) . chr(170) => 'I', chr(196) . chr(171) => 'i',
      chr(196) . chr(172) => 'I', chr(196) . chr(173) => 'i',
      chr(196) . chr(174) => 'I', chr(196) . chr(175) => 'i',
      chr(196) . chr(176) => 'I', chr(196) . chr(177) => 'i',
      chr(196) . chr(178) => 'IJ', chr(196) . chr(179) => 'ij',
      chr(196) . chr(180) => 'J', chr(196) . chr(181) => 'j',
      chr(196) . chr(182) => 'K', chr(196) . chr(183) => 'k',
      chr(196) . chr(184) => 'k', chr(196) . chr(185) => 'L',
      chr(196) . chr(186) => 'l', chr(196) . chr(187) => 'L',
      chr(196) . chr(188) => 'l', chr(196) . chr(189) => 'L',
      chr(196) . chr(190) => 'l', chr(196) . chr(191) => 'L',
      chr(197) . chr(128) => 'l', chr(197) . chr(129) => 'L',
      chr(197) . chr(130) => 'l', chr(197) . chr(131) => 'N',
      chr(197) . chr(132) => 'n', chr(197) . chr(133) => 'N',
      chr(197) . chr(134) => 'n', chr(197) . chr(135) => 'N',
      chr(197) . chr(136) => 'n', chr(197) . chr(137) => 'N',
      chr(197) . chr(138) => 'n', chr(197) . chr(139) => 'N',
      chr(197) . chr(140) => 'O', chr(197) . chr(141) => 'o',
      chr(197) . chr(142) => 'O', chr(197) . chr(143) => 'o',
      chr(197) . chr(144) => 'O', chr(197) . chr(145) => 'o',
      chr(197) . chr(146) => 'OE', chr(197) . chr(147) => 'oe',
      chr(197) . chr(148) => 'R', chr(197) . chr(149) => 'r',
      chr(197) . chr(150) => 'R', chr(197) . chr(151) => 'r',
      chr(197) . chr(152) => 'R', chr(197) . chr(153) => 'r',
      chr(197) . chr(154) => 'S', chr(197) . chr(155) => 's',
      chr(197) . chr(156) => 'S', chr(197) . chr(157) => 's',
      chr(197) . chr(158) => 'S', chr(197) . chr(159) => 's',
      chr(197) . chr(160) => 'S', chr(197) . chr(161) => 's',
      chr(197) . chr(162) => 'T', chr(197) . chr(163) => 't',
      chr(197) . chr(164) => 'T', chr(197) . chr(165) => 't',
      chr(197) . chr(166) => 'T', chr(197) . chr(167) => 't',
      chr(197) . chr(168) => 'U', chr(197) . chr(169) => 'u',
      chr(197) . chr(170) => 'U', chr(197) . chr(171) => 'u',
      chr(197) . chr(172) => 'U', chr(197) . chr(173) => 'u',
      chr(197) . chr(174) => 'U', chr(197) . chr(175) => 'u',
      chr(197) . chr(176) => 'U', chr(197) . chr(177) => 'u',
      chr(197) . chr(178) => 'U', chr(197) . chr(179) => 'u',
      chr(197) . chr(180) => 'W', chr(197) . chr(181) => 'w',
      chr(197) . chr(182) => 'Y', chr(197) . chr(183) => 'y',
      chr(197) . chr(184) => 'Y', chr(197) . chr(185) => 'Z',
      chr(197) . chr(186) => 'z', chr(197) . chr(187) => 'Z',
      chr(197) . chr(188) => 'z', chr(197) . chr(189) => 'Z',
      chr(197) . chr(190) => 'z', chr(197) . chr(191) => 's',
      // Euro Sign
      chr(226) . chr(130) . chr(172) => 'E'
    );
  }

  // transliterate
  return strtr($string, $charmap);
}

function is_slug($str) {
  return $str == slug($str);
}




