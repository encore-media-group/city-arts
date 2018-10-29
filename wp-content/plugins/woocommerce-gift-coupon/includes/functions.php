<?php
/**
 * WooCommerce Gift Coupon Functions
 *
 * Sets functions of the plugin
 *
 * @author      WooCommerce Gift Coupon
 * @package     WooCommerce Gift Coupon/Functions
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_filter( 'plugin_row_meta', 'woocommerce_gift_coupon_row_meta', 10, 2 );

/**
 * Show row meta on the plugin screen.
 *
 * @param mixed $links Plugin Row Meta.
 * @param mixed $file  Plugin Base file.
 * @return array
 */
function woocommerce_gift_coupon_row_meta( $links, $file ) {
	if ( strpos( $file, 'woocommerce-gift-coupon.php' ) !== false ) {
		$action_links =
		array(
			'<a href="https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=F3E987XCMEGRQ"><b>' . __( 'Donate', 'woocommerce-gift-coupon' ) . '</b></a>',
		);
		$links        = array_merge( $links, $action_links );
	}
	return $links;
}

add_filter( 'plugin_action_links_' . WOOCOMMERCE_GIFT_COUPON_BASENAME, 'woocommerce_gift_coupon_action_links' );

/**
 * Show action links on the plugin screen.
 *
 * @param mixed $links Plugin Action links.
 * @return array
 */
function woocommerce_gift_coupon_action_links( $links ) {
	$action_links = array(
		'settings' => '<a href="' . admin_url( 'admin.php?page=woocommerce_gift_coupon_options_page' ) . '" aria-label="' . esc_attr__( 'Settings', 'woocommerce-gift-coupon' ) . '">' . esc_html__( 'Settings', 'woocommerce' ) . '</a>',
	);
	return array_merge( $action_links, $links );
}

add_action( 'admin_menu', 'woocommerce_gift_coupon_menu' );

/**
 * Helper function to add menu items.
 */
function woocommerce_gift_coupon_menu() {
	add_menu_page(
		__( 'Woo Gift Coupon', 'woocommerce-gift-coupon' ),
		__( 'Woo Gift Coupon', 'woocommerce-gift-coupon' ),
		'manage_options',
		'woocommerce_gift_coupon_options_page',
		'woocommerce_gift_coupon_import_options_page',
		WOOCOMMERCE_GIFT_COUPON_URL . 'admin/images/woocommerce_gift_coupon-icon.png',
		'55.6'
	);

	add_submenu_page(
		null,
		__( 'Download coupon PDF', 'woocommerce-gift-coupon' ),
		__( 'Download coupon PDF', 'woocommerce-gift-coupon' ),
		'manage_options',
		'woocommerce_gift_coupon_download_coupon_pdf',
		'woocommerce_gift_coupon_export_coupon_pdf'
	);
}

/**
 * Helper function to include options_admin_page.
 */
function woocommerce_gift_coupon_import_options_page() {
	require_once WOOCOMMERCE_GIFT_COUPON_DIR . 'admin/options-admin-page.php';
}

/**
 * Helper function to include preview_coupon_page.
 */
function woocommerce_gift_coupon_export_coupon_pdf() {
	require_once WOOCOMMERCE_GIFT_COUPON_DIR . 'admin/preview-coupon-page.php';
}

/**
 * Check coupons by order.
 *
 * @param mixed $order_id WC Order ID.
 * @return array
 */
function woocommerce_gift_coupon_check_order_coupons( $order_id ) {
	global $wpdb;
	if ( ! $order_id ) {
		return;
	}
	return $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}woocommerce_gift_coupon WHERE id_order=" . $order_id );
}

/**
 * Check coupons count by order.
 *
 * @param int $order_id WC Order ID.
 * @return array
 */
function woocommerce_gift_coupon_check_order_coupons_count( $order_id ) {
	$order   = new WC_Order( $order_id );
	$items   = $order->get_items();
	$coupons = array(
		'count' => 0,
	);

	$numc = 0;

	foreach ( $items as $item ) {
		$product    = get_post_meta( $item['product_id'], 'giftcoupon' );
		$giftcoupon = reset( $product );
		if ( $giftcoupon == 'yes' ) {
			$numc++;
			if ( $item['qty'] > 1 ) {
				$addsum = $item['qty'] - 1;
				$numc   = $numc + $addsum;
			}
			$coupons['count'] = $numc;
		}
	}

	return $coupons;
}

add_action( 'admin_enqueue_scripts', 'woocommerce_gift_coupon_plugin_scripts' );
add_action( 'admin_enqueue_scripts', 'woocommerce_gift_coupon_plugin_styles' );

/**
 * Enqueue scripts.
 */
function woocommerce_gift_coupon_plugin_scripts() {
	wp_enqueue_script( 'woocommerce-gift-coupon', WOOCOMMERCE_GIFT_COUPON_URL . 'admin/js/woocommerce-gift-coupon.js', array( 'wp-color-picker' ), false, true );
}

/**
 * Enqueue styles.
 */
function woocommerce_gift_coupon_plugin_styles() {
	wp_enqueue_style( 'wp-color-picker' );
	wp_register_style( 'woocommerce_gift_coupon_css', WOOCOMMERCE_GIFT_COUPON_URL . 'admin/css/styles.css' );
	wp_enqueue_style( 'woocommerce_gift_coupon_css' );
}

add_action( 'show_user_profile', 'woocommerce_gift_coupon_user_coupons', 10, 2 );
add_action( 'edit_user_profile', 'woocommerce_gift_coupon_user_coupons', 10, 2 );

/**
 * Get a table of user coupons.
 *
 * @param mixed $user Current user profile.
 */
function woocommerce_gift_coupon_user_coupons( $user ) {
	print '<h2>' . esc_attr__( 'My coupons', 'woocommerce-gift-coupon' ) . '</h2>';
	$user_coupons = woocommerce_gift_coupon_get_coupons_user( $user->ID );
	print '<table class="form-table">';
	if ( ! empty( $user_coupons ) ) {
		print '<tr>';
			print '<th>' . __( 'Coupon ID', 'woocommerce-gift-coupon' ) . '</th>';
			print '<th>' . __( 'Order ID', 'woocommerce-gift-coupon' ) . '</th>';
			print '<th>' . __( 'Coupon code', 'woocommerce-gift-coupon' ) . '</th>';
			print '<th>' . __( 'Coupon date', 'woocommerce-gift-coupon' ) . '</th>';
			print '<th>' . __( 'Preview', 'woocommerce-gift-coupon' ) . '</th>';
		print '</tr>';
		foreach ( $user_coupons as $key => $coupon ) {
			$product_reference = get_post_meta( $coupon->id_coupon, 'product_reference' );
			$product_reference = reset( $product_reference );
			if ( ! empty( $product_reference ) ) {
				$preview = '<a href="' . admin_url( "admin.php?page=woocommerce_gift_coupon_download_coupon_pdf&coupon_id=$coupon->id_coupon&product_id=$product_reference" ) . '" target="_blank"><span class="woocommerce-gift-coupon-preview-pdf"></span></a>';
			} else {
				$preview = __( 'No preview', 'woocommerce-gift-coupon' );
			}
			print '<tr>';
				print '<td>' . $coupon->id_coupon . '</td>';
				print '<td>' . $coupon->id_order . '</td>';
				print '<td>' . $coupon->post_title . '</td>';
				print '<td>' . $coupon->post_date . '</td>';
				print '<td>' . $preview . '</td>';
			print '</tr>';
		}
	} else {
		print '<td>' . __( 'No coupons', 'woocommerce-gift-coupon' ) . '</td>';
	}
	print '</table>';
}

/**
 * Get results of coupons by user.
 *
 * @param int $user_id Current user profile ID.
 * @return array
 */
function woocommerce_gift_coupon_get_coupons_user( $user_id ) {
	global $wpdb;
	if ( ! $user_id ) {
		return;
	}
	return $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}woocommerce_gift_coupon LEFT JOIN {$wpdb->prefix}posts ON {$wpdb->prefix}woocommerce_gift_coupon.id_coupon =  {$wpdb->prefix}posts.ID WHERE id_user={$user_id} ORDER BY id_coupon DESC" );
}

add_filter( 'manage_edit-shop_order_columns', 'woocommerce_gift_coupon_columns' );

/**
 * Render individual columns.
 *
 * @param array $columns Columns to render.
 * @return array
 */
function woocommerce_gift_coupon_columns( $columns ) {
	$columns['coupon_purchased'] = __( 'Bought coupons', 'woocommerce-gift-coupon' );
	$columns['coupon_status']    = __( 'Coupon Status', 'woocommerce-gift-coupon' );
	$columns['coupons']          = __( 'Coupons', 'woocommerce-gift-coupon' );

	return $columns;
}

add_action( 'manage_shop_order_posts_custom_column', 'woocommerce_gift_coupon_render_columns' );

/**
 * Render individual columns.
 *
 * @param array $column Column to render.
 */
function woocommerce_gift_coupon_render_columns( $column ) {
	global $post, $woocommerce, $wpdb;
	switch ( $column ) {
		case 'coupon_purchased':
			$coupons    = woocommerce_gift_coupon_check_order_coupons_count( $post->ID );
			$str_coupon = __( 'coupons bought', 'woocommerce-gift-coupon' );
			if ( $coupons['count'] > 0 ) {
				if ( $coupons['count'] == 1 ) {
					$str_coupon = __( 'coupon bought', 'woocommerce-gift-coupon' );
				}
				printf( '<b>' . $coupons['count'] . '</b> <span class="%s">%s</span>', sanitize_title( $str_coupon ), $str_coupon );
			} else {
				printf( '0 <span class="%s">%s</span>', sanitize_title( $str_coupon ), $str_coupon );
			}
			break;
		case 'coupon_status':
			$coupons_generated = woocommerce_gift_coupon_check_order_coupons( $post->ID );
			if ( ! empty( $coupons_generated ) ) {
				printf( '<span class="%s">%s</span>', sanitize_title( 'Sended' ), __( 'Sended', 'woocommerce-gift-coupon' ) );
			}
			break;
		case 'coupons':
			$coupons_generated = woocommerce_gift_coupon_check_order_coupons( $post->ID );
			if ( ! empty( $coupons_generated ) ) {
				foreach ( $coupons_generated as $coupon ) {
					$product_reference = get_post_meta( $coupon->id_coupon, 'product_reference' );
					$product_reference = reset( $product_reference );
					$preview           = '';
					if ( ! empty( $product_reference ) ) {
						$preview = ' - <a href="' . admin_url( "admin.php?page=woocommerce_gift_coupon_download_coupon_pdf&coupon_id=$coupon->id_coupon&product_id=$product_reference" ) . '" target="_blank"><span class="woocommerce-gift-coupon-preview-pdf"></span><br /></a>';
					}
					printf( '<span class="%s">%s</span>', sanitize_title( 'ID: <a href="' . get_edit_post_link( $coupon->id_coupon ) . '">' . $coupon->id_coupon . '</a><br />' ), 'ID: <a href="' . get_edit_post_link( $coupon->id_coupon ) . '">' . $coupon->id_coupon . '</a>' . $preview, 'woocommerce-gift-coupon' );
				}
			}
			break;
	}
}

add_filter( 'post_row_actions', 'woocommerce_gift_coupon_coupon_action_link', 10, 2 );

/**
 * Set row actions.
 *
 * @param array   $actions Array of actions.
 * @param WP_Post $post Current post object.
 * @return array
 */
function woocommerce_gift_coupon_coupon_action_link( $actions, $post ) {
	$post_type = 'shop_order';
	if ( $post->post_type == $post_type ) {
		$coupons           = woocommerce_gift_coupon_check_order_coupons_count( $post->ID );
		$coupons_generated = woocommerce_gift_coupon_check_order_coupons( $post->ID );
		if ( count( $coupons_generated ) < $coupons['count'] ) {
			$str_coupon = __( 'Generate coupons', 'woocommerce-gift-coupon' );
			if ( $coupons['count'] == 1 ) {
				$str_coupon = __( 'Generate coupon', 'woocommerce-gift-coupon' );
			}
			$sendback                   = admin_url( "edit.php?post_type=$post_type" );
			$sendback                   = add_query_arg( 'paged', 1, $sendback );
			$sendback                   = add_query_arg( 'wcgc_gc', array( $post->ID ), $sendback );
			$sendback                   = add_query_arg( array( 'ids' => $post->ID ), $sendback );
			$sendback                   = remove_query_arg( array( 'action2', 'tags_input', 'post_author', 'comment_status', 'ping_status', '_status', 'post', 'bulk_edit', 'post_view' ), $sendback );
			$actions['generate_coupon'] = sprintf( '<a href="%1$s">%2$s</a>', esc_url( $sendback ), esc_html( $str_coupon ) );
		}
	}

	return $actions;
}

add_action( 'add_meta_boxes', 'woocommerce_gift_coupon_product_add' );
add_action( 'save_post', 'woocommerce_gift_coupon_product_save' );
add_action( 'delete_post', 'woocommerce_gift_coupon_coupon_delete' );

/**
 * Delete coupons files.
 *
 * @param int $post_id Post id.
 */
function woocommerce_gift_coupon_coupon_delete( $post_id ) {
	global $wpdb;

	if ( ! empty( $post_id ) ) {
		$code = $wpdb->get_results( "SELECT post_title FROM {$wpdb->prefix}posts WHERE ID={$post_id}" );
		if ( ! empty( $code ) ) {
			$code       = reset( $code );
			$code       = $code->post_title;
			$upload_dir = wp_upload_dir();
			$pathupload = $upload_dir['basedir'] . '/woocommerce-gift-coupon';
			$file       = $pathupload . '/' . $code . '.pdf';
			if ( is_file( $file ) ) {
				unlink( $file );
			}
		}
	}
}

/**
 * Add WC Meta boxes.
 */
function woocommerce_gift_coupon_product_add() {
	add_meta_box( 'product_details', __( 'Gift coupon', 'woocommerce-gift-coupon' ), 'woocommerce_gift_coupon_call', 'product', 'normal', 'high' );
}

/**
 * Add WC Gift Coupon Meta Box Coupon Data.
 *
 * @param WP_Post $post Post object.
 */
function woocommerce_gift_coupon_call( $post ) {
	require_once WOOCOMMERCE_GIFT_COUPON_DIR . 'includes/class-wc-gift-coupon-meta-box-coupon-data.php';
	echo '<div id="woocommerce-coupon-data" class="postbox">';
		WC_Gift_Coupon_Meta_Box_Coupon_Data::output( $post );
	echo '</div>';
}

/**
 * Check if we're saving, the trigger an action based on the post type.
 *
 * @param int $post_id Post id.
 */
function woocommerce_gift_coupon_product_save( $post_id ) {
	global $post;
	if ( ! empty( $_POST ) && ! empty( $post ) ) {
		if ( $post->post_type == 'product' ) {
			$giftcoupon                 = sanitize_text_field( isset( $_POST['giftcoupon'] ) ? 'yes' : 'no' );
			$type                       = wc_clean( $_POST['discount_type'] );
			$amount                     = wc_format_decimal( $_POST['coupon_amount'] );
			$usage_limit                = empty( $_POST['usage_limit'] ) ? '' : absint( $_POST['usage_limit'] );
			$usage_limit_per_user       = empty( $_POST['usage_limit_per_user'] ) ? '' : absint( $_POST['usage_limit_per_user'] );
			$limit_usage_to_x_items     = empty( $_POST['limit_usage_to_x_items'] ) ? '' : absint( $_POST['limit_usage_to_x_items'] );
			$individual_use             = sanitize_text_field( isset( $_POST['individual_use'] ) ? 'yes' : 'no' );
			$expiry_date                = wc_clean( $_POST['expiry_date'] );
			$apply_before_tax           = sanitize_text_field( isset( $_POST['apply_before_tax'] ) ? 'yes' : 'no' );
			$free_shipping              = sanitize_text_field( isset( $_POST['free_shipping'] ) ? 'yes' : 'no' );
			$exclude_sale_items         = sanitize_text_field( isset( $_POST['exclude_sale_items'] ) ? 'yes' : 'no' );
			$minimum_amount             = wc_format_decimal( $_POST['minimum_amount'] );
			$maximum_amount             = wc_format_decimal( $_POST['maximum_amount'] );
			$customer_email             = array_filter( array_map( 'trim', explode( ',', wc_clean( $_POST['customer_email'] ) ) ) );
			$product_ids                = isset( $_POST['product_ids'] ) ? implode( ',', array_filter( array_map( 'intval', (array) $_POST['product_ids'] ) ) ) : '';
			$exclude_product_ids        = isset( $_POST['exclude_product_ids'] ) ? implode( ',', array_filter( array_map( 'intval', (array) $_POST['exclude_product_ids'] ) ) ) : '';
			$product_categories         = isset( $_POST['product_categories'] ) ? (array) $_POST['product_categories'] : array();
			$exclude_product_categories = isset( $_POST['exclude_product_categories'] ) ? (array) $_POST['exclude_product_categories'] : array();

			update_post_meta( $post_id, 'giftcoupon', $giftcoupon );
			update_post_meta( $post_id, 'discount_type', $type );
			update_post_meta( $post_id, 'coupon_amount', $amount );
			update_post_meta( $post_id, 'individual_use', $individual_use );
			update_post_meta( $post_id, 'product_ids', $product_ids );
			update_post_meta( $post_id, 'exclude_product_ids', $exclude_product_ids );
			update_post_meta( $post_id, 'usage_limit', $usage_limit );
			update_post_meta( $post_id, 'usage_limit_per_user', $usage_limit_per_user );
			update_post_meta( $post_id, 'limit_usage_to_x_items', $limit_usage_to_x_items );
			update_post_meta( $post_id, 'expiry_date', $expiry_date );
			update_post_meta( $post_id, 'apply_before_tax', $apply_before_tax );
			update_post_meta( $post_id, 'free_shipping', $free_shipping );
			update_post_meta( $post_id, 'exclude_sale_items', $exclude_sale_items );
			update_post_meta( $post_id, 'product_categories', $product_categories );
			update_post_meta( $post_id, 'exclude_product_categories', $exclude_product_categories );
			update_post_meta( $post_id, 'minimum_amount', $minimum_amount );
			update_post_meta( $post_id, 'maximum_amount', $maximum_amount );
			update_post_meta( $post_id, 'customer_email', $customer_email );
		}
	}
}
