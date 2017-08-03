<?php
/**
 * Single item large partial template.
 *
 * @package understrap
 */

?>
<?php
$thumbnail_id = get_post_thumbnail_id( $post->ID );
$thumbnail = wp_get_attachment_image_src( $thumbnail_id, "full" );

$thumbnail_url = $thumbnail[0];
$thumbnail_width = $thumbnail[1];
$thumbnail_height = $thumbnail[2];

$thumbnail_caption = get_post($thumbnail_id)->post_excerpt;
?>
  <div class="large-item">
    <div class="image"><img src="<?php echo $thumbnail_url; ?>" style=" height: 500px; width: 750px; "></div>
    <div class="item-content-container">
      <div class="caption"><?php echo $thumbnail_caption ?></div>
      <div class="contributors"> <?php echo understrap_posted_on(); ?></div>
      <div class="category-label"><span>Category</span></div>
      <h1><a href="<?php the_permalink() ?>" rel="bookmark"><?php the_title(); ?></a></h1>
      <div class="excerpt"><?php echo $post->post_excerpt; ?></div>
    </div>
  </div>
