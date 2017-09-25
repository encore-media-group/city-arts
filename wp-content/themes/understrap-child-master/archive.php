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
$container   = get_theme_mod( 'understrap_container_type' );
$sidebar_pos = get_theme_mod( 'understrap_sidebar_position' );

$archive_slug =  get_queried_object()->slug;

$is_discipline_archive = in_array($archive_slug, get_disciplines() ) ?: false;

$paged = ( get_query_var('paged') ) ? get_query_var('paged') : 1;

if ($paged < 2) : $posts_per_page = 15; else: $posts_per_page = 16; endif;

$the_query = new WP_Query(array(
    'posts_per_page' => $posts_per_page,
  //  'meta_query' => array( array('key' => '_thumbnail_id' ) ),
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
));



?>

<div class="wrapper" id="archive-wrapper">

	<div class="<?php echo esc_attr( $container ); ?>" id="content" tabindex="-1">

		<div class="row">

			<main class="col site-main" id="main">

				<?php if ( have_posts() ) : ?>

						<?php if ( $paged < 2 && $is_discipline_archive ) : ?>
							<header class="page-header row">
								<h1 class=" col page-title text-center pb-4"> <?php single_cat_title() ?> </h1>
						<?php endif; ?>

						<?php if ( !$is_discipline_archive ) : ?>
							<header class="page-header row w-75 mx-auto">
								<h2 class="col page-title pb-4 sidelines"> <?php single_cat_title() ?> </h2>
						<?php endif; ?>

						<?php if ( $paged < 2 && !$is_discipline_archive ) : ?>
							<header class="page-header row">
								<div class="col">
									<div class="row justify-content-center">
										<?php the_archive_description( '<div class="col-12 col-lg-10 pb-4 taxonomy-description">', '</div>' ); ?>
									</div>
								</div>
						<?php endif; ?>
						</header><!-- .page-header -->

					<?php
						$count = 1;
					  echo '<div class="row">';
            while( $the_query->have_posts() ) : $the_query->the_post();
							if ( $count == 1 && $paged < 2 && $is_discipline_archive ) :
						 		get_template_part( 'item-templates/item', '730x487-horizontal' );
						 		echo '</div><!-- end row --><div class="row pt-4">';
							elseif ($count == 2 && $paged < 2 && $is_discipline_archive ):
						 		get_template_part( 'item-templates/item', '320x213' );
							elseif ($count == 3 && $paged < 2 && $is_discipline_archive ):
						 		get_template_part( 'item-templates/item', '320x213' );
						 		echo '</div><!-- end row -->';
				 				get_template_part( 'item-templates/item', 'landscape-ad' );
						 		echo '<div class="row">';
							else:
						?>
						<div class="col-12 col-sm-6 col-lg-3">
							<?php get_template_part( 'item-templates/item', '255x170' ); ?>
						</div>
						<?php if ($count > 4 && $count % 4 == 0) : echo '<div class="m-100"></div>'; endif; ?>
						<?php
							endif;
							$count++;
							endwhile;
							wp_reset_postdata();
							echo "</div><!-- end row -->";
						else :
							get_template_part( 'loop-templates/content', 'none' );
						endif;
					?>

				</div><!--row-->
			</main><!-- #main -->

		</div><!-- #primary -->

	</div> <!-- .row -->

</div><!-- Container end -->

<divi class="container">
	<div class="row justify-content-center">
		<div class="col-auto">
		<!-- The pagination component -->
		<?php understrap_pagination(); ?>
		</div>
	</div>
</divi>

</div><!-- Wrapper end -->

<?php get_footer(); ?>
