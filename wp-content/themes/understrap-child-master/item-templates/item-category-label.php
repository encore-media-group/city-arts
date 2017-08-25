
<?php

$categories = get_the_category();
$url = "/";
$name = "City Arts";

if ( ! empty($categories) ) :
  $disciplines = get_disciplines();
  $small_categories = $categories;

  foreach ($categories as $key=>$category) {
    $match_index = array_search( $category->slug, $disciplines);

    if( $match_index !== false ) {
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

<span class="category-label align-middle  mb-2 px-2 py-1"><?php echo '<a class="url fn n" href="' . $url . '">' . $name . '</a>'; ?></span>
