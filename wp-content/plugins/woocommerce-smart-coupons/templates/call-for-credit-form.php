<?php
/**
 * Call For Credit Form
 *
 * @author      StoreApps
 * @package     WooCommerce Smart Coupons/Templates
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
} ?>

<br /><br />
<div id="call_for_credit">
	<?php
		$currency_symbol = get_woocommerce_currency_symbol();
	?>
	<p style="float: left">
	<?php
	if ( ! empty( $currency_symbol ) ) {
		echo stripslashes( $smart_coupon_store_gift_page_text ) . ' (' . $currency_symbol . ')'; // WPCS: XSS ok.
	} else {
		echo stripslashes( $smart_coupon_store_gift_page_text ); // WPCS: XSS ok.
	}
		echo '</p>';
		echo "<input id='credit_called' step='any' type='number' min='1' name='credit_called' value='' autocomplete='off' autofocus />";    // This line is required in this template.
	?>
	<p id="error_message" style="color: red;"></p>
</div><br />
