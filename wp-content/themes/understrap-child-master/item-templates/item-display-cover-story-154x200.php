<?php
/**
 * Single item 154x200-vertical partial template.
 *
 * @package understrap
 */

  $issue_slug = isset($issue_slug) ? $issue_slug : '';
  $direction = isset($direction) ? strtolower( $direction ) : null;

  //global post of the cover story page with a category of /issue/monthname-year
  $issue_post_id = isset($post_id) ? $post_id : get_the_ID();

  if( $issue_slug ) {
    echo build_154x200_vertical( $issue_slug, $issue_post_id, $direction );
  }
