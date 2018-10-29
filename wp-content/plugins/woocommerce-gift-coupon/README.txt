=== WooCommerce Gift Coupon ===
Contributors: studiosweb
Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=F3E987XCMEGRQ
Tags: woocommerce, coupons, cards, gift, pdf coupon, discount coupon
Requires at least: 3.4
Tested up to: 4.9
Stable tag: 3.2.5
License: GPL2
License URI: http://www.gnu.org/licenses/gpl-2.0.html

== Description ==

Buy coupons as a product to give a friend. Generate coupon code PDF and send it by email or download manually on the user profile.

With this plugin, you can create a WooCommerce product as gift coupon to sell it in your shop for your customers to give them discount coupons for give their friends in future purchases.

[youtube https://www.youtube.com/watch?v=gucCV0_1L7s]

= What can you do with Woocommerce Gift Coupon? = 

* Create coupons as product: You can set the coupon that will sell and subsequently will generate for the customer. You can create as many coupon type products you want to sell in a shop.

* Customize your PDF´s template: You can change the style of the PDF´s coupons template to be sent to your customers while generating coupons.

* Generate coupons: At all times you can check in WooCommerce orders list, coupons that have been generated and sending for each order. In this list, the administrator have always the posibility to resend manually the coupons or get a queckly preview of the PDF.

= Contribute to us =

This plugin is proudly open source (GPL license) and we're always delighted to help you. But it´s necesary to contribute with a low **[donation](https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=F3E987XCMEGRQ "Donate us to continue developing")** apportation to continue developing with this plugin.

== Installation ==

= Automatic installation =

Automatic installation is the easiest option as WordPress handles the file transfers itself and you don't need to leave your web browser. To do an automatic install of WooCommerce Gift Coupon, log in to your WordPress dashboard, navigate to the Plugins menu and click "Add New".

In the search field type "WooCommerce Gift Coupon" and click Search Plugins. Once you have found the plugin you can view details about it such as the point release, rating and description. Most importantly of course, you can install it by simply clicking "Install Now".

= Manual installation =

The manual installation method involves downloading our donation plugin and uploading it to your server via your favorite FTP application. The WordPress codex contains [instructions on how to do this here](https://codex.wordpress.org/Managing_Plugins#Manual_Plugin_Installation).

= Updating =

Automatic updates should work like a charm; as always though, ensure you backup your site just in case.

== Frequently Asked Questions ==

= How can generate a new coupon code after a sale? = 

After customer buy a gift coupon, there are two ways to send a coupon code in a PDF attachment.

Send Manually: The administrator have the possibility to generate a new coupon code to send it by mail with PDF attachment with the code generated to use in following purchases.

Send Automatically: Coupon code can be generated automatically after order had been completed or is in processing status.

This options are on plugin settings page, which contains a selector that administrator can choose the best way to send coupons.

= Why can i not see the preview of a generated coupon =

Preview PDF coupons are only available for generated coupons from version 3.2.4. The generated coupons before this version not include this improviment.

= To which email address will the gift coupon be shipped? =

The gift coupon is sent to the buyer's email address with a PDF attachment. The PDF contains the coupon code.

= Is possible to change the format of the code? =

Is not possible change the format of the coupon code.

= To which email address will the gift coupon be shipped? =

The gift coupon is sent to the buyer's email address with a PDF attachment. The PDF contains the coupon code.

= How can I get support for WooCommerce Gift Coupon? =

We work hard to the best support possible for WooCommerce Gift Coupon. The [WordPress.org Support Forum](https://wordpress.org/support/plugin/woocommerce-gift-coupon) is used for free community based support. We continually monitor the forum and do our best to ensure everyone gets a response.

= Why it´s important to give us a donation? =

This plugin is totally free and we work hard to the best support possible for WooCommerce Gift Coupon and continue creating improvements to create a better plugin for the community. Its so gratifying to give us a [donation](https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=F3E987XCMEGRQ) for our effort

== Screenshots ==

1. Create a new product as gift coupon and configurate price, discount, limit usage etc.
2. You can generate coupons automatically on complete order or processing status or manually through the option "Generate coupons" in the WooCommerce order list and send by email with PDF attachment or download on user profile.
3. Look at your list generated coupons.
4. Customize your email template. You can add custom text or show the description and title of the product and choose your colours for your coupon.
5. Customers can check all their bought coupons on their user profile page.

== Changelog ==

= Version 3.2.5 =
*   Fixed: Admin page save options.
*		Fixed: New line in text areas are not shown in PDF.

= Version 3.2.4 =
*   Fixed: Notice errors fixed
*   Add: Preview and download coupons for the new generated PDFs
*   Add: Show generated coupons on the user profile
*   Update: Dompdf upgrade to stable version
*   Update: Compatibility with WooCommerce 3.3.3
*   Add: Preview coupon example on settings page

= Version 3.2.3 =
*   Fixed: Duplicated emails
*   Fixed: Charset info on email template
*   Fixed: Count coupons on order list
*   Fixed: Hook activation, desactivation

= Version 3.2.2 =
*   Add: Orders table styles
*   Add: Replace donate button on admin page
*   Fixed: Some translations strings
*   Change: Actions plugin links
*   Fixed: Remove new Order class from order list table
*   Change: Some code functions
*   Change: Icon, text and position admin menu

= Version 3.2.1 =
*   Fixed: Structure plugin files
*   Add: ABSPATH defined
*   Add: Languages translations

= Version 3.2 =
*   Fixed: PDF styles template
*   Add: PDF A4 portrait
*   Fixed: PDF email template configuration
*   Fixed: Empty message and subject not sended emails
*   Add: Youtube demostration video
*   Fixed: Code standards

= Version 3.1 =
*   Fixed: JS admin error on edit products
*   Fixed: Conflict with email spam
*   Fixed: Settings admin page styles
*   Add: Field email message on plugin settings admin page
*   Add: Generate coupons action link in the order list page
*   Add: Change remote donate button to a local stream
*   Add: Send emails automatically on finish payment

= Version 3.0 =
*   Fixed: JS admin error
*   Add: Option to choose the way to send coupons

= Version 2.9 =
*   Fixed: Permanlinks products
*   Fixed: CSS admin page
*   Fixed: Get WooCommerce email and from to send email
*   Add: Option to hide discount amount in the coupon template

= Version 2.8 =
*   Fixed: Bug with restrict products
*   Add: Compatibility with WooCommerce versión 3.2.6
*   Add: Selector to show in the coupon a custom description or product description
*   Add: Selector to show in the coupon a custom title or product title

= Version 2.7 =
*   Change: Remove required GiftCoupon taxonomy.
*   Add: Include Gift Coupon checkbox on product page.
*   Fixed: Relative price or percent in emails.
*   Change: Plugin structure files.

= Version 2.6 =
*   Fixed: Complete orders automatically
*   Fixed: Not send coupons on change status if are already generated
*   Fixed: CSS style on settings page

= Version 2.5 =
*   Fixed: Format price on the email template.
*   Add: Compatibility with WooCommerce versión 3.2.5
*   Fixed: Footer cellpadding template email
*   Add: README.TXT information
*   Change: Settings page information
*   Change: Styles settings page information

= Version 2.4 =
*   Add: Compatibility with WooCommerce versión 3.2.3
*   Add: Compatibility with WordPress 4.9
*   Remove: Documentation page admin
*   Fixed: Styles settings admin page
*   Add: Contribute us in settings admin page
*   Fixed: Tab spaces
*   Fixed: Not install if WooCommerce is not yet installed

= Version 2.3 =
*   Add: Can edit giftcoupon taxonomy
*   Add: Dompdf - Can attachment pdf´s coupon to email.

= Version 2.2 =
*   Add: Email template configuration
*   Add: Responsive email template
*   Add: Complete orders after payment.

= Version 2.1 =
*   Send email automatically on complete orders

= Version 2.0 =
*   Bug: Change relative prefix DB.

= Version 1.9 =
*   Fixed: Email send to spam folder

= Version 1.8 =
*   Fixed: Activation hook

= Version 1.7 =
*   Fixed: Admin menu position

= Version 1.6 =
*   Fixed: Author URL

= Version 1.5 =
*   Fixed: Color picker email

= Version 1.4 =
*   Fixed: Color picker + css

= Version 1.3 =
*   Fixed: Sanitize admin form

= Version 1.2 =
*   Fixed: Email send to spam folder

= Version 1.1 =
*   Fixed: Bug unnistall

= Version 1.0 =
*   initial version