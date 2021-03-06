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

$page_title = '';

if ( is_day() ) :
	$page_title = sprintf( __( 'Day: %s', 'understrap' ), '<span>' . get_the_date() . '</span>' );
elseif ( is_month() ) :
	$page_title = sprintf( __( 'Month: %s', 'understrap' ), '<span>' . get_the_date( _x( 'F Y', 'monthly archives date format', 'understrap' ) ) . '</span>' );
elseif ( is_year() ) :
	$page_title = sprintf( __( 'Year: %s', 'understrap' ), '<span>' . get_the_date( _x( 'Y', 'yearly archives date format', 'understrap' ) ) . '</span>' );
endif;
?>

<div class="wrapper" id="archive-wrapper">
	<main  class="site-main" id="main">
		<div class="<?php echo esc_attr( $container ); ?>" id="content" tabindex="-1">
			<div class="row pt-4">
				<div class="col">
						<?php if ( have_posts() ) : ?>
							<header class="page-header row w-md-75 mx-auto">
								<h2 class="col page-title pb-4 sidelines"> <?= $page_title; ?> </h2>
							</header><!-- .page-header -->
						<?php endif; ?>
						<div class="row">
							<?php
							$count = 1;
	            while( have_posts() ) : the_post();
								if ( $count == 1 && $paged < 2  ) :
							 		get_template_part( 'item-templates/item', '730x487-horizontal' );
							 		echo '</div><!-- end row --><div class="row pt-4">';
								elseif ($count == 2 && $paged < 2  ):
									set_query_var( 'show_byline', true );
							 		get_template_part( 'item-templates/item', '320x213' );
								elseif ($count == 3 && $paged < 2  ):
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
					<div class="row">
					 	<?php else: ?>
							<div class="col-12 col-sm-6 col-lg-3">
								<?php get_template_part( 'item-templates/item', '255x170' ); ?>
							</div>
							<?php if ($count > 4 && $count % 4 == 0) : echo '<div class="m-100"></div>'; endif; ?>
						<?php
							endif;
							$count++;
						endwhile;
						wp_reset_postdata();
					?>
					</div><!-- row -->
				</div><!-- col -->
			</div><!-- row -->
		</div><!-- container -->
		<div class="container">
			<div class="row justify-content-center">
				<div class="col-auto">
				<!-- The pagination component -->
				<?php understrap_pagination(); ?>
				</div>
			</div>
		</div>
	</main><!-- #main -->
</div><!-- Wrapper end -->

<?php get_footer(); ?>
