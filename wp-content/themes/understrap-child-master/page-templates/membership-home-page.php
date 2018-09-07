<?php
/**
 * Template Name: Membership Home Template
 *
 *
 * @package understrap
 */

get_header();

while ( have_posts() ) : the_post();
	$thumbnail_id = get_post_thumbnail_id( $post->ID );

	$img_src = wp_get_attachment_image_url( $thumbnail_id, 'ca-1140-760' );
	$img_srcset = wp_get_attachment_image_srcset( $thumbnail_id, 'ca-1140-760' );
?>

<div class="container-fluid" tabindex="-1">
	<div class="row">
	  <div class="col-12 px-0">
	  	<div class="membership-image-wrapper" style="background-image: url('<?= $img_src ?>');">
	    <img
	     src="<?php echo esc_url( $img_src ); ?>"
	     srcset="<?php echo esc_attr( $img_srcset ); ?>"
	     sizes="(max-width: 46em) 100vw, 730px"
	     style="max-width:100%;height:auto; display: none;"
	     class="img-fluid"
	     alt="">
	   </div>
	  </div>
	 </div>
</div>
<div class="container membership-page" id="content" tabindex="-1">
  <div class="row">
    <article <?php post_class('col'); ?> id="post-<?php the_ID(); ?>">
 <!-- <div class="row">
        <header class="entry-header col col-md-9"><?php the_title( '<h1 class="entry-title">', '</h1>' ); ?> </header>
      </div>
  -->
      <div class="row">
        <div class="col-12 col-md-10 pt-4 mx-auto">
    			<div class="article-content">
    				<h1 class="entry-title">City Arts Memberships</h1>
              <?php the_content(); ?>
    			</div> <!-- .article-content -->
        </div>
      </div><!-- row -->
    </article><!-- #post-## -->
  </div><!-- .row -->
</div><!-- Container end -->

  	<?php endwhile; // end of the loop. ?>

<?
get_footer();
