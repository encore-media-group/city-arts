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
			'<a href="https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=F3E987XCMEGRQ"><b>' . __( 'Donate', WOOCOMMERCE_GIFT_COUPON_TEXT_DOMAIN ) . '</b></a>',
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
		'settings' => '<a href="' . admin_url( 'admin.php?page=woocommerce_gift_coupon_options_page' ) . '" aria-label="' . esc_attr__( 'Settings', WOOCOMMERCE_GIFT_COUPON_TEXT_DOMAIN ) . '">' . esc_html__( 'Settings', 'woocommerce' ) . '</a>',
	);
	return array_merge( $action_links, $links );
}

add_action( 'admin_menu', 'woocommerce_gift_coupon_menu' );

/**
 * Helper function to add menu items.
 */
function woocommerce_gift_coupon_menu() {
	add_menu_page(
		__( 'Woo Gift Coupon', WOOCOMMERCE_GIFT_COUPON_TEXT_DOMAIN ),
		__( 'Woo Gift Coupon', WOOCOMMERCE_GIFT_COUPON_TEXT_DOMAIN ),
		'manage_options',
		'woocommerce_gift_coupon_options_page',
		'woocommerce_gift_coupon_import_options_page',
		WOOCOMMERCE_GIFT_COUPON_URL . 'admin/images/woocommerce_gift_coupon-icon.png',
		'55.6'
	);

	add_submenu_page(
		null,
		__( 'Download coupon PDF', WOOCOMMERCE_GIFT_COUPON_TEXT_DOMAIN ),
		__( 'Download coupon PDF', WOOCOMMERCE_GIFT_COUPON_TEXT_DOMAIN ),
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
	if ( !$order_id || !$wpdb ) {
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
	if ( !empty( $items ) ) {
		foreach ( $items as $item ) {
			$product_type = get_post_meta( $item['product_id'], 'giftcoupon' );
			if ( !empty( $product_type ) ) {
				$giftcoupon = reset( $product_type );
				if ( $giftcoupon == 'yes' && $item['qty'] > 0 ) {
					$coupons['count'] += $item['qty'];
				}
			}
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
	wp_enqueue_script( WOOCOMMERCE_GIFT_COUPON_TEXT_DOMAIN, WOOCOMMERCE_GIFT_COUPON_URL . 'admin/js/woocommerce-gift-coupon.js', array( 'wp-color-picker' ), false, true );
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
 * Get product reference of a coupon
 *
 * @param int $id_coupon Coupon ID.
 */
function woocommerce_gift_coupon_get_product_reference_coupon( $id_coupon ) {
	$product_reference = get_post_meta( $id_coupon, 'product_reference' );
	if ( !empty( $product_reference ) ) {
		return reset( $product_reference );
	}
	return;
}

/**
 * Get product if is gift coupon
 *
 * @param int $id_product Product ID.
 */
function woocommerce_gift_coupon_get_product_coupon( $id_product ) {
	$product_type = get_post_meta( $id_product, 'giftcoupon' );
	if ( !empty( $product_type ) ) {
		return reset( $product_type );
	}
	return;
}

/**
 * Get a table of user coupons.
 *
 * @param mixed $user Current user profile.
 */
function woocommerce_gift_coupon_user_coupons( $user ) {
	print '<h2>' . esc_attr__( 'My coupons', WOOCOMMERCE_GIFT_COUPON_TEXT_DOMAIN ) . '</h2>';
	$user_coupons = woocommerce_gift_coupon_get_coupons_user( $user->ID );
	print '<table class="form-table">';
	if ( ! empty( $user_coupons ) ) {
		print '<tr>';
			print '<th>' . __( 'Coupon ID', WOOCOMMERCE_GIFT_COUPON_TEXT_DOMAIN ) . '</th>';
			print '<th>' . __( 'Order ID', WOOCOMMERCE_GIFT_COUPON_TEXT_DOMAIN ) . '</th>';
			print '<th>' . __( 'Coupon code', WOOCOMMERCE_GIFT_COUPON_TEXT_DOMAIN ) . '</th>';
			print '<th>' . __( 'Coupon date', WOOCOMMERCE_GIFT_COUPON_TEXT_DOMAIN ) . '</th>';
			print '<th>' . __( 'Preview', WOOCOMMERCE_GIFT_COUPON_TEXT_DOMAIN ) . '</th>';
		print '</tr>';
		foreach ( $user_coupons as $key => $coupon ) {
			$product_reference = woocommerce_gift_coupon_get_product_reference_coupon( $coupon->id_coupon );
			if ( ! empty( $product_reference ) ) {
				$preview = '<a href="' . admin_url( "admin.php?page=woocommerce_gift_coupon_download_coupon_pdf&coupon_id=$coupon->id_coupon&product_id=$product_reference" ) . '" target="_blank"><span class="woocommerce-gift-coupon-preview-pdf"></span></a>';
			} else {
				$preview = __( 'No preview', WOOCOMMERCE_GIFT_COUPON_TEXT_DOMAIN );
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
		print '<td>' . __( 'No coupons', WOOCOMMERCE_GIFT_COUPON_TEXT_DOMAIN ) . '</td>';
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
	$columns['coupon_purchased'] = __( 'Bought coupons', WOOCOMMERCE_GIFT_COUPON_TEXT_DOMAIN );
	$columns['coupon_status']    = __( 'Coupon Status', WOOCOMMERCE_GIFT_COUPON_TEXT_DOMAIN );
	$columns['coupons']          = __( 'Coupons', WOOCOMMERCE_GIFT_COUPON_TEXT_DOMAIN );

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
			$str_coupon = __( 'coupons bought', WOOCOMMERCE_GIFT_COUPON_TEXT_DOMAIN );
			if ( $coupons['count'] > 0 ) {
				if ( $coupons['count'] == 1 ) {
					$str_coupon = __( 'coupon bought', WOOCOMMERCE_GIFT_COUPON_TEXT_DOMAIN );
				}
				printf( '<b>' . $coupons['count'] . '</b> <span class="%s">%s</span>', sanitize_title( $str_coupon ), $str_coupon );
			} else {
				printf( '0 <span class="%s">%s</span>', sanitize_title( $str_coupon ), $str_coupon );
			}
			break;
		case 'coupon_status':
			$coupons_generated = woocommerce_gift_coupon_check_order_coupons( $post->ID );
			if ( ! empty( $coupons_generated ) ) {
				printf( '<b>' . count( $coupons_generated ) . '</b> <span class="%s">%s</span>', sanitize_title( 'Sended' ), __( 'Sended', WOOCOMMERCE_GIFT_COUPON_TEXT_DOMAIN ) );
			}
			break;
		case 'coupons':
			$coupons_generated = woocommerce_gift_coupon_check_order_coupons( $post->ID );
			if ( ! empty( $coupons_generated ) ) {
				foreach ( $coupons_generated as $coupon ) {
					$product_reference = woocommerce_gift_coupon_get_product_reference_coupon( $coupon->id_coupon );
					$preview           = '';
					if ( ! empty( $product_reference ) ) {
						$preview = ' - <a href="' . admin_url( "admin.php?page=woocommerce_gift_coupon_download_coupon_pdf&coupon_id=$coupon->id_coupon&product_id=$product_reference" ) . '" target="_blank"><span class="woocommerce-gift-coupon-preview-pdf"></span><br /></a>';
					}
					printf( '<span class="%s">%s</span>', sanitize_title( 'ID: <a href="' . get_edit_post_link( $coupon->id_coupon ) . '">' . $coupon->id_coupon . '</a><br />' ), 'ID: <a href="' . get_edit_post_link( $coupon->id_coupon ) . '">' . $coupon->id_coupon . '</a>' . $preview, WOOCOMMERCE_GIFT_COUPON_TEXT_DOMAIN );
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
		if ( $coupons['count'] > 0 ) {
			if ( count( $coupons_generated ) < $coupons['count'] ) {
				$str_coupon = __( 'Generate coupons', WOOCOMMERCE_GIFT_COUPON_TEXT_DOMAIN );
				if ( $coupons['count'] == 1 ) {
					$str_coupon = __( 'Generate coupon', WOOCOMMERCE_GIFT_COUPON_TEXT_DOMAIN );
				}
				$sendback                   = admin_url( "edit.php?post_type=$post_type" );
				$sendback                   = add_query_arg( 'paged', 1, $sendback );
				$sendback                   = add_query_arg( 'wcgc_gc', array( $post->ID ), $sendback );
				$sendback                   = add_query_arg( array( 'ids' => $post->ID ), $sendback );
				$sendback                   = remove_query_arg( array( 'action2', 'tags_input', 'post_author', 'comment_status', 'ping_status', '_status', 'post', 'bulk_edit', 'post_view' ), $sendback );
				$actions['generate_coupon'] = sprintf( '<a href="%1$s">%2$s</a>', esc_url( $sendback ), esc_html( $str_coupon ) );
			}
		}
	}

	return $actions;
}

add_action( 'add_meta_boxes', 'woocommerce_gift_coupon_product_add' );
add_action( 'delete_post', 'woocommerce_gift_coupon_coupon_delete' );
add_action( 'edit_post', 'woocommerce_gift_coupon_order_update' );
add_action( 'delete_user', 'woocommerce_gift_coupon_user_delete' );

/**
 * Update customer on edit order.
 *
 * @param int $post_id Post id.
 */
function woocommerce_gift_coupon_user_delete( $user_id ) {
	global $wpdb;
	$wpdb->query( "DELETE FROM `{$wpdb->prefix}woocommerce_gift_coupon` WHERE id_user = {$user_id}" );
}

/**
 * Update customer on edit order.
 *
 * @param int $post_id Post id.
 */
function woocommerce_gift_coupon_order_update( $post_id ) {
	global $post, $wpdb;
	if ( ! empty( $_POST ) && ! empty( $post ) ) {
		if ( $post->post_type == 'shop_order' ) {
			$customer = isset( $_POST['customer_user'] ) ? $_POST['customer_user'] : NULL;
			if ( empty( $customer )  || $customer < 1) {
				$user_order = 'NULL';
			} else {
				$user_order = $customer;
			}
			$wpdb->query( "UPDATE `{$wpdb->prefix}woocommerce_gift_coupon` SET id_user = {$user_order} WHERE id_order = {$post_id}" );
		}
	}
}

/**
 * Delete coupons files.
 *
 * @param int $post_id Post id.
 */
function woocommerce_gift_coupon_coupon_delete( $post_id ) {
	global $wpdb;

	$wpdb->hide_errors();

	require_once ABSPATH . 'wp-admin/includes/upgrade.php';

	if ( ! empty( $post_id ) ) {
		// Remove PDFs.
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
		// Remove relationships db on removing post.
		$wpdb->query( "DELETE FROM `{$wpdb->prefix}woocommerce_gift_coupon` WHERE id_coupon = {$post_id} OR id_order = {$post_id}" );
	}
}

// Save Coupon Meta Boxes on product details.
add_action( 'woocommerce_process_product_meta', 'WC_Gift_Coupon_Meta_Box_Coupon_Data::save', 10, 2 );

/**
 * Add WC Meta boxes.
 */
function woocommerce_gift_coupon_product_add() {
	add_meta_box( 'product_details', __( 'Gift Coupon', WOOCOMMERCE_GIFT_COUPON_TEXT_DOMAIN ), 'woocommerce_gift_coupon_call', 'product', 'normal', 'high' );
}

/**
 * Add WC Gift Coupon Meta Box Coupon Data.
 *
 * @param WP_Post $post Post object.
 */
function woocommerce_gift_coupon_call( $post ) {
	echo '<div id="woocommerce-coupon-data" class="postbox">';
		WC_Gift_Coupon_Meta_Box_Coupon_Data::output( $post );
	echo '</div>';
}
