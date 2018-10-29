<?php
/**
 * WooCommerce Gift Coupon Preview template
 *
 * Sets up the preview template
 *
 * @author      WooCommerce Gift Coupon
 * @package     WooCommerce Gift Coupon/Preview Template
 */

if ( isset( $_GET['page'] ) && $_GET['page'] == 'woocommerce_gift_coupon_download_coupon_pdf' ) {

	require_once WOOCOMMERCE_GIFT_COUPON_DIR . 'includes/mail-template.php';

	$product_id = isset( $_GET['product_id'] ) ? $_GET['product_id'] : null;
	$coupon_id  = isset( $_GET['coupon_id'] ) ? $_GET['coupon_id'] : null;

	if ( ! empty( $product_id ) && ! empty( $coupon_id ) ) {
		$discount_type             = get_post_meta( $coupon_id, 'discount_type' );
		$coupon_amount             = get_post_meta( $coupon_id, 'coupon_amount' );
		$type                      = reset( $discount_type );
		$amount                    = reset( $coupon_amount );
		$coupon                    = get_post( $coupon_id );
		$coupons_mail['coupon_id'] = $product_id;
		$coupons_mail['code']      = $coupon->post_title;

		if ( $type == 'percent' ) {
			$coupons_mail['price'] = $amount . '%';
		} else {
			$coupons_mail['price'] = wc_price( $amount );
		}

		$filename   = $coupon->post_title . '.pdf';
		$upload_dir = wp_upload_dir();
		$pathupload = $upload_dir['basedir'] . '/woocommerce-gift-coupon';
		$urlupload  = $upload_dir['baseurl'] . '/woocommerce-gift-coupon/' . $filename;
		$body_pdf   = '<div class="woocommerce-gift-coupon-preview-pdf-meta">';
		if ( wp_mkdir_p( $pathupload ) && file_exists( trailingslashit( $pathupload ) . $filename ) ) {
			$body_pdf .= '<a href="' . $urlupload . '" target="_blank">' . __( 'Download PDF', 'woocommerce-gift-coupon' ) . '</a>';
		}
		$body_pdf .= '</div>';
		$body_pdf .= woocommerce_gift_coupon_generate_pdf_mail( $coupons_mail );
	} else {
		$coupons_mail['code']  = '01234567890';
		$coupons_mail['price'] = wc_price( 20 );
		$body_pdf              = woocommerce_gift_coupon_generate_pdf_mail( $coupons_mail, true );
	}

	print $body_pdf;
}
