<?php
/**
 * Single post partial template.
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

$thumbnail_caption = get_post($thumbnail_id)->post_excerpt; ?>

<article <?php post_class('row'); ?> id="post-<?php the_ID(); ?>">

	<div class="col-sm-6 col-lg-6">
		<div class="single-post-image-hero " style="padding-bottom: <?php echo $thumbnail_height; ?>px;background-image: url('<?php echo $thumbnail_url; ?>');"></div>
		<div><?php echo $thumbnail_caption ?></div>
		<div>
		<?php dynamic_sidebar( 'article-left-1' ); ?>
			<?php if ( is_active_sidebar( 'article-1eft-1' ) ) : ?>
				<div id="article-left-1-sidebar" class="primary-sidebar widget-area" role="complementary">
					<?php dynamic_sidebar( 'article-left-1' ); ?>
				</div><!-- #primary-sidebar -->
			<?php endif; ?>
		</div>
	</div>



	<div class="col-sm-6 col-lg-6">
			<header class="entry-header row">
				<?php the_title( '<h1 class="entry-title">', '</h1>' ); ?>
					<div class="entry-meta"><?php understrap_posted_on(); ?></div><!-- .entry-meta -->
					<?php the_category( ' | ' ); ?>
				</header><!-- .entry-header -->
	<div class="entry-content row">
		<?php the_content(); ?>

		<?php
		$postbody = get_the_content();
		get_images_from_post($postbody);

    function get_images_from_post($tmpPost) {
      /* parse the contents of the post and extract image urls */
      $post_images = array();
      libxml_use_internal_errors(true);
      $doc = new DOMDocument();

        $doc->loadHTML($tmpPost);
        $xml=simplexml_import_dom($doc);
        $images=$xml->xpath('//img');

        foreach ($images as $img) {
          if(strpos($img['src'], 'http') !== true ){
            $post_images[] = $img['src'];
            echo basename($img['src']) . "<br>";
          }
        }

      return $post_images;
    }

		?>
		<div class="attached-images">
			<ul>
			<?php
			$images = get_attached_media('image', $post->ID);

			foreach($images as $image) {
					echo var_dump($image);
					?>
			    <li><img src="<?php echo wp_get_attachment_image_src($image->ID,'medium')[0]; ?>" /></li>
			<?php } ?>
			</ul>
		</div>
		<?php
		wp_link_pages( array(
			'before' => '<div class="page-links">' . __( 'Pages:', 'understrap' ),
			'after'  => '</div>',
		) );
		?>

	</div><!-- .entry-content -->
	</div><!--end col-lg-6-->
	<footer class="entry-footer">

		<?php understrap_entry_footer(); ?>

	</footer><!-- .entry-footer -->

</article><!-- #post-## -->
