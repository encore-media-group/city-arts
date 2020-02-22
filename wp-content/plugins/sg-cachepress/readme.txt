=== SG Optimizer ===
Contributors: Hristo Sg, siteground, sstoqnov
Tags: nginx, caching, speed, memcache, memcached, performance, siteground, nginx, supercacher
Requires at least: 4.7
Requires PHP: 5.5
Tested up to: 5.3
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

With the SiteGround Optimizer enabled, you're getting the very best from your hosting environment!

== Description ==

This plugin is designed to link WordPress with the SiteGround Performance services. It WILL NOT WORK on another hosting provider. 

The SG Optimizer plugin has few different parts handling speciffic performance optimizations:

== Configuration ==

For detailed information on our plugin and how it works, please check out our [SG Optimizer Tutorial](https://www.siteground.com/tutorials/wordpress/sg-optimizer/ "SG Optimizer Tutorial").


= SuperCacher Settings = 

In this tab, you can configure your Dynamic Caching and Memcached. Make sure you've enabled them from your cPanel before using the plugin. You can enable/disable the automatic cache purge, exclude URLs from the cache and test your pages if they are properly cached.

= Environment Optimization = 

Here, you can force HTTPS for your site, switch between different PHP Versions (compatibility check available), and enable or disable Gzip Compression and Borwser Caching rules for your site.

= Frontend Optimization =

In this tab, you can enable or disable Minification of your HTML, JS and CSS resources, remove the Emoji support and remove the query strings from your static resources.

= Image Optimization = 

Here, you can enable or disable optimization for your newly uploaded images, bulk optimize your old ones and enable lazy loading for your site images.

= Plugin Compatibility =

If your plugin does not trigger standard WordPress hooks or you need us to purge the cache, you can use this public function in your code:

	if (function_exists('sg_cachepress_purge_cache')) {
		sg_cachepress_purge_cache();
	}

Preferrably, you can pass an URL to the function to clear the cache just for it instead of purging the entire cache. For example:

	if (function_exists('sg_cachepress_purge_cache')) {
		sg_cachepress_purge_cache('https://yoursite.com/pluginpage');
	}

You can exclude styles from being combined and minified using the filters we’ve designed for that purpose. Here’s an example of the code, you can add to your functions.php file:

	add_filter( 'sgo_css_combine_exclude', 'css_combine_exclude' );
	function css_combine_exclude( $exclude_list ) {
		// Add the style handle to exclude list.
		$exclude_list[] = 'style-handle';
		$exclude_list[] = 'style-handle-2';

		return $exclude_list;
	}

	add_filter( 'sgo_css_minify_exclude', 'css_minify_exclude' );
	function css_minify_exclude( $exclude_list ) {
		// Add the style handle to exclude list.
		$exclude_list[] = 'style-handle';
		$exclude_list[] = 'style-handle-2';

		return $exclude_list;
	}

You can exclude script from being minified using the filter we’ve designed for that purpose. Here’s an example of the code, you can add to your functions.php file:

	add_filter( 'sgo_js_minify_exclude', 'js_minify_exclude' );
	function js_minify_exclude( $exclude_list ) {
		$exclude_list[] = 'script-handle';
		$exclude_list[] = 'script-handle-2';

		return $exclude_list;
	}

You can exclude script from being loaded asynchronous using the filter we’ve designed for that purpose. Here’s an example of the code, you can add to your functions.php file:

	add_filter( 'sgo_js_async_exclude', 'js_async_exclude' );
	function js_async_exclude( $exclude_list ) {
		$exclude_list[] = 'script-handle';
		$exclude_list[] = 'script-handle-2';

		return $exclude_list;
	}

You can exclude url or url that contain specific query param using the following filters:

	add_filter( 'sgo_html_minify_exclude_params', 'html_minify_exclude_params' );
	function html_minify_exclude_params( $exclude_params ) {
		// Add the query params that you want to exclude.
		$exclude_params[] = 'test';

		return $exclude_params;
	}

	add_filter( 'sgo_html_minify_exclude_urls', 'html_minify_exclude' );
	function html_minify_exclude( $exclude_urls ) {
		// Add the url that you want to exclude.
		$exclude_urls[] = 'http://mydomain.com/page-slug';

		return $exclude_urls;
	}

You can exclude images from Lazy Load using the following filter:

	add_filter( 'sgo_lazy_load_exclude_classes', 'exclude_images_with_specific_class' );
	function exclude_images_with_specific_class( $classes ) {
		// Add the class name that you want to exclude from lazy load.
		$classes[] = 'test-class';

		return $classes;
	}

= WP-CLI Support = 

In version 5.0 we've added full WP-CLI support for all plugin options and functionalities. 

* wp sg purge (url) - purges the entire cache or if URL is passed 
* wp sg memcached enable|disable - enables or disables Memcached
* wp sg forcehttps enable|disable - enables or disables HTTPS for your site
* wp sg phpver check (--version=) - checks your site for compatibility with PHP 7.1 or the version you specify
* wp sg optimize - enables or disables different optimization options for your site:
* wp sg optimize html enable|disable - enables or disables HTML minification
* wp sg optimize js enable|disable - enables or disables JS minification
* wp sg optimize css enable|disable - enables or disables CSS minification
* wp sg optimize querystring enable|disable - enables or disables query strings removal
* wp sg optimize emojis enable|disable - enables or disables stripping of the Emoji scripts
* wp sg optimize images enable|disable - enables or disables New image optimization
* wp sg optimize lazyload enable|disable - enables or disables Lazy loading of images
* wp sg optimize gzip enable|disable - enables or disables Gzip compression for your site
* wp sg optimize browsercache enable|disable - enables or disables Browser caching rules
* wp sg optimize dynamic-cache enable|disable - enables or disables Dynamic caching rules
* wp sg optimize google-fonts enable|disable - enables or disables Google Fonts Combination
* wp sg status dynamic-cache|autoflush-cache|mobile-cache|html|js|js-async|css|combine-css|querystring|emojis|images|lazyload_images|lazyload_gravatars|lazyload_thumbnails|lazyload_responsive|lazyload_textwidgets|gzip|browser-caching|memcache|ssl|ssl-fix|google-fonts - returns optimization current status (enabled|disabled)

= Requirements =

In order to work correctly, this plugin requires that your server meets the following criteria:

* SiteGround account
* WordPress 4.7
* PHP 5.5
* If you're not hosted with SiteGround this plugin WILL NOT WORK  because it relies on a specific server configuration

Our plugin uses a cookie in order to function properly. It does not store personal data and is used solely for the needs of our caching system.


== Installation ==

= Automatic Installation =

1. Go to Plugins -> Add New
1. Search for "SG CachePress"
1. Click on the Install button under the SG CachePress plugin
1. Once the plugin is installed, click on the Activate plugin link

= Manual Installation =

1. Login to the WordPress admin panel and go to Plugins -> Add New
1. Select the 'Upload' menu 
1. Click the 'Choose File' button and point your browser to the SGCachePress.zip file you've downloaded
1. Click the 'Install Now' button
1. Go to Plugins -> Installed Plugins and click the 'Activate' link under the WordPress SG CachePress listing

== Changelog ==

= Version 5.4.3 =
* Added Lazy loading functionality for iframes
* Added Lazy loading functionality for videos

= Version 5.4.2 =
* Fixed bug with WebP image regeneration on image delete

= Version 5.4.1 =
* Added PHP 7.4 support for PHP Compatibility Checker
* Improved WebP Conversion
* Fixed bug with WebP image regeneration on image edit
* Improved plugin localization

= Version 5.4.0 =
* Added WebP Support on All Accounts on Site Tools
* Added Google PageSpeed Test 
* Improved Image Optimization Process
* Improved SSL Certificate check

= Version 5.3.10 =
* Better PHP Version Management for Site Tools
* NGINX Direct Delivery for Site Tools

= Version 5.3.9 =
* Improved check for SG Servers

= Version 5.3.8 =
* Fixed a bug when Memcached fails to purge when new WordPress version requiring a database update is released
* Added alert and check if you’re running SG Optimizer on a host different than SiteGround
* Improved compatibility with WooCommerce
* Improved conditional styles combination
* Improved image optimization process

= Version 5.3.7 =
* Added WooCommerce Square Payment & Braintree For WooCommerce Exclude by Default
* Improved Google Fonts Optimization
* Added Notice for Defer Render-Blocking Scripts Optimization
* Added wp-cli commands for Google Fonts Optimization
* Changed New Images Optimizer hook to wp_generate_attachment_metadata

= Version 5.3.6 =
* Improved Google Fonts loading with better caching
* Improved Defer of render-blocking JS

= Version 5.3.5 =
* WordPress 5.3 Support Declared
* Better Elementor Compatibility
* Better Image Optimization Messaging
* Better Google Fonts combination
* Added PHP 7.4 support

= Version 5.3.4 =
* Improved Async load of JS files
* Added Google Fonts Combination optimization
* Moved lazyload script in footer
* Improved CSS combination

= Version 5.3.3 =
* Improved browser cache handling upon plugin update
* Added wp-cli commands for Dynamic Cache, Autoflush and Browser-Speciffic cache handling

= Version 5.3.2 =
* Fixed bug with https enforce for www websites
* Improved JILT support

= Version 5.3.1 =
* Better SSL force to accommodate websites with WWW in the URL
* Global exclusion of siteorigin-widget-icon-font-fontawesome from Combine CSS

= Version 5.3.0 =
* Refactoring of the Lazy Load functionality
* Redesign of the Lazy Load screen
* Improved WooCommerce product image Lazy Load
* Gzip functionality update for Site Tools accounts
* Browser caching functionality update for Site Tools accounts
* Improved Browser caching functionality for cPanel accounts

= Version 5.2.5 =
* New Feature: Option to split caches per User Agent
* New Feature: Option to disable lazy loading for mobile devices
* Improved Memcached check

= Version 5.2.4 =
* Improved XML RCP checks compatibility

= Version 5.2.3 =
* Improved LazyLoad

= Version 5.2.2 =
* Improved Events Calendar Compatibility
* Suppressed notices in the REST API in certain cases
* Improved nonscript tag in LazyLoad

= Version 5.2.1 =
* Improved Cloudflare compatibility

= Version 5.2.0 =
* Exclude list Interface for JavaScript handlers
* Exclude list Interface for CSS handlers
* Exclude list Interface for HTML minification (URL like dynamic)
* Exclude list interface for LazyLoading (Class)
* Improved Thrive Architect support
* Fixed notice when purging comment cache

= Version 5.1.3 =
* Improved Elementor support
* Improved CSS optimization for inclusions without protocol
* Excluded large PNGs from optimizations
* Added better WP-CLI command documentation

= Version 5.1.2 =
* Added support for Recommended by SiteGround PHP Version
* Improved LazyLoad Support for WooCommerce sites
* Improved Image Optimization checks
* Improved PHP Version switching checks
* Added wp cli status command for checking optimization status
* Fixed bug with Combine CSS

= Version 5.1.1 =
* Improved cache invalidation for combined styles
* Cache purge from the admin bar now handles combined files too
* Added filter to exclude images from Lazy Loading
* Added filter to exclude pages from HTML Minification
* Added Filter to query params from HTML Minification
* Added PHP 7.3 support

= Version 5.1.0 =
* Added CSS Combination Functionality
* Added Async Load of Render-Blocking JS
* Added WooCommerce Support for LazyLoad
* Added Filter to Exclude Styles from CSS Combination
* Improved Lazy Load Functionality on Mobile Devices
* Fixed Issue with WP Rocket’s .htaccess rules and GZIP
* Fixed Issue with Query String Removal Script in the Admin Section
* Fixed Compatibility Issues with 3rd Party Plugins and Lazy Load
* Fixed Compatibility Issues with Woo PDF Catalog Plugin and HTML Minification
* Improved Memcached Reliability
* Improved Lazy Load for Responsive Images

= Version 5.0.13 =
* Modified HTML minification to keep comments
* Interface Improvements
* Better input validation and sanitation for PHP Version check
* Improved security

= Version 5.0.12 =
* Better cache purge for multisite
* Surpress dynamic cache notices for localhost sites

= Version 5.0.11 =
* Improved handling of third party plugins causing issues with the compatibility checker functionality
* Optimized WP-CLI commands for better performance
* Better notice handling for Multisite and conflicting plugins

= Version 5.0.10 =
* Fixed issue with Mythemeshop themes
* Fixed issues with exclude URL on update
* Fixed issues with exclude URL on update
* Exclude Lazy Load from AMP pages
* Exclude Lazy Load from Backend pages
* Fixed WPML problems
* Fixed Beaver Builder issues
* Fixed Spanish translations
* Fixed incompatibility with JCH Optimize

= Version 5.0.9 =
* Fixed woocommerce bugs
* Improved memcached flush
* Improved https force

= Version 5.0.8 =
* Better .htaccess handling when disabling and enabling Browser Cache and Gzip
* Improved image optimization handling
* Added option to stop the image optimization and resume it later
* Fixed bug with memcached notifications
* Fixed bug with conflicting plugin notices for non-admins
* Fixed bug when user accesses their site through IP/~cPaneluser
* Fixed bug with labels for HTML, CSS & JS Minification
* SEO Improvements in the Lazy Load functionality

= Version 5.0.7 =
* Fixed bug with notifications removal
* Fixed bug with modifying wrong .htaccess file for installations in subdirectory
* Flush redux cache when updating to new version 
* Improved check for existing SSL rules in your .htaccess file
* Added check and removal of duplicate Gzip rules in your .htaccess file
* Added check and removal of duplicate Browser caching  rules in your .htaccess file

= Version 5.0.6 =
* Memcache issues fixed. Unique WP_CACHE_KEY_SALT is generated each time you enable it on your site.
* Better status update handling
* Added option to start checks even if the default WP Cron is disabled (in case you use real cronjob)

= Version 5.0.5 =
* Fixed Compatibility Checker progress issues.
* Fixed images optimization endless loops.
* Changed php version regex to handle rules from other plugins.

= Version 5.0.4 =
* Fixed CSS minification issues.
* Add option to re-optimize images.
* Allow users to hide notices.

= Version 5.0.0 =
* Complete plugin refactoring
* Frontend optimiztions added
* Environment optimizations added
* Images Optimizatoins adder
* Full WP-CLI Support
* Better Multisite Support
* Better Interface

= Version 4.0.7 =
* Fixed bug in the force SSL functionality in certain cases for MS
* Added information about the cookie our plugin uses in the readme file

= Version 4.0.6 =
* Bug fixes
* Better https enforcement in MS environment

= Version 4.0.5 =
* Removed stopping of WP Rocket cache

= Version 4.0.4 =
* Minor bug fixes

= Version 4.0.3 =
* Switching recommended PHP Version to 7.1

= Version 4.0.2 =
* WPML and Memcache / Memcached bug fix

= Version 4.0.1 =
* Minor bug fixes
* UK locale issue fixed

= Version 4.0.0 =
* Added proper Multisite support
* Quick optimizations - Gzip and Browser cache config settings for the Network Admin
* Network admin can purge the cache per site 
* Network admin can disallow Cache and HTTPS configuration pages per site
* WPML support when Memcached is enabled
* Cache is being purged per site and not for the entire network
* Multiple performance & interface improvements
* Security fixes against, additional access checks introduced
* Fixed minor cosmetic errors in the interface

= Version 3.3.3 =
* Fixed minor interface issues

= Version 3.3.2 =
* Fixed bug with disabling the Force HTTPS option

= Version 3.3.1 =
* Fixed cache purge issue when CloudFlare is enabled
* Added logging of failed attempts in XMLRPC API.

= Version 3.3.0 =
* Improved public purge function for theme and plugin developers
* Added WP-CLI command for cache purge - wp sg purge

= Version 3.2.4 =
* Updated Memcache.tpl
* Fixed a link in the PHP Check interface

= Version 3.2.3 =
* Improved WP-CLI compatibility

= Version 3.2.1 =
* Improved cron fallback, added error message if the WP CRON is disabled

= Version 3.2.0 =
* Adding PHP 7.0 Compatibility check & PHP Version switch

= Version 3.0.5 =
* Improved Certficiate check

= Version 3.0.4 =
* Fixed bug with unwrittable .htaccess

= Version 3.0.3 =
* Fixed bug in adding CSS files

= Version 3.0.2 =
* User-agent added to the SSL availability check

= Version 3.0.1 =
* PHP Compatibility fixes

= Version 3.0.0 =

* Plugin renamed to SG Optimizer
* Interface split into multiple screens
* HTTPS Force functionality added which will reconfigure WordPress, make an .htaccess redirect to force all the traffic through HTTPS and fixes any potential insecure content issues
* Plugin prepared for PHP version compatibility checker and changer tool

= Version 2.3.11 =
* Added public purge function
* Memcached bug fixes

= Version 2.3.10 =
* Improved Memcached performance
* Memcached bug fixes

= Version 2.3.9 =
* Improved WordPress 4.6 compatibilitty

= Version 2.3.8 =
* Improved compatibility with SiteGround Staging System

= Version 2.3.7 =
* Fixed PHP warnings in Object Cache classes

= Version 2.3.6 =
* Minor URL handling bug fixes

= Version 2.3.5 =
* Improved cache testing URL detection

= Version 2.3.4 =
* CSS Bug fixes

= Version 2.3.3 =
* Improved Memcache work
* Interface improvements
* Bug fixes

= Version 2.3.2 =
* Fixed bug with Memcached cache purge

= Version 2.3.1 =
* Interface improventes
* Internationalization support added
* Spanish translation added by <a href="https://www.siteground.es">SiteGround.es</a>
* Bulgarian translation added

= Version 2.3.0 =
* Memcached support added
* Better PHP7 compatibility

= Version 2.2.11 =
* Improved compatibility with WP Rocket
* Bug fixes

= Version 2.2.10 =
* Revamped notices work
* Bug fixes

= Version 2.2.9 =
* Bug fixes

= Version 2.2.8 =
* Bug fixing and improved notification behaviour
* Fixed issues with MS installations

= Version 2.2.7 =
* Added testing box and notification if Dynamic Cache is not enabled in cPanel

= Version 2.2.6 =
* Fixed bug with Memcached causing issues after WP Database update

= Version 2.2.5 =
* Minor system improvements

= Version 2.2.4 =
* Minor system improvements

= Version 2.2.3 =
* Admin bar link visible only for admin users

= Version 2.2.2 =
* Minor bug fixes

= Version 2.2.1 =
* Added Purge SG Cache button
* Redesigned mobile-friendly interface

= Version 2.2.0 =
* Added NGINX support

= Version 2.1.7 =
* Fixed plugin activation bug

= Version 2.1.6 =
* The purge button will now clear the Static cache even if Dynamic cache is not enabled
* Better and more clear button labeling

= Version 2.1.5 =
* Better plugin activation and added to the wordpress.org repo

= Version 2.1.2 =
* Fixed bug that prevents you from enabling Memcached if using a wildcard SSL Certificate

= Version 2.1.1 =
* Cache will flush when scheduled posts become live

= Version 2.1.0 =
* Cache will be purged if WordPress autoupdates

= Version 2.0.3 =
* Minor bug fixes

= Version 2.0.2 =
* 3.8 support added

= Version 2.0.1 =
* Interface improvements
* Minor bug fixes

= Version 2.0 =
* New interface
* Minor bug fixes
* Settings and Purge pages combined into one

= Version 1.2.3 =
* Minor bug fixes
* SiteGround Memcached support added
* URL Exclude from caching list added

= 1.0 =
* Plugin created.

== Screenshots ==

1. The Super Cacher Settings tab handles your Dynamic caching and Memcached. Here, you can exclude URls from the cache, test your site and purge the Dynamic caching manually.
2. In the Environment Optimization tab, you can force HTTPS for your site, switch PHP versions and enable Gzip and Browser Caching rules.
3. The Frontend Optimization tab allows you to Minify HTML, CSS & JS, as well as to remove query strings from your static resources and disable the Emoji support.
4. The Image Optimization tab allows you to optimize your Media Library images, aswell as adds Lazy Loading functionality for your site.
5. Multisite Only! In the Global Settings tab, you can configure all options that are global for your network.
6. Multisite Only! In the Per Site Defaults tab, you can configure how the new sites, added to your network will be setup.
