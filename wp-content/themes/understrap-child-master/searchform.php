<?php
/**
 * The template for displaying search forms in Underscores.me
 *
 * @package understrap
 */

?>
  <form method="get" id="searchform" action="<?php echo esc_url( home_url( '/' ) ); ?>" role="search">
  	<!--<label class="assistive-text" for="s"><?php esc_html_e( 'Search', 'understrap' ); ?></label>-->
  	<div class="input-group">
  		<input
      value="<?php echo get_search_query(); ?>" class="field form-control search-box" id="s" name="s" type="text" autofocus="autofocus"
  			placeholder="<?php esc_attr_e( 'Search &hellip;', 'understrap' ); ?>">
  		<span class="input-group-btn">
  			<input class="submit btn btn-primary" id="searchsubmit" name="submit" type="submit"
  			value="<?php esc_attr_e( 'Search', 'understrap' ); ?>">
  	</span>
  	</div>
</form>
