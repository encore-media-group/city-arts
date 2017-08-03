<?php
/**
 * Single item large partial template.
 *
 * @package understrap
 */

?>
<?php
$thumbnail_id = get_post_thumbnail_id( $post->ID );
$thumbnail = wp_get_attachment_image_src( $thumbnail_id, "small" );

$thumbnail_url = $thumbnail[0];
$thumbnail_width = $thumbnail[1];
$thumbnail_height = $thumbnail[2];

$thumbnail_caption = get_post($thumbnail_id)->post_excerpt;
?>
  <div class="small-item">
    <div class="image"><img src="<?php echo $thumbnail_url; ?>" style=" width: 160px; height: 120px; "></div>
    <div class="category-label"><span>Category</span></div>
    <h4><a href="<?php the_permalink() ?>" rel="bookmark"><?php the_title(); ?></a></h4>
    <div class="contributors"><?php echo get_contributors() ?></div>

  </div>
