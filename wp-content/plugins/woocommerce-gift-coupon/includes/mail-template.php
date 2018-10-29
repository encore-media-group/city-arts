<?php
/**
 * WooCommerce Gift Coupon Mail Template
 *
 * Sets up the email template
 *
 * @author      WooCommerce Gift Coupon
 * @package     WooCommerce Gift Coupon/Mail Template
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Generate HTML PDF.
 *
 * @param array $data Coupon data.
 * @param bool  $preview Check if is preview.
 * @return string
 */
function woocommerce_gift_coupon_generate_pdf_mail( $data, $preview = false ) {
	$woocommerce_gift_coupon_hide_amount         = get_option( 'woocommerce_gift_coupon_hide_amount' );
	$woocommerce_gift_coupon_show_logo           = get_option( 'woocommerce_gift_coupon_show_logo' );
	$woocommerce_gift_coupon_logo                = get_option( 'woocommerce_gift_coupon_logo' );
	$woocommerce_gift_coupon_info_paragraph_type = get_option( 'woocommerce_gift_coupon_info_paragraph_type' );
	$woocommerce_gift_coupon_info_paragraph      = get_option( 'woocommerce_gift_coupon_info_paragraph' );
	$woocommerce_gift_coupon_info_footer         = get_option( 'woocommerce_gift_coupon_info_footer' );
	$woocommerce_gift_coupon_title_type          = get_option( 'woocommerce_gift_coupon_title_type' );
	$woocommerce_gift_coupon_title_h             = get_option( 'woocommerce_gift_coupon_title_h' );
	$woocommerce_gift_coupon_subject             = get_option( 'woocommerce_gift_coupon_subject' );
	$woocommerce_gift_coupon_bg_color_header     = get_option( 'woocommerce_gift_coupon_bg_color_header' );
	$woocommerce_gift_coupon_bg_color_footer     = get_option( 'woocommerce_gift_coupon_bg_color_footer' );
	$woocommerce_gift_coupon_bg_color_title      = get_option( 'woocommerce_gift_coupon_bg_color_title' );
	$title_coupon                                = $woocommerce_gift_coupon_title_h;
	$description_coupon                          = wpautop( $woocommerce_gift_coupon_info_paragraph );

	if ( empty( $preview ) ) {
		// Get title or description coupon.
		if ( $woocommerce_gift_coupon_title_type > 0 || $woocommerce_gift_coupon_info_paragraph_type > 0 ) {
			$data_coupon = get_post( $data['coupon_id'] );
			if ( ! empty( $data_coupon ) ) {
				// Get custom title or product coupon title.
				if ( $woocommerce_gift_coupon_title_type > 0 ) {
					$title_coupon = $data_coupon->post_title;
				}
				// Get custom description or product coupon description.
				if ( $woocommerce_gift_coupon_info_paragraph_type > 0 ) {
					$description_coupon = empty( $data_coupon->post_excerpt ) ? wp_trim_words( $data_coupon->post_content, 55, '...' ) : $data_coupon->post_excerpt;
				}
			}
		}
	} else {
		if ( empty( $title_coupon ) ) {
			$title_coupon = 'Lorem ipsum dolor sit amet';
		}
		if ( empty( $description_coupon ) ) {
			$description_coupon = 'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum.';
		}
	}

	$email  = '<!DOCTYPE html>
	<html ' . get_language_attributes() . '>
		<head>
			<meta http-equiv="Content-Type" content="text/html; charset=' . get_bloginfo( 'charset' ) . '" />
			<title>' . get_bloginfo( 'name', 'display' ) . '</title>';
	$email .= woocommerce_gift_coupon_generate_email_styles();
	$email .= '</head>
		<body bgcolor="#f5f5f5" leftmargin="0" marginwidth="0" topmargin="0" marginheight="0" offset="0">
			<table border="0" cellpadding="0" cellspacing="0" height="100%" width="100%" id="bodyTable" bgcolor="#f5f5f5">
				<tr>
					<td align="center" valign="top">
						<table bgcolor="#f5f5f5" border="0" cellpadding="0" cellspacing="0" width="595">
							<tr>
								<td align="center" valign="top">
									<table border="0" cellpadding="0" cellspacing="0" width="100%" bgcolor="#fff">
										<tr>
											<td align="center" valign="top">
												<table border="0" cellpadding="0" cellspacing="0" width="595">
													<tr>
														<td align="center" valign="top" width="595">
															<table border="0" cellpadding="20" cellspacing="0" width="100%">
																<tr>
																	<td align="center" valign="top" bgcolor="' . $woocommerce_gift_coupon_bg_color_header . '">';
	if ( $woocommerce_gift_coupon_show_logo > 0 ) {
																		$email .= '<img src="' . $woocommerce_gift_coupon_logo . '" width="190" />';
	}
																	$email .= '
																	</td>
																</tr>
																<tr>
																	<td align="center" valign="top" bgcolor="' . $woocommerce_gift_coupon_bg_color_title . '">
																		<h1>' . $title_coupon . '</h1>';
	if ( $woocommerce_gift_coupon_hide_amount < 1 ) {
																			$email .= '<h2>' . $data['price'] . '</h2>';
	}
																	$email .= '</td>
																</tr>           
																<tr>
																	<td align="center" valign="top" bgcolor="#ccc">
																		<h3>' . esc_html__( 'Code', 'woocommerce-gift-coupon' ) . ': ' . $data['code'] . '</h3>
																	</td>
																</tr>
																<tr>
																	<td align="left" valign="middle">
																		' . $description_coupon . '
																	</td>
																</tr>
															</table>
														</td>
													</tr>
												</table>
											</td>
										</tr>
									</table>
								</td>
							</tr>
						<table bgcolor="' . $woocommerce_gift_coupon_bg_color_footer . '" border="0" cellpadding="0" cellspacing="0" width="595">                        
							<tr>
								<td align="center" valign="top">      
									<table border="0" cellpadding="0" cellspacing="0" width="100%">
										<tr>
											<td align="center" valign="top">
												<table border="0" cellpadding="0" cellspacing="0" width="595">
													<tr>
														<td align="center" valign="top" width="595">
															<table border="0" cellpadding="20" cellspacing="0" width="100%">
																<tr>
																	<td align="center" valign="top">
																		' . $woocommerce_gift_coupon_info_footer . '
																	</td>
																</tr>
															</table>
														</td>
													</tr>
												</table>
											</td>   
										</tr>                             
									</table>
								</td>
							</tr>
						</table>
					</td>
				</tr>
			</table>
		</body>
	</html>';
	return $email;
}

/**
 * Add inline styles to PDF template.
 */
function woocommerce_gift_coupon_generate_email_styles() {
	$styles = '
	<style type="text/css">
		html { 
			background-color:#fff; 
			margin:0; 
			padding:0; 
		}
		body{
			height:100%!important; 
			margin:0; 
			padding:0; 
			width:100%!important;
			font-family:Helvetica, Arial, sans-serif;
		}
		table{
			border-collapse:collapse;
		}
		table[id=bodyTable] {
			table-layout: fixed;
			max-width:100%!important;
			width: 100%!important;
			min-width: 100%!important;
			margin:40px 0;color:#7A7A7A;
			font-weight:normal;
		}
		table img, 
		table a img{
			border:0; 
			outline:none;
			text-decoration:none;
			height:auto;
			line-height:100%;
		}
		table a{
			text-decoration:none!important;
			border-bottom:1px solid #ff5a34;
		}
		table a:hover{
			text-decoration:none!important;
			border-bottom:1px solid #1A242E;
		}
		table h1,
		table h2,
		table h3{
			color:#fff;
			font-weight:bold;
			line-height:100%;
			text-align:center;
			letter-spacing:normal;
			font-style: normal;
			margin:0!important;
			padding:0!important;
		}
		table h1 {
			text-transform:uppercase;
			display: block;
			font-family: Helvetica;
			font-size: 56px;
			line-height: 1.385em;
			font-weight: normal;
		}
		table h2 {
			text-transform:uppercase;
			font-size: 86px;
			display: block;
		}
		table h3 {
			color:#252525;
			line-height:100%; 
			font-size:24px;
		}
	</style>';
	return $styles;
}
