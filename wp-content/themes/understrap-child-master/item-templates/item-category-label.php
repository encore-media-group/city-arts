
<?php

$categories = get_the_category();

$url = "/";
$name = "City Arts";

$cover_story_cat_id = get_cached_cat_id_by_slug('cover-story');
$issue_cat_id = get_cached_cat_id_by_slug('issue');

if ( ! empty($categories) ) :
  $disciplines = get_disciplines();
  $small_categories = $categories;

  foreach ($categories as $key=>$category) {
    $match_index = array_search( $category->slug, $disciplines);

    if( $match_index !== false ) {
      unset($small_categories[$key]); //remove any categories for this post that match the core disciplines
    }
    if ( $category->term_id === $cover_story_cat_id) {
      unset($small_categories[$key]);
    }
    if ( $category->parent === $issue_cat_id) {
      unset($small_categories[$key]);
    }

  }

  $small_categories = array_values($small_categories);

  if( sizeof($small_categories) > 0
    ? $category_output = $small_categories[0]
    : $category_output = $categories[0]
    );

  $url =  esc_url( get_category_link( $category_output->term_id ) );
  $name = esc_html(  $category_output->name );

endif;
?>

<a class="url fn n" href="<?php echo $url ?>"><span class="category-label align-middle text-center mb-2 px-2 py-1"><?php echo $name ?></span><a>









