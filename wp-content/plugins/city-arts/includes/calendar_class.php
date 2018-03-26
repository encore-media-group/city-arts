<?php
class Calendar {

	public static function get_calendar_posts( $results = 10, $cats = [] ) {

		$roles = ( is_user_logged_in() ) ? ['publish', 'pending', 'future', 'private'] : [];

		return new WP_Query ([
		    'posts_per_page' => $results,
				'post_status' => array_merge(['publish'], $roles),
		    'post_type' => 'post',
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
}
