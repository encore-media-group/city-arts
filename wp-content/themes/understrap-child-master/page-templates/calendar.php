<?php
/**
 * Template Name: Calendar Template
 *
 *
 * @package understrap
 */

get_header();

$genre_slug = get_query_var('cal');
$issue_slug = ( empty( get_query_var('issue-month')) || empty(get_query_var('issue-year')) )
	? get_current_issue_slug()
	: sprintf('%1$s-%2$s', get_query_var('issue-month'), get_query_var('issue-year'));

$cats = [];
$cats[] = ( in_array( $genre_slug, get_disciplines() )) ? $genre_slug : "";
$cats[] = ( is_array( term_exists( $issue_slug, "category") )) ? $issue_slug : get_current_issue_slug();
$cats = array_filter($cats);

while ( have_posts() ) : the_post();
	set_query_var ('cats', $cats );
	get_template_part( 'loop-templates/content', 'calendar' );
endwhile;

get_footer();
