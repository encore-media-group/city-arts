<?php
/**
 * Template Name: Calendar Template
 *
 *
 * @package understrap
 */

get_header();

$genre_slug = get_query_var('cal');

$issue_slug = ((get_query_var('issue-month')) &&  (get_query_var('issue-year'))) ? sprintf('%1$s-%2$s', get_query_var('issue-month'), get_query_var('issue-year')) : "";

$is_calendar_archive = ( $issue_slug ) ? true : false;

$cats = [];
$cats[] = ( in_array( $genre_slug, get_disciplines() )) ? $genre_slug : "";
$cats[] = ( is_array( term_exists( $issue_slug, "category") )) ? $issue_slug : "";
$cats = array_filter($cats);

if( $genre_slug ) :
	//genre is a singular post
	$genre_posts = Calendar::get_calendar_posts( 1, $cats );
	$genre_posts_arr = $genre_posts->posts;

	foreach ($genre_posts_arr as $genre_post) :
		set_query_var ('this_post', $genre_post );
		get_template_part( 'loop-templates/content', 'article-past-parent' );
	endforeach;
	wp_reset_postdata();

else:
	//this is a calendar page with a list of posts for that month
	if ( $is_calendar_archive ) :
		while ( have_posts() ) : the_post();
			set_query_var ('cats', $cats );
			set_query_var ('is_calendar_archive', $is_calendar_archive );
			get_template_part( 'loop-templates/content', 'calendar' );
		endwhile;

	else:
		$show_page = Calendar::get_current_calendar_page( $post );
		while ( $show_page->have_posts() ) : $show_page->the_post();
			set_query_var ('cats', $cats );
			set_query_var ('is_calendar_archive', $is_calendar_archive );
			get_template_part( 'loop-templates/content', 'calendar' );
		endwhile;

	endif;
	wp_reset_postdata();

endif;

get_footer();
