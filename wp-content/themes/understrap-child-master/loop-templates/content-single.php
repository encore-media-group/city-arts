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
<article <?php post_class(); ?> id="post-<?php the_ID(); ?>">

	<div class="single-post-image-hero" style="background-image: url('<?php echo $thumbnail_url; ?>');">
	<?php //echo get_the_post_thumbnail( $post->ID, 'large' ); ?>
	</div>
		<header class="entry-header">

		<?php the_title( '<h1 class="entry-title">', '</h1>' ); ?>

		<div class="entry-meta">

			<?php understrap_posted_on(); ?>


		</div><!-- .entry-meta -->
		<?php the_category( ' | ' ); ?>

	</header><!-- .entry-header -->
	<div class="entry-content">
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

	<footer class="entry-footer">

		<?php understrap_entry_footer(); ?>

	</footer><!-- .entry-footer -->

</article><!-- #post-## -->
