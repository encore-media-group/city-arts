<?php
class Calendar {

	public static function get_calendar_posts( $results = 10, $cats = [], $month = "", $year = "" ) {

		$roles = DataHelper::get_roles();

		if( empty($month) || empty($year) ) :
			//only show results for the current year and month.
			$today = getdate();
			$month = $today['mon']; //must be a number, not name
			$year = $today['year'];
		endif;

		return new WP_Query ([
		    'posts_per_page' => $results,
				'post_status' => array_merge(['publish'], $roles),
		    'post_type' => 'post',
		    'nopaging' => true,
				'date_query' => [
					[
						'year'  => $year,
						'month' => $month
					],
				],
		    'tax_query' => [
		    	['taxonomy' => 'category',
					'field' => 'slug',
					'terms' => $cats ,
					'operator' => 'AND']
			 	],
		    'meta_query' => [
		    	[
						'key'=>'show_in_calendar',
						'value' => 'yes'
						],
					],
		  	'order'	=> 'DESC',
		    ]
			);
	}

	public static function meta_query_hide_calendar_posts () {

		return [
	        'relation' => 'OR', [
	            'key'=>'show_in_calendar',
	            'value' => 'yes',
	            'compare' => '!='
	        ],
	        [
	            'key'=>'show_in_calendar',
	            'compare' => 'NOT EXISTS'
	        ],
	      ];
	}

	public static function get_calendar_page( $atts = [] ) {
		$atts = shortcode_atts([
			'issue' => '',
			'return_array' => false
		], $atts );

		$roles = DataHelper::get_roles();

		$args = [
			'post_status' => array_merge(['publish'], $roles),
			'post_type'      => 'page',
			'posts_per_page' => 1,
			'order'          => 'DESC',
			'orderby'        => 'date'
		];

		if ( !empty($atts['issue']) ) :
			//show the page that has the same issue slug
			$page = get_page_by_path( 'calendar/' . $atts['issue'] );
			$this_page = $page->ID;
			$args['p'] = $this_page;
		else:
			//show the most recent month
			$page = get_page_by_path( 'calendar' );
			$post_parent = $page->ID;
			$args['post_parent'] = $post_parent;
		endif;

		$results = new WP_Query( $args );

		if( $atts['return_array'] ) :
			if ( $results->have_posts() ) :
				$current = $results->posts;
			 	return $current[0];
			endif;
		else:
			return $results;
		endif;
	}

	public static function list_events( $events ) {

		if ( ! $events )
			return "";

		$output = "";
		foreach ($events as $event) :
			$event_date = ( !empty( $event['event_date'] ) ) ? $event['event_date'] : "";
			$event_title = ( !empty( $event['event_title'] ) ) ? $event['event_title'] : "";
			$event_description = ( !empty( $event['event_description'] ) ) ? $event['event_description'] : "";
			$event_link = ( !empty( $event['event_link'] ) ) ? $event['event_link'] : "";
			$event_link_text = ( !empty( $event['event_link_text'] ) ) ? $event['event_link_text'] : "";
			$venue_name = ( !empty( $event['venue_name'] ) ) ? $event['venue_name'] : "";

			$tix_link = ( !empty($event_link) && !empty($event_link_text) ) ? sprintf('<a href="%1$s">%2$s</a>', $event_link, $event_link_text) : "";

			$date = new DateTime($event_date);

			$output .= "<h5>" . $event_date . "</h5>";
			$output .= "<h3>" . $event_title . "</h3>";
			$output .= "<p>" . $event_description . "</p>";
			$output .= "<p>" . $tix_link . "</p>";
			$output .= "<h6>" . $venue_name . "</h6>";
			$output .= "<hr>";
		endforeach;

		return $output;
	}
}

class DataHelper {

	public static function get_roles() {
		return ( is_user_logged_in() ) ? ['publish', 'pending', 'future', 'private'] : [];
	}

	public static function get_see_it_this_week() {

		$roles = self::get_roles();

		return new WP_Query([
      'posts_per_page' => 1,
      'orderby' => 'modified',
      'no_found_rows' => true,
      'post_status' => array_merge(['publish'], $roles),
      'tax_query' => [
				[
				    'taxonomy' => 'category',
				    'field'    => 'slug',
				    'terms'    =>  ['see-it-this-week'],
				],
			]
    ]);
  }

}
