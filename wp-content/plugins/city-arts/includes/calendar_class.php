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

	public static function get_current_calendar_page ( $post ){
		$roles = DataHelper::get_roles();

		$args = [
			'post_status' => array_merge(['publish'], $roles),
			'post_type'      => 'page',
			'posts_per_page' => 1,
			'post_parent'    => $post->ID,
			'order'          => 'DESC',
			'orderby'        => 'date'
		];
		return new WP_Query( $args );
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

