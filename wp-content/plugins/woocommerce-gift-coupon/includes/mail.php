<?php
/**
 * WooCommerce Gift Coupon Mail
 *
 * Sets up the mail functionallity.
 *
 * @author      WooCommerce Gift Coupon
 * @package     WooCommerce Gift Coupon/Mail
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once WOOCOMMERCE_GIFT_COUPON_DIR . 'lib/dompdf/lib/html5lib/Parser.php';
require_once WOOCOMMERCE_GIFT_COUPON_DIR . 'lib/dompdf/lib/php-font-lib/src/FontLib/Autoloader.php';
require_once WOOCOMMERCE_GIFT_COUPON_DIR . 'lib/dompdf/lib/php-svg-lib/src/autoload.php';
require_once WOOCOMMERCE_GIFT_COUPON_DIR . 'lib/dompdf/src/Autoloader.php';
Dompdf\Autoloader::register();
use Dompdf\Dompdf;

add_action( 'admin_notices', 'woocommerce_gift_coupon_admin_notices' );
add_action( 'admin_footer', 'woocommerce_gift_coupon_bulk' );
add_action( 'load-edit.php', 'woocommerce_gift_coupon_bulk_action' );

/**
 * Add 'Generate coupons' option to select filter.
 */
function woocommerce_gift_coupon_bulk() {
	global $post_type;
	if ( $post_type == 'shop_order' ) {
		?>
		<script type="text/javascript">
			jQuery(function() {
				jQuery('<option>').val('generate_coupon').text('<?php esc_html_e( 'Generate coupons', 'woocommerce-gift-coupon' ); ?>').appendTo("select[name='action']");
				jQuery('<option>').val('generate_coupon').text('<?php esc_html_e( 'Generate coupons', 'woocommerce-gift-coupon' ); ?>').appendTo("select[name='action2']");
			});
		</script>
	<?php
	}
}

/**
 * Add action of generated coupons on load edit.
 */
function woocommerce_gift_coupon_bulk_action() {
	global $typenow;

	$post_type          = $typenow;
	$wp_list_table      = _get_list_table( 'WP_Posts_List_Table' );
	$action_link_single = isset( $_GET['wcgc_gc'] ) ? $_GET['wcgc_gc'] : null;

	if ( ! empty( $action_link_single ) ) {
		$post_ids         = $action_link_single;
		$sendback         = woocommerce_gift_coupon_generate_sendback( $wp_list_table );
		$generated_coupon = woocommerce_gift_coupon_send_action( $post_ids );
		$sendback         = add_query_arg(
			array(
				'generated_coupon' => $generated_coupon,
				'ids'              => join( ',', $post_ids ),
			),
			$sendback
		);
	} else {
		$action          = $wp_list_table->current_action();
		$allowed_actions = array( 'generate_coupon' );
		if ( ! in_array( $action, $allowed_actions ) ) {
			return;
		}
		check_admin_referer( 'bulk-posts' );
		if ( isset( $_REQUEST['post'] ) ) {
			$post_ids = array_map( 'intval', $_REQUEST['post'] );
		}
		if ( empty( $post_ids ) ) {
			return;
		}
		$sendback = woocommerce_gift_coupon_generate_sendback( $wp_list_table );
		switch ( $action ) {
			case 'generate_coupon':
				$generated_coupon = woocommerce_gift_coupon_send_action( $post_ids );
				$sendback         = add_query_arg(
					array(
						'generated_coupon' => $generated_coupon,
						'ids'              => join( ',', $post_ids ),
					),
					$sendback
				);
				break;
			default:
				return;
		}
	}
	$sendback = remove_query_arg( array( 'action', 'action2', 'tags_input', 'post_author', 'comment_status', 'ping_status', '_status', 'post', 'bulk_edit', 'post_view' ), $sendback );
	wp_redirect( $sendback );
	exit();
}

/**
 * Helper function to create de query arguments of the URL.
 *
 * @param object $wp_list_table Table list orders
 */
function woocommerce_gift_coupon_generate_sendback( $wp_list_table ) {
	$sendback = remove_query_arg( array( 'generated_coupon', 'untrashed', 'deleted', 'ids' ), wp_get_referer() );
	if ( ! $sendback ) {
		$sendback = admin_url( "edit.php?post_type=$post_type" );
	}
	$pagenum  = $wp_list_table->get_pagenum();
	$sendback = add_query_arg( 'paged', $pagenum, $sendback );
	return $sendback;
}

/**
 * Helper function to show the results of the generation.
 */
function woocommerce_gift_coupon_admin_notices() {
	global $pagenow;
	if ( $pagenow == 'edit.php' && ! isset( $_GET['trashed'] ) ) {
		$generated_coupon = 0;
		if ( isset( $_REQUEST['generated_coupon'] ) && (int) $_REQUEST['generated_coupon'] ) {
			$generated_coupon = (int) $_REQUEST['generated_coupon'];
		} elseif ( isset( $_GET['generated_coupon'] ) && (int) $_GET['generated_coupon'] ) {
			$generated_coupon = (int) $_GET['generated_coupon'];
		}
		$str_coupon = __( 'Coupons generated', 'woocommerce-gift-coupon' );
		if ( $generated_coupon == 1 ) {
			$str_coupon = __( 'Coupon generated', 'woocommerce-gift-coupon' );
		}
		if ( isset( $_REQUEST['generated_coupon'] ) || isset( $_GET['generated_coupon'] ) ) {
			$message = sprintf( _n( '<b>%s</b> ' . $str_coupon, '<b>%s</b> ' . $str_coupon, $generated_coupon ), number_format_i18n( $generated_coupon ) );
			echo "<div class=\"updated\"><p>{$message}</p></div>";
		}
	}
}


add_action( 'woocommerce_order_status_completed', 'woocommerce_gift_coupon_automatically_send_completed' );
add_action( 'woocommerce_order_status_processing', 'woocommerce_gift_coupon_automatically_send_processing' );

/**
 * Order Status completed.
 *
 * @param int $order_id Order ID.
 */
function woocommerce_gift_coupon_automatically_send_completed( $order_id ) {
	$woocommerce_gift_coupon_send = get_option( 'woocommerce_gift_coupon_send' );
	if ( $woocommerce_gift_coupon_send == 1 ) {
		return woocommerce_gift_coupon_automatically_send( $order_id );
	} else {
		return;
	}
}

/**
 * Order Status processing.
 *
 * @param int $order_id Order ID.
 */
function woocommerce_gift_coupon_automatically_send_processing( $order_id ) {
	$woocommerce_gift_coupon_send = get_option( 'woocommerce_gift_coupon_send' );
	if ( $woocommerce_gift_coupon_send == 2 ) {
		return woocommerce_gift_coupon_automatically_send( $order_id );
	} else {
		return;
	}
}

/**
 * Helper function to execute the sended process of the emails.
 *
 * @param int $order_id Order ID.
 */
function woocommerce_gift_coupon_automatically_send( $order_id ) {
	if ( isset( $order_id ) ) {
		$post_ids = array_map( 'intval', array( $order_id ) );
	}
	if ( empty( $order_id ) ) {
		return;
	}
	return woocommerce_gift_coupon_send_action( $post_ids );
}

add_action( 'woocommerce_thankyou', 'woocommerce_gift_coupon_woocommerce_auto_complete_order' );

/**
 * Helper function to send emails automatically on finish payment.
 *
 * @param int $order_id Order ID.
 */
function woocommerce_gift_coupon_woocommerce_auto_complete_order( $order_id ) {
	if ( ! $order_id ) {
		return;
	}
	$order                        = wc_get_order( $order_id );
	$status_order                 = $order->get_status();
	$woocommerce_gift_coupon_send = get_option( 'woocommerce_gift_coupon_send' );
	switch ( $woocommerce_gift_coupon_send ) {
		case 1: // Complete orders.
			$status_transform = 'completed';
			break;
		case 2: // Processing orders.
			$status_transform = 'processing';
			break;
		default:
			$status_transform = '';
			break;
	}
	if ( $status_transform == $status_order ) {
		$post_ids = array_map( 'intval', array( $order_id ) );
		if ( empty( $post_ids ) ) {
			return;
		}
		return woocommerce_gift_coupon_send_action( $post_ids );
	}
}

/**
 * Helper function to generate a random coupon code.
 *
 * @param int $post_id Post ID.
 */
function woocommerce_gift_coupon_create_code( $post_id ) {
	return rand( 1, $post_id ) . time() . $post_id . rand( 1, 9 );
}

/**
 * Helper function to send emails.
 *
 * @param array $post_ids Posts IDs.
 */
function woocommerce_gift_coupon_send_action( $post_ids ) {
	global $wpdb;
	require_once WOOCOMMERCE_GIFT_COUPON_DIR . 'includes/mail-template.php';
	$generated_coupon = 0;
	foreach ( $post_ids as $post_id ) {
		$order                = new WC_Order( $post_id );
		$items                = $order->get_items();
		$mailto               = $order->billing_email;
		$coupons_mail         = array();
		$sc                   = false;
		$coupons_to_generated = woocommerce_gift_coupon_check_order_coupons_count( $post_id );
		$coupons_generated    = woocommerce_gift_coupon_check_order_coupons( $post_id );
		if ( count( $coupons_generated ) < $coupons_to_generated['count'] ) {
			foreach ( $items as $key => $item ) {
				$product_id = $item['product_id'];
				$giftcoupon = get_post_meta( $product_id, 'giftcoupon' );
				$giftcoupon = reset( $giftcoupon );
				if ( $giftcoupon == 'yes' ) {
					$sc = true;
				}
				if ( $sc == true ) {
					for ( $i = 1; $i <= $item['qty']; $i++ ) {
						$couponcode    = woocommerce_gift_coupon_create_code( $post_id );
						$coupon        = array(
							'post_title'   => $couponcode,
							'post_excerpt' => 'Discount coupon',
							'post_status'  => 'publish',
							'post_author'  => 1,
							'post_type'    => 'shop_coupon',
						);
						$new_coupon_id = wp_insert_post( $coupon );
						$wpdb->insert( $wpdb->prefix . 'woocommerce_gift_coupon',
							array(
								'id_user'   => $order->user_id,
								'id_coupon' => $new_coupon_id,
								'id_order'  => $post_id,
							),
							array(
								'%s',
								'%s',
								'%s',
							)
						);
						if ( $wpdb == false ) {
							return false;
						}

						$type                       = get_post_meta( $product_id, 'discount_type' );
						$amount                     = get_post_meta( $product_id, 'coupon_amount' );
						$individual_use             = get_post_meta( $product_id, 'individual_use' );
						$product_ids                = get_post_meta( $product_id, 'product_ids' );
						$exclude_product_ids        = get_post_meta( $product_id, 'exclude_product_ids' );
						$usage_limit                = get_post_meta( $product_id, 'usage_limit' );
						$usage_limit_per_user       = get_post_meta( $product_id, 'usage_limit_per_user' );
						$limit_usage_to_x_items     = get_post_meta( $product_id, 'limit_usage_to_x_items' );
						$expiry_date                = get_post_meta( $product_id, 'expiry_date' );
						$apply_before_tax           = get_post_meta( $product_id, 'apply_before_tax' );
						$free_shipping              = get_post_meta( $product_id, 'free_shipping' );
						$exclude_sale_items         = get_post_meta( $product_id, 'exclude_sale_items' );
						$product_categories         = get_post_meta( $product_id, 'product_categories' );
						$exclude_product_categories = get_post_meta( $product_id, 'exclude_product_categories' );
						$minimum_amount             = get_post_meta( $product_id, 'minimum_amount' );
						$maximum_amount             = get_post_meta( $product_id, 'maximum_amount' );
						$customer_email             = get_post_meta( $product_id, 'customer_email' );

						$type                       = reset( $type );
						$amount                     = reset( $amount );
						$individual_use             = reset( $individual_use );
						$product_ids                = reset( $product_ids );
						$exclude_product_ids        = reset( $exclude_product_ids );
						$usage_limit                = reset( $usage_limit );
						$usage_limit_per_user       = reset( $usage_limit_per_user );
						$limit_usage_to_x_items     = reset( $limit_usage_to_x_items );
						$expiry_date                = reset( $expiry_date );
						$apply_before_tax           = reset( $apply_before_tax );
						$free_shipping              = reset( $free_shipping );
						$exclude_sale_items         = reset( $exclude_sale_items );
						$product_categories         = reset( $product_categories );
						$exclude_product_categories = reset( $exclude_product_categories );
						$minimum_amount             = reset( $minimum_amount );
						$maximum_amount             = reset( $maximum_amount );
						$customer_email             = reset( $customer_email );
						$coupons_mail['coupon_id']  = $product_id;
						$coupons_mail['code']       = $couponcode;

						if ( $type == 'percent' ) {
							$coupons_mail['price'] = $amount . '%';
						} else {
							$coupons_mail['price'] = wc_price( $amount );
						}

						$mail_settings = ! empty( get_option( 'woocommerce_email_from_address' ) ) ? get_option( 'woocommerce_email_from_address' ) : get_bloginfo( 'admin_email' );
						$from_settings = ! empty( get_option( 'woocommerce_email_from_name' ) ) ? get_option( 'woocommerce_email_from_name' ) : get_bloginfo( 'name' );

						// Generate headers and body.
						$headers  = 'MIME-Version: 1.0' . "\r\n";
						$headers .= 'From: ' . $from_settings . ' <' . $mail_settings . '>' . "\r\n" .
						'Reply-To: ' . $mail_settings . '' . "\r\n" .
						'X-Mailer: PHP/' . phpversion() . "\r\n" .
						$headers .= "Content-Type: text/html\n";
						$body_pdf = woocommerce_gift_coupon_generate_pdf_mail( $coupons_mail );
						$subject  = ! empty( get_option( 'woocommerce_gift_coupon_subject' ) ) ? get_option( 'woocommerce_gift_coupon_subject' ) : '';
						$message  = ! empty( get_option( 'woocommerce_gift_coupon_email_message' ) ) ? get_option( 'woocommerce_gift_coupon_email_message' ) : esc_html__( 'Please download the PDF', 'woocommerce-gift-coupon' );

						// Generate PDF.
						if ( ! empty( $body_pdf ) ) {
							$attachment = woocommerce_gift_coupon_generate_pdf( $body_pdf, $couponcode );
							if ( ! empty( $attachment ) ) {
								update_post_meta( $new_coupon_id, 'giftcoupon', $giftcoupon );
								update_post_meta( $new_coupon_id, 'product_reference', $product_id );
								update_post_meta( $new_coupon_id, 'discount_type', $type );
								update_post_meta( $new_coupon_id, 'coupon_amount', $amount );
								update_post_meta( $new_coupon_id, 'individual_use', $individual_use );
								update_post_meta( $new_coupon_id, 'usage_limit', $usage_limit );
								update_post_meta( $new_coupon_id, 'usage_limit_per_user', $usage_limit_per_user );
								update_post_meta( $new_coupon_id, 'limit_usage_to_x_items', $limit_usage_to_x_items );
								update_post_meta( $new_coupon_id, 'expiry_date', $expiry_date );
								update_post_meta( $new_coupon_id, 'apply_before_tax', $apply_before_tax );
								update_post_meta( $new_coupon_id, 'free_shipping', $free_shipping );
								update_post_meta( $new_coupon_id, 'product_ids', $product_ids );
								update_post_meta( $new_coupon_id, 'exclude_product_ids', $exclude_product_ids );
								update_post_meta( $new_coupon_id, 'exclude_sale_items', $exclude_sale_items );
								update_post_meta( $new_coupon_id, 'product_categories', $product_categories );
								update_post_meta( $new_coupon_id, 'exclude_product_categories', $exclude_product_categories );
								update_post_meta( $new_coupon_id, 'minimum_amount', $minimum_amount );
								update_post_meta( $new_coupon_id, 'maximum_amount', $maximum_amount );
								update_post_meta( $new_coupon_id, 'customer_email', $customer_email );
								add_filter( 'wp_mail_content_type', create_function( '', 'return "text/html";' ) );
								// Mail send function.
								wp_mail( $mailto, $subject, wpautop( $message ), $headers, $attachment );
								$generated_coupon++;
							}
						} else {
							return false;
						}
					}
				}
				$sc = false;
			}
		}
	}
	return $generated_coupon;
}

/**
 * Helper function to save pdfs
 *
 * @param string $body Body html to generate PDF.
 * @param string $pdf_name PDF Filename.
 */
function woocommerce_gift_coupon_generate_pdf( $body, $pdf_name ) {
	$filename   = $pdf_name . '.pdf';
	$upload_dir = wp_upload_dir();
	$pathupload = $upload_dir['basedir'] . '/woocommerce-gift-coupon';
	if ( wp_mkdir_p( $pathupload ) && ! file_exists( trailingslashit( $pathupload ) . '.htaccess' ) ) {
		$pdf_handle = fopen( trailingslashit( $pathupload ) . '.htaccess', 'w' );
		if ( $pdf_handle ) {
			fwrite( $pdf_handle, 'allow from all' );
			fclose( $pdf_handle );
		}
	}
	$dompdf = new Dompdf();
	$dompdf->setPaper( 'A4', 'portrait' );
	$dompdf->loadHtml( $body );
	$dompdf->render();
	$ficheropath = $pathupload . '/' . $filename;
	file_put_contents( $ficheropath, $dompdf->output() );
	return $ficheropath;
}
