<?php
/**
 * Coupon Email Content
 *
 * @author      StoreApps
 * @package     WooCommerce Smart Coupons/Templates
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
} ?>

<?php
global $store_credit_label;

if ( function_exists( 'wc_get_template' ) ) {
	wc_get_template( 'emails/email-header.php', array( 'email_heading' => $email_heading ) );
} else {
	woocommerce_get_template( 'emails/email-header.php', array( 'email_heading' => $email_heading ) );
}
?>

<style type="text/css">
		.coupon-container {
			margin: .2em;
			box-shadow: 0 0 5px #e0e0e0;
			display: inline-table;
			text-align: center;
			cursor: pointer;
			padding: .55em;
			line-height: 1.4em;
		}

		.coupon-content {
			padding: 0.2em 1.2em;
		}

		.coupon-content .code {
			font-family: monospace;
			font-size: 1.2em;
			font-weight:700;
		}

		.coupon-content .coupon-expire,
		.coupon-content .discount-info {
			font-family: Helvetica, Arial, sans-serif;
			font-size: 1em;
		}
		.coupon-content .discount-description {
			font: .7em/1 Helvetica, Arial, sans-serif;
			width: 250px;
			margin: 10px inherit;
			display: inline-block;
		}

</style>
<style type="text/css"><?php echo ( isset( $coupon_styles ) && ! empty( $coupon_styles ) ) ? $coupon_styles : ''; // WPCS: XSS ok. ?></style>
<style type="text/css">
	.coupon-container.left:before,
	.coupon-container.bottom:before {
		background: <?php echo esc_html( $foreground_color ); ?> !important;
	}
	.coupon-container.left:hover, .coupon-container.left:focus, .coupon-container.left:active,
	.coupon-container.bottom:hover, .coupon-container.bottom:focus, .coupon-container.bottom:active {
		color: <?php echo esc_html( $background_color ); ?> !important;
	}
</style>

<?php echo $message_from_sender; // WPCS: XSS ok. ?>

<p>
<?php
	/* translators: %s: Default email subject */
	echo sprintf( esc_html__( 'To redeem your discount use coupon code %s during checkout or click on the following coupon:', 'woocommerce-smart-coupons' ), '<strong><code>' . esc_html( $coupon_code ) . '</code></strong>' );
?>
</p>

<?php

$coupon = new WC_Coupon( $coupon_code );

if ( $this->is_wc_gte_30() ) {
	if ( ! is_object( $coupon ) || ! is_callable( array( $coupon, 'get_id' ) ) ) {
		return;
	}
	$coupon_id = $coupon->get_id();
	if ( empty( $coupon_id ) ) {
		return;
	}
	$coupon_amount    = $coupon->get_amount();
	$is_free_shipping = ( $coupon->get_free_shipping() ) ? 'yes' : 'no';
	$expiry_date      = $coupon->get_date_expires();
	$coupon_code      = $coupon->get_code();
} else {
	$coupon_id        = ( ! empty( $coupon->id ) ) ? $coupon->id : 0;
	$coupon_amount    = ( ! empty( $coupon->amount ) ) ? $coupon->amount : 0;
	$is_free_shipping = ( ! empty( $coupon->free_shipping ) ) ? $coupon->free_shipping : '';
	$expiry_date      = ( ! empty( $coupon->expiry_date ) ) ? $coupon->expiry_date : '';
	$coupon_code      = ( ! empty( $coupon->code ) ) ? $coupon->code : '';
}

$coupon_post = get_post( $coupon_id );

$coupon_data = $this->get_coupon_meta_data( $coupon );

	$coupon_target              = '';
	$wc_url_coupons_active_urls = get_option( 'wc_url_coupons_active_urls' );
if ( ! empty( $wc_url_coupons_active_urls ) ) {
	$coupon_target = ( ! empty( $wc_url_coupons_active_urls[ $coupon_id ]['url'] ) ) ? $wc_url_coupons_active_urls[ $coupon_id ]['url'] : '';
}
if ( ! empty( $coupon_target ) ) {
	$coupon_target = home_url( '/' . $coupon_target );
} else {
	$coupon_target = home_url( '/?sc-page=shop&coupon-code=' . $coupon_code );
}

	$coupon_target = apply_filters( 'sc_coupon_url_in_email', $coupon_target, $coupon );
?>

<div style="margin: 10px 0; text-align: center;" title="<?php echo esc_html__( 'Click to visit store. This coupon will be applied automatically.', 'woocommerce-smart-coupons' ); ?>">
	<a href="<?php echo esc_url( $coupon_target ); ?>" style="color: #444;">

		<div class="coupon-container <?php echo esc_attr( $this->get_coupon_container_classes() ); ?>" style="cursor:pointer; text-align:center; <?php echo $this->get_coupon_style_attributes(); // WPCS: XSS ok. ?>">
			<?php
				echo '<div class="coupon-content ' . esc_attr( $this->get_coupon_content_classes() ) . '">
					<div class="discount-info">';

			if ( ! empty( $coupon_data['coupon_amount'] ) && 0 !== $coupon_amount ) {
				echo $coupon_data['coupon_amount']; // phpcs:ignore
				echo ' ' . esc_html( $coupon_data['coupon_type'] );
				if ( 'yes' === $is_free_shipping ) {
					echo esc_html__( ' &amp; ', 'woocommerce-smart-coupons' );
				}
			}

			if ( 'yes' === $is_free_shipping ) {
				echo esc_html__( 'Free Shipping', 'woocommerce-smart-coupons' );
			}
					echo '</div>';

					echo '<div class="code">' . esc_html( $coupon_code ) . '</div>';

					$show_coupon_description = get_option( 'smart_coupons_show_coupon_description', 'no' );
			if ( ! empty( $coupon_post->post_excerpt ) && 'yes' === $show_coupon_description ) {
				echo '<div class="discount-description">' . $coupon_post->post_excerpt . '</div>'; // WPCS: XSS ok.
			}

			if ( ! empty( $expiry_date ) ) {
				$expiry_date = $this->get_expiration_format( $expiry_date );
				echo '<div class="coupon-expire">' . esc_html( $expiry_date ) . '</div>';
			} else {
				echo '<div class="coupon-expire">' . esc_html__( 'Never Expires ', 'woocommerce-smart-coupons' ) . '</div>';
			}
				echo '</div>';
			?>
		</div>
	</a>
</div>

<?php $site_url = ! empty( $url ) ? $url : home_url(); ?>
<center><a href="<?php echo esc_url( $site_url ); ?>"><?php echo esc_html__( 'Visit Store', 'woocommerce-smart-coupons' ); ?></a></center>

<?php if ( ! empty( $from ) ) { ?>
	<p>
		<?php
			/* translators: %s: singular name for store credit */
			echo ( ! empty( $store_credit_label['singular'] ) ? sprintf( esc_html__( 'You got this %s', 'woocommerce-smart-coupons' ), esc_html( strtolower( $store_credit_label['singular'] ) ) ) : esc_html__( 'You got this gift card', 'woocommerce-smart-coupons' ) ) . ' ' . esc_html( $from ) . esc_html( $sender );
		?>
	</p>
<?php } ?>

<div style="clear:both;"></div>

<?php
if ( function_exists( 'wc_get_template' ) ) {
	wc_get_template( 'emails/email-footer.php' );
} else {
	woocommerce_get_template( 'emails/email-footer.php' );
}
?>
