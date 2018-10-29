<?php
/**
 * WooCommerce Gift Coupon Options Admin Page Settings
 *
 * Sets up the options plugin.
 *
 * @author      WooCommerce Gift Coupon
 * @package     WooCommerce Gift Coupon/Options Admin Page Settings
 */

$max_upload_size = wp_max_upload_size();
if ( ! $max_upload_size ) {
	$max_upload_size = 0;
}

if ( ! empty( $_POST['submit'] ) ) {

	$woocommerce_gift_coupon_send                = isset( $_POST['woocommerce_gift_coupon_send'] ) ? $_POST['woocommerce_gift_coupon_send'] : null;
	$woocommerce_gift_coupon_show_logo           = isset( $_POST['woocommerce_gift_coupon_show_logo'] ) ? $_POST['woocommerce_gift_coupon_show_logo'] : null;
	$woocommerce_gift_coupon_hide_amount         = isset( $_POST['woocommerce_gift_coupon_hide_amount'] ) ? $_POST['woocommerce_gift_coupon_hide_amount'] : null;
	$woocommerce_gift_coupon_logo                = isset( $_FILES['woocommerce_gift_coupon_logo'] ) ? $_FILES['woocommerce_gift_coupon_logo'] : null;
	$woocommerce_gift_coupon_info_paragraph_type = sanitize_text_field( isset( $_POST['woocommerce_gift_coupon_info_paragraph_type'] ) ? esc_html( trim( $_POST['woocommerce_gift_coupon_info_paragraph_type'] ) ) : 0 );
	$woocommerce_gift_coupon_info_paragraph      = isset( $_POST['woocommerce_gift_coupon_info_paragraph'] ) ? stripslashes( wp_filter_post_kses( addslashes( $_POST['woocommerce_gift_coupon_info_paragraph'] ) ) ) : null;
	$woocommerce_gift_coupon_info_footer         = isset( $_POST['woocommerce_gift_coupon_info_footer'] ) ? stripslashes( wp_filter_post_kses( addslashes( $_POST['woocommerce_gift_coupon_info_footer'] ) ) ) : null;
	$woocommerce_gift_coupon_title_type          = sanitize_text_field( isset( $_POST['woocommerce_gift_coupon_title_type'] ) ? esc_html( trim( $_POST['woocommerce_gift_coupon_title_type'] ) ) : 0 );
	$woocommerce_gift_coupon_title_h             = isset( $_POST['woocommerce_gift_coupon_title_h'] ) ? stripslashes( wp_filter_post_kses( addslashes( $_POST['woocommerce_gift_coupon_title_h'] ) ) ) : null;
	$woocommerce_gift_coupon_subject             = sanitize_text_field( isset( $_POST['woocommerce_gift_coupon_subject'] ) ? esc_html( trim( $_POST['woocommerce_gift_coupon_subject'] ) ) : null );
	$woocommerce_gift_coupon_email_message       = sanitize_text_field( isset( $_POST['woocommerce_gift_coupon_email_message'] ) ? esc_html( trim( $_POST['woocommerce_gift_coupon_email_message'] ) ) : null );
	$woocommerce_gift_coupon_bg_color_header     = sanitize_text_field( isset( $_POST['woocommerce_gift_coupon_bg_color_header'] ) ? esc_html( trim( $_POST['woocommerce_gift_coupon_bg_color_header'] ) ) : null );
	$woocommerce_gift_coupon_bg_color_footer     = sanitize_text_field( isset( $_POST['woocommerce_gift_coupon_bg_color_footer'] ) ? esc_html( trim( $_POST['woocommerce_gift_coupon_bg_color_footer'] ) ) : null );
	$woocommerce_gift_coupon_bg_color_title      = sanitize_text_field( isset( $_POST['woocommerce_gift_coupon_bg_color_title'] ) ? esc_html( trim( $_POST['woocommerce_gift_coupon_bg_color_title'] ) ) : null );

	if ( $woocommerce_gift_coupon_logo['error'] < 1 ) {
		$file = wp_upload_bits( $_FILES['woocommerce_gift_coupon_logo']['name'], null, @file_get_contents( $_FILES['woocommerce_gift_coupon_logo']['tmp_name'] ) );
				update_option( 'woocommerce_gift_coupon_logo', $file['url'] );
	}

	update_option( 'woocommerce_gift_coupon_show_logo', $woocommerce_gift_coupon_show_logo );
	update_option( 'woocommerce_gift_coupon_hide_amount', $woocommerce_gift_coupon_hide_amount );
	update_option( 'woocommerce_gift_coupon_send', $woocommerce_gift_coupon_send );
	update_option( 'woocommerce_gift_coupon_info_paragraph_type', $woocommerce_gift_coupon_info_paragraph_type );
	update_option( 'woocommerce_gift_coupon_info_paragraph', $woocommerce_gift_coupon_info_paragraph );
	update_option( 'woocommerce_gift_coupon_info_footer', $woocommerce_gift_coupon_info_footer );
	update_option( 'woocommerce_gift_coupon_title_type', $woocommerce_gift_coupon_title_type );
	update_option( 'woocommerce_gift_coupon_title_h', $woocommerce_gift_coupon_title_h );
	update_option( 'woocommerce_gift_coupon_subject', $woocommerce_gift_coupon_subject );
	update_option( 'woocommerce_gift_coupon_email_message', $woocommerce_gift_coupon_email_message );
	update_option( 'woocommerce_gift_coupon_bg_color_header', $woocommerce_gift_coupon_bg_color_header );
	update_option( 'woocommerce_gift_coupon_bg_color_footer', $woocommerce_gift_coupon_bg_color_footer );
	update_option( 'woocommerce_gift_coupon_bg_color_title', $woocommerce_gift_coupon_bg_color_title );

	print '<div id="message" class="updated inline updated"><p><strong>';
		esc_html_e( 'Your settings have been saved.' );
	print '</strong></p></div>';

}

$woocommerce_gift_coupon_show_logo           = get_option( 'woocommerce_gift_coupon_show_logo' );
$woocommerce_gift_coupon_hide_amount         = get_option( 'woocommerce_gift_coupon_hide_amount' );
$woocommerce_gift_coupon_logo                = get_option( 'woocommerce_gift_coupon_logo' );
$woocommerce_gift_coupon_send                = get_option( 'woocommerce_gift_coupon_send' );
$woocommerce_gift_coupon_info_paragraph_type = get_option( 'woocommerce_gift_coupon_info_paragraph_type' );
$woocommerce_gift_coupon_info_paragraph      = get_option( 'woocommerce_gift_coupon_info_paragraph' );
$woocommerce_gift_coupon_info_footer         = get_option( 'woocommerce_gift_coupon_info_footer' );
$woocommerce_gift_coupon_title_h             = get_option( 'woocommerce_gift_coupon_title_h' );
$woocommerce_gift_coupon_title_type          = get_option( 'woocommerce_gift_coupon_title_type' );
$woocommerce_gift_coupon_subject             = get_option( 'woocommerce_gift_coupon_subject' );
$woocommerce_gift_coupon_email_message       = get_option( 'woocommerce_gift_coupon_email_message' );
$woocommerce_gift_coupon_bg_color_header     = get_option( 'woocommerce_gift_coupon_bg_color_header' );
$woocommerce_gift_coupon_bg_color_footer     = get_option( 'woocommerce_gift_coupon_bg_color_footer' );
$woocommerce_gift_coupon_bg_color_title      = get_option( 'woocommerce_gift_coupon_bg_color_title' );

if ( $woocommerce_gift_coupon_show_logo > 0 ) {
	$checked_show_logo = 'checked';
} else {
	$checked_show_logo = false;
}

if ( $woocommerce_gift_coupon_hide_amount > 0 ) {
	$checked_hide_amount = 'checked';
} else {
	$checked_hide_amount = false;
}

$settings_tinymce = array(
	'_content_editor_dfw'  => 1,
	'drag_drop_upload'     => true,
	'tabfocus_elements'    => 'content-html,save-post',
	'editor_height'        => 150,
	'tinymce'              => array(
		'resize'             => false,
		'wp_autoresize_on'   => 1,
		'add_unload_trigger' => false,
	),
	'media_buttons'        => false,
);
?>

<div class="container">
	<div class="notice-box">
		<div class="header">
			<h1><?php esc_html_e( 'Help us', 'woocommerce-gift-coupon' ); ?></h1>
		</div>
		<div clas="box-info">
			<?php esc_html_e( "This plugin is proudly open source (GPL license) and we're always delighted to help you. But it´s necesary to contribute with a low donation to continue developing this plugin for create a new improvements and continue to give you a maintence. Thanks a lot!", 'woocommerce-gift-coupon' ); ?> <p />
			<a href="https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=F3E987XCMEGRQ"><span class="donate-button"><?php esc_html_e( 'Donate', 'woocommerce-gift-coupon' ); ?></span></a>
		</div>
	</div>
	<form name="woocommerce_gift_coupon_form" method="post" enctype="multipart/form-data" action="<?php echo str_replace( '%7E', '~', $_SERVER['REQUEST_URI'] ); ?>">
		<div class="wgc-box basic-information">
			<div class="header medium">
				<span class="step">1</span><h2><?php esc_html_e( 'Basic configuration:', 'woocommerce-gift-coupon' ); ?></h2>
			</div>
			<div class="wgc-box-body">
				<table class="form-table">
					<tr>
						<th>
							<?php esc_html_e( 'How to send coupons', 'woocommerce-gift-coupon' ); ?>
						</th>
						<td>
							<select name="woocommerce_gift_coupon_send" id="woocommerce_gift_coupon_send">
								<?php
									$order_status = array(
										0 => __( 'Generate coupons mannually', 'woocommerce-gift-coupon' ),
										1 => __( 'Generate coupons automatically on complete order status', 'woocommerce-gift-coupon' ),
										2 => __( 'Generate coupons automatically on processing order status', 'woocommerce-gift-coupon' ),
									);
									foreach ( $order_status as $key => $order ) {
										if ( isset( $woocommerce_gift_coupon_send ) && $woocommerce_gift_coupon_send == $key ) {
											$selected = 'selected';
										} else {
											$selected = false;
										}
										print '<option value="' . $key . '" ' . $selected . '>' . $order . '</option>';
									}
								?>
							</select>
						</td>
					</tr>
				</table>
			</div>
		</div>
		<div class="wgc-box">
			<div class="header medium">
				<span class="step">2</span><h2><?php esc_html_e( 'Email configuration:', 'woocommerce-gift-coupon' ); ?></h2>
			</div>
			<div class="wgc-box-body">
				<table class="form-table">
					<tr>
						<th>
							<label for="woocommerce_gift_coupon_subject"><?php esc_html_e( 'Subject:', 'woocommerce-gift-coupon' ); ?></label>
						</th>
						<td>
							<input type="text" id="woocommerce_gift_coupon_subject" name="woocommerce_gift_coupon_subject" value="<?php echo $woocommerce_gift_coupon_subject; ?>" />
						</td>
					</tr>
					<tr>
						<th>
							<label for="woocommerce_gift_coupon_email_message"><?php esc_html_e( 'Message:', 'woocommerce-gift-coupon' ); ?></label>
						</th>
						<td>
							<?php wp_editor( $woocommerce_gift_coupon_email_message, 'woocommerce_gift_coupon_email_message', $settings_tinymce ); ?>
						</td>
					</tr>
				</table>
			</div>
		</div>
		<div class="wgc-box">
			<div class="header medium">
				<span class="step">3</span><h2><?php esc_html_e( 'Coupon PDF configuration:', 'woocommerce-gift-coupon' ); ?></h2>
			</div>
			<div class="wgc-box-body">
				<table class="form-table">
					<tr>
						<th>
							<?php esc_html_e( 'Show logo:', 'woocommerce-gift-coupon' ); ?>
						</th>
						<td>
							<fieldset>
								<label for="woocommerce_gift_coupon_show_logo">
									<input type="checkbox" id="woocommerce_gift_coupon_show_logo" name="woocommerce_gift_coupon_show_logo" value="1" <?php echo $checked_show_logo; ?> />
									<?php esc_html_e( 'Show logo in the header on coupon template', 'woocommerce-gift-coupon' ); ?>
								</label>
							</fieldset>
						</td>
					</tr>
					<tr>
						<th>
							<label for="woocommerce_gift_coupon_logo">
								<?php esc_html_e( 'Logo', 'woocommerce-gift-coupon' ); ?> (<span class="max-upload-size"><?php printf( __( 'Max. size: %s', 'woocommerce-gift-coupon' ), esc_html( size_format( $max_upload_size ) ) ); ?></span>)
							</label>
						</th> 
						<td>
							<input type="file" name="woocommerce_gift_coupon_logo" id="woocommerce_gift_coupon_logo"  multiple="false" />
							<?php if ( ! empty( $woocommerce_gift_coupon_logo ) ) : ?>
								<img class="logo" src="<?php echo $woocommerce_gift_coupon_logo; ?>" width="25" />
							<?php endif; ?>
							<?php wp_nonce_field( plugin_basename( __FILE__ ), 'woocommerce_gift_coupon_logo' ); ?>
						</td>
					</tr>
					<tr>
						<th>
							<?php esc_html_e( 'Hide discount amount:', 'woocommerce-gift-coupon' ); ?>
						</th>
						<td>
							<fieldset>
								<label for="woocommerce_gift_coupon_hide_amount">
									<input type="checkbox" id="woocommerce_gift_coupon_hide_amount" name="woocommerce_gift_coupon_hide_amount" value="1" <?php echo $checked_hide_amount; ?> />
									<?php esc_html_e( 'Hide the quantity of the discount on coupon template', 'woocommerce-gift-coupon' ); ?>
								</label>                            
							</fieldset>
						</td>
					</tr>
					<tr>
						<th>
							<label for="woocommerce_gift_coupon_title_type"><?php esc_html_e( 'Title:', 'woocommerce-gift-coupon' ); ?></label>
						</th>
						<td>
							<select name="woocommerce_gift_coupon_title_type" id="woocommerce_gift_coupon_title_type">
								<?php
									$title_types = array(
										0 => __( 'Show custom title', 'woocommerce-gift-coupon' ),
										1 => __( 'Show product coupon title', 'woocommerce-gift-coupon' ),
									);
									foreach ( $title_types as $key => $type ) {
										if ( isset( $woocommerce_gift_coupon_title_type ) && $woocommerce_gift_coupon_title_type == $key ) {
											$selected = 'selected';
										} else {
											$selected = false;
										}
										print '<option value="' . $key . '" ' . $selected . '>' . $type . '</option>';
									}
								?>
							</select>
						</td>
					</tr>
					<tr>
						<th></th>
						<td>
							<?php wp_editor( $woocommerce_gift_coupon_title_h, 'woocommerce_gift_coupon_title_h', $settings_tinymce ); ?>
						</td>
					</tr>
					<tr>
						<th>
							<label for="woocommerce_gift_coupon_info_paragraph_type"><?php esc_html_e( 'Description:', 'woocommerce-gift-coupon' ); ?></label>
						</th>
						<td>
							<select name="woocommerce_gift_coupon_info_paragraph_type" id="woocommerce_gift_coupon_info_paragraph_type">
								<?php
									$title_types = array(
										0 => __( 'Show custom description', 'woocommerce-gift-coupon' ),
										1 => __( 'Show product coupon description', 'woocommerce-gift-coupon' ),
									);
									foreach ( $title_types as $key => $type ) {
										if ( isset( $woocommerce_gift_coupon_info_paragraph_type ) && $woocommerce_gift_coupon_info_paragraph_type == $key ) {
											$selected = 'selected';
										} else {
											$selected = false;
										}
										print '<option value="' . $key . '" ' . $selected . '>' . $type . '</option>';
									}
								?>
							</select>
						</td>
					</tr>
					<tr>
						<th></th>
						<td>
							<?php wp_editor( $woocommerce_gift_coupon_info_paragraph, 'woocommerce_gift_coupon_info_paragraph', $settings_tinymce ); ?>
						</td>
					</tr>
					<tr>
						<th>
							<label for="woocommerce_gift_coupon_info_footer"><?php esc_html_e( 'Footer:', 'woocommerce-gift-coupon' ); ?></label>
						</th>
						<td>
							<?php wp_editor( $woocommerce_gift_coupon_info_footer, 'woocommerce_gift_coupon_info_footer', $settings_tinymce ); ?>
						</td>
					</tr>
						<tr>
						<th>
							<label for="woocommerce_gift_coupon_bg_color_header"><?php esc_html_e( 'Logo area color:', 'woocommerce-gift-coupon' ); ?></label>
						</th>
						<td>
							<input type="text" id="woocommerce_gift_coupon_bg_color_header" class="woocommerce-gift-coupon-color" name="woocommerce_gift_coupon_bg_color_header" id="woocommerce_gift_coupon_bg_color_header" value="<?php echo $woocommerce_gift_coupon_bg_color_header; ?>">
						</td>
					</tr>
					<tr>
						<th>
							<label for="woocommerce_gift_coupon_bg_color_title"><?php esc_html_e( 'Title area color:', 'woocommerce-gift-coupon' ); ?></label>
						</th>
						<td>
							<input type="text" id="woocommerce_gift_coupon_bg_color_title" class="woocommerce-gift-coupon-color" name="woocommerce_gift_coupon_bg_color_title" id="woocommerce_gift_coupon_bg_color_title" value="<?php echo $woocommerce_gift_coupon_bg_color_title; ?>">
						</td>
					</tr>
					<tr>
						<th>
							<label for="woocommerce_gift_coupon_bg_color_footer"><?php esc_html_e( 'Footer area color:', 'woocommerce-gift-coupon' ); ?></label>
						</th>
						<td>
							<input type="text" id="woocommerce_gift_coupon_bg_color_footer" class="woocommerce-gift-coupon-color" name="woocommerce_gift_coupon_bg_color_footer" id="woocommerce_gift_coupon_bg_color_footer" value="<?php echo $woocommerce_gift_coupon_bg_color_footer; ?>">
						</td>
					</tr>
					<tr>
						<th>
							<label><?php esc_html_e( 'Coupon preview example:', 'woocommerce-gift-coupon' ); ?></label>
						</th>
						<td><a href="<?php print admin_url( 'admin.php?page=woocommerce_gift_coupon_download_coupon_pdf' ); ?>" target="_blank"><?php esc_html_e( 'Preview saved template', 'woocommerce-gift-coupon' ); ?></a></td>
					</tr>
				</table>
			</div>
		</div>
		<div class="block">
			<p class="submit">
				<input type="submit" class="button button-primary" name="submit" id="submit" value="<?php esc_html_e( 'Save options', 'woocommerce-gift-coupon' ); ?>" />
			</p>
		</div>
	</form>
</div>
