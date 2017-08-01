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
  //set_contributors(); //do 1
  //set_articles(); //do 2 NOTE: ADD ADDITION OF PAGES- TODO
  // !!!! before you run sync, you have to run an update sql statement against the article table with the post id for that author.
  //sync_posts_to_writers();//do 3 NOTE: are you using the correct ACF value?? make sure you are!!!!
  //set_top_categories(); //do 4
  //set_secondary_categories(); //do 5
  //set_parent_child_category_relationship(); //do 6
  // import pages by calling set_articles and update to be "pages" //do 7
  //set_excerpts(); //do 8
  //sync_wp_post_id_to_image_inline_images();
  update_image_urls_in_posts(); // do 9

    /*
    HOW TO IMPORT AND ATTACH IMAGES
      temp way to show some images:
      update wpsa_posts
      SET post_content = REPLACE ( post_content, '/sites/default/files/inline_images/', 'http://cityartsmagazine.com/sites/default/files/inline_images/')
      WHERE  post_content LIKE '%/sites/default/files/inline_images/%'

      overall image processing:
      - take update from master production database
      - run querey to export slideshow
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
  $myrows = $wpdb->get_results( "SELECT * FROM " . $table . " where post_content !='' and post_status = 'publish'");
  // limit 0, 5000000");
  return $myrows;
}
function swap_images_from_post($post) {
  $ID = $post->ID;
  $content_is_updated = false;
  /* parse the contents of the post and extract image urls */
  $attached_images = array();
  $attached_images = get_images_attached_to_this_post($ID);

  $post_thumbnail_id = get_post_thumbnail_id( $ID ); //we want to know what the featured image is.

  $post_images = array();
  libxml_use_internal_errors(true);
  $doc = new DOMDocument();
  $doc->loadHTML(mb_convert_encoding($post->post_content, 'HTML-ENTITIES', 'UTF-8'), LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
  //$doc->loadHTML($post->post_content, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD | LIBXML_PARSEHUGE );
  $doc->encoding = 'UTF-8';
  $xml=simplexml_import_dom($doc);
  $images=$xml->xpath('//img');

    foreach ($images as $img) {


      if(strpos($img['src'], 'wp-content') === false ){
        echo "postid: " . $post->ID . " - ";
        echo "found:  " . $img['src'] . " <br>";
        echo "(slug): " . slug (rawurldecode( basename( $img['src'] ) ) ). " <br>";
        echo "vardump <pre>" . var_dump($attached_images) . "</pre>";
        $match_index = array_search( slug(rawurldecode( basename( $img['src'] ) ) ), $attached_images  );
        if($match_index !== false) {
          if( $match_index >= 0) {
            $img['class'] = "";
            $img['height'] = "";
            $img['width'] = "";
            $img['src'] = "/wp-content/uploads/" . $attached_images[$match_index];

            echo " and a match found for: " . $img['src'] . ".<br>";
            $content_is_updated = true;
          } else {
            echo "no match for ". $img['src'] . "<br>";
          }
        } else {
          echo " but not attached.<br>";
        }
      }
    }

    $trim_off_front = strpos($doc->saveHTML(),'<body>') + 6;
    $trim_off_end = (strrpos($doc->saveHTML(),'</body>')) - strlen($doc->saveHTML());

    $content_out = substr($doc->saveHTML(), $trim_off_front, $trim_off_end);


    //$content_out = $doc->saveHTML();
    return array(
      'content_is_updated' => $content_is_updated,
      'post_content' => $content_out);
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


function sync_wp_post_id_to_image_inline_images(){
  /* this was the original way to do it, but it was slow. now I used a que based system. */
  /*  run this first:
      we need to run this so that the img list has the wp post id shared.

      update tmp_inline_image_list til, tmp_article_export_7_9_2017 tae
      set til.new_wp_post_id = tae.new_wp_id
      where til.nid = tae.nid

  */

  global $wpdb;
  $table = "tmp_inline_image_list";
  $myrows = $wpdb->get_results( "SELECT * FROM " . $table . ' limit 0, 20 ');

  require_once(ABSPATH . '/wp-admin/includes/file.php');
  require_once(ABSPATH . '/wp-admin/includes/media.php');
  require_once(ABSPATH . '/wp-admin/includes/image.php');
  $upload_dir = MEDIAFROMFTP_PLUGIN_UPLOAD_DIR;
  echo 'the path: ' . $upload_dir . "<br>";




  $count = 0;

  if ($myrows) {
    foreach ( $myrows as $myrow ) {
      $count++;
      echo "# " . $count . " - ";
      $new_wp_post_id = $myrow->new_wp_post_id;
      $inline_image_title = $myrow->field_inline_images_title;
      $filename = $myrow->filename;
      $file_path_and_name = $upload_dir . '/inline_images/' . $filename;
      $delta = $myrow->delta;
      $image_caption = $myrow->field_inline_images_title;
      $image_caption2 = $myrow->field_inline_images_alt;
      if($image_caption == '') { $image_caption = $image_caption2; }

      echo "exists? " . is_file($file_path_and_name) . " filename: " . $file_path_and_name  . "<br>";
      if(!is_file($file_path_and_name)) {
        echo "FILE NOT FOUND: " . $file_path_and_name . "<br>";
      } else {
          echo "FILE FOUND: " . $file_path_and_name . "<br>";

        //echo '<pre> ' . var_dump($myrow ). '</pre>';

        $array = array( //array to mimic $_FILES
          'name' => basename($file_path_and_name), //isolates and outputs the file name from its absolute path
          'type' => wp_check_filetype($file_path_and_name), // get mime type of image file
          'tmp_name' => $file_path_and_name, //this field passes the actual path to the image
          'error' => 0, //normally, this is used to store an error, should the upload fail. but since this isnt actually an instance of $_FILES we can default it to zero here
          'size' => filesize($file_path_and_name) //returns image filesize in bytes
        );

        echo '<pre>' . var_dump($array) . '</pre>';

        $attachment_id = media_handle_sideload($array, $new_wp_post_id); //the actual image processing, that is, move to upload directory, generate thumbnails and image sizes and writing into the database happens here

        if (is_wp_error($attachment_id)) {
            $errors = $attachment_id->get_error_messages();
            foreach ($errors as $error) {
              echo $error . "<br>";
            }
            echo "<p>";
          } else {
            echo' Aattachment id: ' . $attachment_id . '<br>';
            echo 'all good - ';

            if($delta == 0) { set_post_thumbnail( $new_wp_post_id, $attachment_id ); }

            if($image_caption != '') {
              $attachment = array( 'ID' => $attachment_id, 'post_excerpt' => $image_caption );

              wp_update_post(array('ID' => $attachment_id, 'post_excerpt' => $image_caption));
              echo "wp_insert_attachment for: " . $attachment_id . " and post_parent: " . $new_wp_post_id . " with caption: ".  $image_caption ."<br>";
            }




            $wpdb->query('UPDATE tmp_inline_image_list SET new_wp_attachment_id = ' . $attachment_id .  ' WHERE new_wp_post_id= ' . $new_wp_post_id);
          }
      }
    }
  }
}

function sync_single_image_wp_post_id_to_image_inline_images($myrow){
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
      $new_wp_post_id = $myrow->new_wp_post_id;
      $inline_image_title = $myrow->field_inline_images_title;
      $filename = $myrow->filename;
      $uri = $myrow->uri;
//      $file_path_and_name = $upload_dir . '/inline_images/' . $filename;
        $file_path_and_name = $upload_dir . '/inline_images/' . str_replace("public://inline_images/","",$uri);

      $delta = $myrow->delta;
      $image_caption = $myrow->field_inline_images_title;
      $image_caption2 = $myrow->field_inline_images_alt;
      if($image_caption == '') { $image_caption = $image_caption2; }

      if(!is_file($file_path_and_name)) {
        $output .=  "FILE NOT FOUND: " . $file_path_and_name . "<br>";
      } else {
        $output .=  "FILE FOUND: " . $file_path_and_name . "<br>";

        //echo '<pre> ' . var_dump($myrow ). '</pre>';

        $array = array( //array to mimic $_FILES
          'name' => basename($file_path_and_name), //isolates and outputs the file name from its absolute path
          'type' => wp_check_filetype($file_path_and_name), // get mime type of image file
          'tmp_name' => $file_path_and_name, //this field passes the actual path to the image
          'error' => 0, //normally, this is used to store an error, should the upload fail. but since this isnt actually an instance of $_FILES we can default it to zero here
          'size' => filesize($file_path_and_name) //returns image filesize in bytes
        );

     //   $output .=   '<pre>' .  var_dump($array) . '</pre>';

        $attachment_id = media_handle_sideload($array, $new_wp_post_id); //the actual image processing, that is, move to upload directory, generate thumbnails and image sizes and writing into the database happens here

        if (is_wp_error($attachment_id)) {
            $errors = $attachment_id->get_error_messages();
            foreach ($errors as $error) {
            $output .=  $error . "<br>";
            }
            $output .=  "<p>";
          } else {
            $output .= ' Aattachment id: ' . $attachment_id . '<br>';

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

function sync_posts_to_writers() {
  global $wpdb;
  $table = "tmp_article_export_7_9_2017";
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
  global $wpdb;
  $myrows = $wpdb->get_results( "SELECT * FROM tmp_author_post");

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

            $wpdb->query('UPDATE tmp_author_post SET new_wp_id = ' . $post_id .  ' WHERE nid = ' . $drupal_id);
            echo 'success on insert of drupal_id: ' . $drupal_id . ' and wp_post_id' . $post_id .'<br>';
          } else {
            echo 'error on insert of drupal_id: ' . $drupal_id . "<br>";
          }
        }
      }
    }
}

function set_articles() {
  global $wpdb;

//  $table = "tmp_article_export_7_9_2017";
  $table = "tmp_page_export_7_10_2017";
  $myrows = $wpdb->get_results( "SELECT * FROM " . $table);
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
          //$post_long_teaser_value = $myrow->post_long_teaser_value;
          $post_content = $myrow->post_content;
          $post_status = $myrow->post_status;
          $guid = 'http://71672.com/uncategorized/' . $post_slug;
          if($myrow->post_type == 'page') {
            $post_content = $myrow->body_value;
          }
          //$post_author = $myrow->post_author;
          //$post_category = [9];

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
              //'post_excerpt' => $post_long_teaser_value,
              'post_content' => 'temp content', //we do this as some post have empty content..you can't create a post with empty content, but you can update it to be empty.
              'filter' => true,
              'post_type'   =>  'page' //'post'
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

function set_excerpts() {
  global $wpdb;

  $table = "tmp_article_export_7_9_2017";
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
  Update tmp_genre_to_post_map_7_9_2017 gpm, tmp_article_export_7_9_2017 ae
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
