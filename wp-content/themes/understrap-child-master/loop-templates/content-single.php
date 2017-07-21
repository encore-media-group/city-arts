<?php
/**
 * Single post partial template.
 *
 * @package understrap
 */

?>
<?php
$thumbnail = wp_get_attachment_image_src( get_post_thumbnail_id( $post->ID ), "full" );
$thumbnail_url = $thumbnail[0];
?>
<article <?php post_class('row'); ?> id="post-<?php the_ID(); ?>">

	<div class="single-post-image-hero col-lg-6" style="background-image: url('<?php echo $thumbnail_url; ?>');">

	</div>
	<div class="col-lg-6">
			<header class="entry-header row">
				<?php the_title( '<h1 class="entry-title">', '</h1>' ); ?>
					<div class="entry-meta"><?php understrap_posted_on(); ?></div><!-- .entry-meta -->
					<?php the_category( ' | ' ); ?>
				</header><!-- .entry-header -->
	<div class="entry-content row">
		<?php the_content(); ?>
		<div class="attached-images">
			<ul>
			<?php
			$images = get_attached_media('image', $post->ID);

			foreach($images as $image) { ?>
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
