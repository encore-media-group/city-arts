<?php
/**
 * WooCommerce Gift Coupon Meta Boxes
 *
 * Sets up the write panels used by products and orders (custom post types).
 *
 * @author      WooCommerce Gift Coupon
 * @package     WooCommerce Gift Coupon/Meta Boxes
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * WC_Gift_Coupon_Meta_Box_Coupon_Data Class.
 */
class WC_Gift_Coupon_Meta_Box_Coupon_Data extends WC_Meta_Box_Coupon_Data {

	/**
	 * Output the metabox.
	 *
	 * @param WP_Post $post Post object.
	 */
	public static function output( $post ) {
		wp_nonce_field( 'woocommerce_save_data', 'woocommerce_meta_nonce' );

		$giftcoupon                 = get_post_meta( $post->ID, 'giftcoupon' );
		$type                       = get_post_meta( $post->ID, 'discount_type' );
		$amount                     = get_post_meta( $post->ID, 'coupon_amount' );
		$individual_use             = get_post_meta( $post->ID, 'individual_use' );
		$product_ids                = get_post_meta( $post->ID, 'product_ids' );
		$exclude_product_ids        = get_post_meta( $post->ID, 'exclude_product_ids' );
		$usage_limit                = get_post_meta( $post->ID, 'usage_limit' );
		$usage_limit_per_user       = get_post_meta( $post->ID, 'usage_limit_per_user' );
		$limit_usage_to_x_items     = get_post_meta( $post->ID, 'limit_usage_to_x_items' );
		$expiry_date                = get_post_meta( $post->ID, 'expiry_date' );
		$apply_before_tax           = get_post_meta( $post->ID, 'apply_before_tax' );
		$free_shipping              = get_post_meta( $post->ID, 'free_shipping' );
		$exclude_sale_items         = get_post_meta( $post->ID, 'exclude_sale_items' );
		$product_categories         = get_post_meta( $post->ID, 'product_categories' );
		$exclude_product_categories = get_post_meta( $post->ID, 'exclude_product_categories' );
		$minimum_amount             = get_post_meta( $post->ID, 'minimum_amount' );
		$maximum_amount             = get_post_meta( $post->ID, 'maximum_amount' );
		$customer_email             = get_post_meta( $post->ID, 'customer_email' );

		$giftcoupon                 = reset( $giftcoupon );
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
		if ( ! empty( $product_ids ) ) {
			$product_ids = explode( ',', $product_ids );
		}
		if ( ! empty( $exclude_product_ids ) ) {
			$exclude_product_ids = explode( ',', $exclude_product_ids );
		}
		?>

		<div id="coupon_options" class="panel-wrap coupon_data">
			<div class="wc-tabs-back"></div>
			<ul class="coupon_data_tabs wc-tabs" style="display:none;">
				<?php
				$coupon_data_tabs = apply_filters( 'woocommerce_coupon_data_tabs', array(
					'general'           => array(
						'label'  => __( 'General', 'woocommerce-gift-coupon' ),
						'target' => 'general_coupon_data',
						'class'  => 'general_coupon_data',
					),
					'usage_restriction' => array(
						'label'  => __( 'Usage restriction', 'woocommerce-gift-coupon' ),
						'target' => 'usage_restriction_coupon_data',
						'class'  => '',
					),
					'usage_limit'       => array(
						'label'  => __( 'Usage limits', 'woocommerce-gift-coupon' ),
						'target' => 'usage_limit_coupon_data',
						'class'  => '',
					),
				) );
				foreach ( $coupon_data_tabs as $key => $tab ) {
					?>
					<li class="<?php echo $key; ?>_options <?php echo $key; ?>_tab <?php echo implode( ' ' , (array) $tab['class'] ); ?>">
						<a href="#<?php echo $tab['target']; ?>"><span><?php echo esc_html( $tab['label'] ); ?></span></a>
					</li>
				<?php
				}
				?>
			</ul>
			<div id="general_coupon_data" class="panel woocommerce_options_panel">
			<?php
				// Gift Coupon.
				woocommerce_wp_checkbox( array(
					'id'          => 'giftcoupon',
					'label'       => __( 'Allow product as gift coupon', 'woocommerce-gift-coupon' ),
					'description' => __( 'Check this box if you want to allow this product as gift coupon', 'woocommerce-gift-coupon' ),
				) );

				// Type.
				woocommerce_wp_select( array(
					'id'      => 'discount_type',
					'label'   => __( 'Discount type', 'woocommerce-gift-coupon' ),
					'options' => wc_get_coupon_types(),
				) );

				// Amount.
				woocommerce_wp_text_input( array(
					'id'          => 'coupon_amount',
					'label'       => __( 'Coupon amount', 'woocommerce-gift-coupon' ),
					'placeholder' => wc_format_localized_price( 0 ),
					'description' => __( 'Value of the coupon.', 'woocommerce-gift-coupon' ),
					'data_type'   => 'price',
					'desc_tip'    => true,
				) );

				// Free Shipping.
				if ( wc_shipping_enabled() ) {
					woocommerce_wp_checkbox( array(
						'id'          => 'free_shipping',
						'label'       => __( 'Allow free shipping', 'woocommerce-gift-coupon' ),
						'description' => sprintf( __( 'Check this box if the coupon grants free shipping. A <a href="%s" target="_blank">free shipping method</a> must be enabled in your shipping zone and be set to require "a valid free shipping coupon" (see the "Free Shipping Requires" setting).', 'woocommerce-gift-coupon' ), 'https://docs.woocommerce.com/document/free-shipping/' ),
					) );
				}

				// Expiry date.
				$expiry_date = $expiry_date ? $expiry_date : '';
				woocommerce_wp_text_input( array(
					'id'                => 'expiry_date',
					'value'             => esc_attr( $expiry_date ),
					'label'             => __( 'Coupon expiry date', 'woocommerce-gift-coupon' ),
					'placeholder'       => 'YYYY-MM-DD',
					'description'       => '',
					'class'             => 'date-picker',
					'custom_attributes' => array(
						'pattern' => apply_filters( 'woocommerce_date_input_html_pattern', '[0-9]{4}-(0[1-9]|1[012])-(0[1-9]|1[0-9]|2[0-9]|3[01])' ),
					),
				) );
			?></div>
			<div id="usage_restriction_coupon_data" class="panel woocommerce_options_panel"><?php
				echo '<div class="options_group">';

				// minimum spend.
				woocommerce_wp_text_input( array(
					'id'          => 'minimum_amount',
					'label'       => __( 'Minimum spend', 'woocommerce-gift-coupon' ),
					'placeholder' => __( 'No minimum', 'woocommerce-gift-coupon' ),
					'description' => __( 'This field allows you to set the minimum spend (subtotal, including taxes) allowed to use the coupon.', 'woocommerce-gift-coupon' ),
					'data_type'   => 'price',
					'desc_tip'    => true,
				) );

				// maximum spend.
				woocommerce_wp_text_input( array(
					'id'          => 'maximum_amount',
					'label'       => __( 'Maximum spend', 'woocommerce-gift-coupon' ),
					'placeholder' => __( 'No maximum', 'woocommerce-gift-coupon' ),
					'description' => __( 'This field allows you to set the maximum spend (subtotal, including taxes) allowed when using the coupon.', 'woocommerce-gift-coupon' ),
					'data_type'   => 'price',
					'desc_tip'    => true,
				) );

				// Individual use.
				woocommerce_wp_checkbox( array(
					'id'          => 'individual_use',
					'label'       => __( 'Individual use only', 'woocommerce-gift-coupon' ),
					'description' => __( 'Check this box if the coupon cannot be used in conjunction with other coupons.', 'woocommerce-gift-coupon' ),
				) );

				// Exclude Sale Products.
				woocommerce_wp_checkbox( array(
					'id'          => 'exclude_sale_items',
					'label'       => __( 'Exclude sale items', 'woocommerce-gift-coupon' ),
					'description' => __( 'Check this box if the coupon should not apply to items on sale. Per-item coupons will only work if the item is not on sale. Per-cart coupons will only work if there are items in the cart that are not on sale.', 'woocommerce-gift-coupon' ),
				) );
				echo '</div><div class="options_group">';

				// Product ids.
				?>
				<p class="form-field"><label><?php esc_html_e( 'Products', 'woocommerce-gift-coupon' ); ?></label>
				<select class="wc-product-search" multiple="multiple" style="width: 50%;" name="product_ids[]" data-placeholder="<?php esc_attr_e( 'Search for a product&hellip;', 'woocommerce-gift-coupon' ); ?>" data-action="woocommerce_json_search_products_and_variations">
					<?php
						foreach ( $product_ids as $product_id ) {
							$product = wc_get_product( $product_id );
							if ( is_object( $product ) ) {
								echo '<option value="' . esc_attr( $product_id ) . '"' . selected( true, true, false ) . '>' . wp_kses_post( $product->get_formatted_name() ) . '</option>';
							}
						}
					?>
				</select> <?php echo wc_help_tip( __( 'Products that the coupon will be applied to, or that need to be in the cart in order for the "Fixed cart discount" to be applied.', 'woocommerce-gift-coupon' ) ); ?></p>
				<?php

				// Exclude Product ids.
				?>
				<p class="form-field"><label><?php esc_html_e( 'Exclude products', 'woocommerce-gift-coupon' ); ?></label>
				<select class="wc-product-search" multiple="multiple" style="width: 50%;" name="exclude_product_ids[]" data-placeholder="<?php esc_attr_e( 'Search for a product&hellip;', 'woocommerce-gift-coupon' ); ?>" data-action="woocommerce_json_search_products_and_variations">
					<?php
						$product_ids = $exclude_product_ids;
						foreach ( $product_ids as $product_id ) {
							$product = wc_get_product( $product_id );
							if ( is_object( $product ) ) {
								echo '<option value="' . esc_attr( $product_id ) . '"' . selected( true, true, false ) . '>' . wp_kses_post( $product->get_formatted_name() ) . '</option>';
							}
						}
					?>
				</select> <?php echo wc_help_tip( __( 'Products that the coupon will not be applied to, or that cannot be in the cart in order for the "Fixed cart discount" to be applied.', 'woocommerce-gift-coupon' ) ); ?></p>
				<?php
				echo '</div><div class="options_group">';

				// Categories.
				?>
				<p class="form-field"><label for="product_categories"><?php esc_html_e( 'Product categories', 'woocommerce-gift-coupon' ); ?></label>
				<select id="product_categories" name="product_categories[]" style="width: 50%;"  class="wc-enhanced-select" multiple="multiple" data-placeholder="<?php esc_attr_e( 'Any category', 'woocommerce-gift-coupon' ); ?>">
					<?php
						$category_ids = $product_categories;
						$categories   = get_terms( 'product_cat', 'orderby=name&hide_empty=0' );

						if ( $categories ) {
							foreach ( $categories as $cat ) {
								echo '<option value="' . esc_attr( $cat->term_id ) . '"' . selected( in_array( $cat->term_id, $category_ids ), true, false ) . '>' . esc_html( $cat->name ) . '</option>';
							}
						}
					?>
				</select> <?php echo wc_help_tip( __( 'Product categories that the coupon will be applied to, or that need to be in the cart in order for the "Fixed cart discount" to be applied.', 'woocommerce-gift-coupon' ) ); ?></p>
				<?php

				// Exclude Categories.
				?>
				<p class="form-field"><label for="exclude_product_categories"><?php esc_html_e( 'Exclude categories', 'woocommerce-gift-coupon' ); ?></label>
				<select id="exclude_product_categories" name="exclude_product_categories[]" style="width: 50%;"  class="wc-enhanced-select" multiple="multiple" data-placeholder="<?php esc_attr_e( 'No categories', 'woocommerce-gift-coupon' ); ?>">
					<?php
						$category_ids = $exclude_product_categories;
						$categories   = get_terms( 'product_cat', 'orderby=name&hide_empty=0' );
						if ( $categories ) {
							foreach ( $categories as $cat ) {
								echo '<option value="' . esc_attr( $cat->term_id ) . '"' . selected( in_array( $cat->term_id, $category_ids ), true, false ) . '>' . esc_html( $cat->name ) . '</option>';
							}
						}
					?>
				</select> <?php echo wc_help_tip( __( 'Product categories that the coupon will not be applied to, or that cannot be in the cart in order for the "Fixed cart discount" to be applied.', 'woocommerce-gift-coupon' ) ); ?></p>
				<?php
				echo '</div><div class="options_group">';

				// Customers
				woocommerce_wp_text_input( array(
					'id'                => 'customer_email',
					'label'             => __( 'Email restrictions', 'woocommerce-gift-coupon' ),
					'placeholder'       => __( 'No restrictions', 'woocommerce-gift-coupon' ),
					'description'       => __( 'List of allowed emails to check against the customer billing email when an order is placed. Separate email addresses with commas.', 'woocommerce-gift-coupon' ),
					'value'             => implode( ', ', (array) $customer_email),
					'desc_tip'          => true,
					'type'              => 'email',
					'class'             => '',
					'custom_attributes' => array(
						'multiple'  => 'multiple',
					),
				) );
				echo '</div>';
			?></div>
			<div id="usage_limit_coupon_data" class="panel woocommerce_options_panel"><?php
				echo '<div class="options_group">';

				// Usage limit per coupons.
				woocommerce_wp_text_input( array(
					'id'                => 'usage_limit',
					'label'             => __( 'Usage limit per coupon', 'woocommerce-gift-coupon' ),
					'placeholder'       => esc_attr__( 'Unlimited usage', 'woocommerce-gift-coupon' ),
					'description'       => __( 'How many times this coupon can be used before it is void.', 'woocommerce-gift-coupon' ),
					'type'              => 'number',
					'desc_tip'          => true,
					'class'             => 'short',
					'custom_attributes' => array(
						'step'  => 1,
						'min' => 0,
					),
					'value' => $usage_limit ? $usage_limit : '',
				) );

				// Usage limit per product.
				woocommerce_wp_text_input( array(
					'id'                => 'limit_usage_to_x_items',
					'label'             => __( 'Limit usage to X items', 'woocommerce-gift-coupon' ),
					'placeholder'       => esc_attr__( 'Apply to all qualifying items in cart', 'woocommerce-gift-coupon' ),
					'description'       => __( 'The maximum number of individual items this coupon can apply to when using product discounts. Leave blank to apply to all qualifying items in cart.', 'woocommerce-gift-coupon' ),
					'desc_tip'          => true,
					'class'             => 'short',
					'type'              => 'number',
					'custom_attributes' => array(
						'step'  => 1,
						'min' => 0,
					),
					'value' => $limit_usage_to_x_items ? $limit_usage_to_x_items : '',
				) );

				// Usage limit per users.
				woocommerce_wp_text_input( array(
					'id'                => 'usage_limit_per_user',
					'label'             => __( 'Usage limit per user', 'woocommerce-gift-coupon' ),
					'placeholder'       => esc_attr__( 'Unlimited usage', 'woocommerce-gift-coupon' ),
					'description'       => __( 'How many times this coupon can be used by an individual user. Uses billing email for guests, and user ID for logged in users.', 'woocommerce-gift-coupon' ),
					'desc_tip'          => true,
					'class'             => 'short',
					'type'              => 'number',
					'custom_attributes' => array(
						'step'  => 1,
						'min' => 0,
					),
					'value' => $usage_limit_per_user ? $usage_limit_per_user : '',
				) );
				echo '</div>';
			?></div>
			<div class="clear"></div>
		</div>
		<?php
	}
}
