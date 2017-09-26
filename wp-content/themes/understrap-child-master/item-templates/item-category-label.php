
<?php

$cat_label = get_category_label();

$yellow_label = '<a class="url fn n" href="%1$s"><span class="category-label align-middle text-center mb-2 px-2 py-1">%2$s</span></a>';

echo sprintf( $yellow_label, $cat_label['url'], $cat_label['name'] );
?>
