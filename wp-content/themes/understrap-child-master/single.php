<?php
/**
 * The template for displaying all single posts.
 *
 * @package understrap
 */

get_header();

// determine what article view type to display
// the list of article formats is in the city arts plugin

$term_list = wp_get_post_terms($post->ID, 'article_format', array("fields" => "all"));
$article_format = "";
$template = "article-past-parent"; //set a default

if( sizeof( $term_list ) > 0 ) {
  $template = ( $term_list[0]->slug ) . '-parent';
}

global $post;

setup_postdata( $post );
get_template_part( 'loop-templates/content', $template );
wp_reset_postdata();

get_footer();

?>





