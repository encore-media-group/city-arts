<?php
/**
 * The template for displaying archive pages.
 *
 * Learn more: http://codex.wordpress.org/Template_Hierarchy
 *
 * @package understrap
 */

get_header();
?>

<?php

$archive_slug =  get_queried_object()->slug;

$is_discipline_archive = in_array($archive_slug, get_disciplines() ) ?: false;

$is_discipline_archive_class = ( $is_discipline_archive ) ? ' discipline-page ' : '' ;

$paged = ( get_query_var('paged') ) ? get_query_var('paged') : 1;

$offset = -1;
$ppp = 16;
$page_offset = null;

if( $paged > 1  && $is_discipline_archive ) :
  $page_offset = $offset + ( ( $paged - 1 ) * $ppp );

	add_filter( 'found_posts', 'ca_adjust_offset_pagination', 1, 2 );
	function ca_adjust_offset_pagination( $found_posts, $query ) {
		$offset = 0;
		if ( $query->is_paged ) :
			$offset = 1;
		endif;
		return $found_posts - $offset;
	}
endif;

$the_query = new WP_Query(array(
    'posts_per_page' => $ppp,
    'offset' => $page_offset,
    'paged' => $paged,
    'tax_query' => [
            [
                'taxonomy' => 'category',
                'field'    => 'slug',
                'terms'    =>  array( $archive_slug ),
                'include_children' => true,
        				'operator' => 'IN'
            ],
        ],
    'meta_query' => Calendar::meta_query_hide_calendar_posts(),
));

?>

<div class="wrapper <?= $is_discipline_archive_class ?> " id="archive-wrapper">
	<main  class="site-main" id="main">
		<div class="container" id="content" tabindex="-1">
			<div class="row pt-4">
				<div class="col">
					<?php
					if ( have_posts() ) : ?>
							<!-- .page-header -->
							<?php if ( $paged < 2 && $is_discipline_archive ) : ?>
								<header class="page-header row">
									<h1 class=" col page-title discipline-title text-center pb-3"> <?php single_cat_title() ?> </h1>
								</header>

							<?php elseif ( $paged < 2 && !$is_discipline_archive ) : ?>
								<header class="page-header row">
									<div class="col-12">
										<h2 class="page-title pb-4 sidelines"> <?php single_cat_title() ?> </h2>
									</div>
									<div class="col-12">
										<div class="row justify-content-center">
											<?php the_archive_description( '<div class="col-12 col-lg-10 pb-4 taxonomy-description">', '</div>' ); ?>
										</div>
									</div>
							</header><!-- .page-header -->
							<?php else: ?> <!-- page greater than 2 -->
								<header class="page-header row w-md-75 mx-auto">
									<h2 class="col page-title pb-4 sidelines"> <?php single_cat_title() ?> </h2>
								</header>
							<?php endif; ?>

						<?php
							$count = 1;
						  echo '<div class="row">';
	            while( $the_query->have_posts() ) : $the_query->the_post();
	            	if( $is_discipline_archive && $paged == 1 ) :
									if ( $count == 1 ) :
							 			get_template_part( 'item-templates/item', '730x487-horizontal' );
							 			echo '</div><!-- end row --><div class="row pt-4">';
									elseif ($count == 2  ):
										set_query_var( 'show_byline', true );
							 			get_template_part( 'item-templates/item', '320x213' );
									elseif ($count == 3  ):
										set_query_var( 'show_byline', true );
							 			get_template_part( 'item-templates/item', '320x213' );
							 			echo '</div><!-- end row -->';
							 	?>
							 			</div><!--col-->
							 		</div><!--row-->
							 	</div><!--container-->
							 	<div class="container-fluid ad-container">
					 					<?php echo ad_728xlandscape_shortcode(); ?>
					 			</div>
							 	<div class="container">
							 		<div class="row pt-4">
										<div class="col">

					 			<?php
							 			echo '<div class="row">';
							 		elseif( $count <= 15 ): ?>
										<div class="col-12 col-sm-6 col-lg-3">
											<?php get_template_part( 'item-templates/item', '255x170' ); ?>
										</div>
										<?php if ($count >= 4 && $count % 4 == 0) : echo '<div class="m-100"></div>'; endif; ?>
							<?php
	            		endif;
								else:
							?>
									<div class="col-12 col-sm-6 col-lg-3">
										<?php get_template_part( 'item-templates/item', '255x170' ); ?>
									</div>
									<?php if ($count >= 4 && $count % 4 == 0) : echo '<div class="m-100"></div>'; endif; ?>
							<?php
								endif;

								$count++;
							endwhile;
							wp_reset_postdata();
						endif;
						?>

					</div><!--row-->
				</div>
			</div> <!-- .row -->
		</div><!-- container end -->
		<div class="container">
			<div class="row justify-content-center">
				<div class="col-auto">
				<!-- The pagination component -->
				<?php
				understrap_pagination( $the_query );
				?>
				</div>
			</div>
		</div><!-- container end -->
	</main><!-- #main -->
</div><!-- Wrapper end -->

<?php get_footer(); ?>
