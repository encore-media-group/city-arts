<?php
/**
 * The base configuration for WordPress
 *
 * The wp-config.php creation script uses this file during the
 * installation. You don't have to use the web site, you can
 * copy this file to "wp-config.php" and fill in the values.
 *
 * This file contains the following configurations:
 *
 * * MySQL settings
 * * Secret keys
 * * Database table prefix
 * * ABSPATH
 *
 * @link https://codex.wordpress.org/Editing_wp-config.php
 *
 * @package WordPress
 */

// ** MySQL settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define('DB_NAME', 'c7167234_cadb');

/** MySQL database username */
define('DB_USER', 'c7167234_cadb');

/** MySQL database password */
define('DB_PASSWORD', 'n!0p9)Yv0S');

/** MySQL hostname */
define('DB_HOST', 'localhost');

/** Database Charset to use in creating database tables. */
define('DB_CHARSET', 'utf8');

/** The Database Collate type. Don't change this if in doubt. */
define('DB_COLLATE', '');

/**#@+
 * Authentication Unique Keys and Salts.
 *
 * Change these to different unique phrases!
 * You can generate these using the {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service}
 * You can change these at any point in time to invalidate all existing cookies. This will force all users to have to log in again.
 *
 * @since 2.6.0
 */
define('AUTH_KEY',         'x2f9ielj9uk1gr2hxskzp1ldh2kw2hgvhtscz7ysyvnjign3r3fyt5xron1gfmsq');
define('SECURE_AUTH_KEY',  'imhliktnskofgvajbw98iy6w23pzkyglvzwdimafc3l4tgilkdli1bgylkttobpi');
define('LOGGED_IN_KEY',    'qz8iuudiepgphtua9o495fsuzcbyrwfnmqmfyu7oktbqoz4rqwxhnvtu8nlcydzz');
define('NONCE_KEY',        'cyrrk1zhe03n9cj9go9uvi16narc5ebhbqs844bzbxgdzihd84xzgjqyyrph8kka');
define('AUTH_SALT',        'z38vbqpmyjcziz0kqjom1xndboasl3bkrdy92ehtfkceh87cvopppbhs4zw1kkwk');
define('SECURE_AUTH_SALT', 'atu9fzzqfhuv0dsybxdx9aeafvnuiqi5bxhyonymwrnusbcdsflaglc10dv4zg0q');
define('LOGGED_IN_SALT',   'm37jcu5dadcfgm2951vbxgp547opqvoapd405wv0ccynfjtn0sbrsgfb8subjfjy');
define('NONCE_SALT',       'mtwe1uhjwe4rmehrqigdsiq9bphghjgwlqa5cuzxxqdjh96a4cmk08oggswzoobf');

/**#@-*/

/**
 * WordPress Database Table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 */
$table_prefix  = 'wpsa_';

/**
 * For developers: WordPress debugging mode.
 *
 * Change this to true to enable the display of notices during development.
 * It is strongly recommended that plugin and theme developers use WP_DEBUG
 * in their development environments.
 *
 * For information on other constants that can be used for debugging,
 * visit the Codex.
 *
 * @link https://codex.wordpress.org/Debugging_in_WordPress
 */
define('WP_DEBUG', false);
define( 'WP_MEMORY_LIMIT', '128M' );
define( 'WP_AUTO_UPDATE_CORE', false );

/* That's all, stop editing! Happy blogging. */

/** Absolute path to the WordPress directory. */
if ( !defined('ABSPATH') )
	define('ABSPATH', dirname(__FILE__) . '/');

/** Sets up WordPress vars and included files. */
require_once(ABSPATH . 'wp-settings.php');
