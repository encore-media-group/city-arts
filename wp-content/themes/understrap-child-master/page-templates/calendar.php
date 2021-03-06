<?php
/**
 * Template Name: Calendar Template
 *
 *
 * @package understrap
 */

get_header();

$genre_slug = get_query_var('cal');

$month = get_query_var('issue-month');
$year = get_query_var('issue-year');
$issue_slug = (( $month ) && ( $year )) ? sprintf('%1$s-%2$s', $month, $year ) : "";

$month_num = "";

if( !empty($month) ) :
	$month_obj = date_parse($month);
	$month_num = $month_obj['month'];
endif;

$is_calendar_archive = ( $issue_slug ) ? true : false;

$cats = [];
$cats[] = ( in_array( $genre_slug, get_disciplines() )) ? $genre_slug : "";
$cats[] = ( is_array( term_exists( $issue_slug, "category") )) ? $issue_slug : "";
$cats = array_filter($cats);

if( $genre_slug ) :
	//genre is a singular post
	$genre_posts = Calendar::get_calendar_posts( 1, $cats, $month_num, $year );
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
			set_query_var ('year', $year );
			set_query_var ('month', $month_num );
			set_query_var ('is_calendar_archive', $is_calendar_archive );
			get_template_part( 'loop-templates/content', 'calendar' );
		endwhile;

	else:
		$show_page = Calendar::get_calendar_page();
		while ( $show_page->have_posts() ) : $show_page->the_post();
			set_query_var ('cats', $cats );
			set_query_var ('is_calendar_archive', $is_calendar_archive );
			get_template_part( 'loop-templates/content', 'calendar' );
		endwhile;

	endif;
	wp_reset_postdata();

endif;

get_footer();
