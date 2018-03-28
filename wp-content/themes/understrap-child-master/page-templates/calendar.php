<?php
/**
 * Template Name: Calendar Template
 *
 *
 * @package understrap
 */

get_header();

$current_issue_slug = get_current_issue_slug();

$genre_slug = get_query_var('cal');

$issue_q_var = sprintf('%1$s-%2$s', get_query_var('issue-month'), get_query_var('issue-year'));

$issue_slug = ( empty( get_query_var('issue-month')) || empty(get_query_var('issue-year')) )
	? $current_issue_slug
	: sprintf('%1$s-%2$s', get_query_var('issue-month'), get_query_var('issue-year'));

$is_calendar_archive = ( $issue_q_var == $current_issue_slug ) ? true : false;

$cats = [];
$cats[] = ( in_array( $genre_slug, get_disciplines() )) ? $genre_slug : "";
$cats[] = ( is_array( term_exists( $issue_slug, "category") )) ? $issue_slug : $current_issue_slug;
$cats = array_filter($cats);

/*
to pull the genre post;
.com/calendar/music
then i need to get a post with:
 - $current_issue_slug
 - $show_in_calendar
 - disipline
if genre, then ->
*/
/*
if( $genre_slug ) :

	$genre_posts = Calendar::get_calendar_posts( 2, $cats );

	while( $genre_posts->have_posts() ) : $genre_posts->the_post();
		get_template_part( 'loop-templates/content', 'article-past-parent' );
	endwhile;
	wp_reset_postdata();

else:*/
	while ( have_posts() ) : the_post();
		set_query_var ('cats', $cats );
		set_query_var ('is_calendar_archive', $is_calendar_archive );
		get_template_part( 'loop-templates/content', 'calendar' );
	endwhile;
/*endif;*/


get_footer();
