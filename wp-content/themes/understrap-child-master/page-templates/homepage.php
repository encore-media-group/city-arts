<?php
/**
 * Template Name: Home Page Template
 *
 *
 * @package understrap
 */

get_header('home');

while ( have_posts() ) : the_post();
	get_template_part( 'loop-templates/content', 'homepage' );
endwhile;

get_footer();
