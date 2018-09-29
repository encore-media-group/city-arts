<?php

$cat_label = get_category_label();
$cat_url = $cat_label['url'];
$cat_name = $cat_label['name'];

$fields = get_fields( $post->ID);
$calendar_label_class = "";
$add_url = true;

if( !empty( $fields['show_in_calendar'] ) ) :
	if( $fields['show_in_calendar'] == 'yes' ) :
		$cat_name = "Calendar";
		$calendar_label_class = " calendar-item calendar-item-label ";
		$add_url = "/calendar";
	endif;
endif;

$span = sprintf( '<span class="category-label align-middle text-center mb-2 px-2 py-1 %2$s ">%1$s</span></a>' , $cat_name, $calendar_label_class );

if( $add_url ) :
	echo sprintf('<a class="url fn n" href="%1$s">%2$s</a>', $cat_url, $span );
else:
	echo $span;
endif;

?>
