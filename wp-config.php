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
define('DB_NAME', 'rvbvg');

/** MySQL database username */
define('DB_USER', 'tcf_admin');

/** MySQL database password */
define('DB_PASSWORD', 'KCPy33rSK20nQSih');

/** MySQL hostname */
define('DB_HOST', 'localhost');

/** Database Charset to use in creating database tables. */
define('DB_CHARSET', 'utf8mb4');

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
define('AUTH_KEY',         'vq{Pb.5xtsI [Q`]MmhEB;EB??}9DV3V.e/i:?%4O>=u-,Y:(7[=eP~W^2n!7]RN');
define('SECURE_AUTH_KEY',  ' <n=NONfZ5p^OwDOpDB>rVIXa3}YBp@+zz >y=p7u}GNlflB?bdzsD}t^M%)DtDA');
define('LOGGED_IN_KEY',    'rO_q}QW^kfB>bhL%d$mLK5tZ<%FbigLitlI=woq5i@-Cv3P0vvqbhpSQSs#r:F3L');
define('NONCE_KEY',        'UGqwJfMwR1L]p+Wu6S4juP+f&&m.#bm(F/Vi *(]#HMdVU:55J+x!){K+MW?y-B8');
define('AUTH_SALT',        'Djeq:q;q*X]e*<zh4CXsviI.wH)S73Q CX7$2u%z.{&8*5V?:_*-p9b3$0SrjPIy');
define('SECURE_AUTH_SALT', ':1,pnlBQExA>(#OdBAgx[{iiewvI[KAsdd@jnsOGGrI<:cFg=D%)XBffNKFnUOQ;');
define('LOGGED_IN_SALT',   'u(Cl$_sv1Q!kqc0C]w06/^{PHO Mh?q0@Oeoe9*@vg<H @K&IMlT[>q$ldH9eEO[');
define('NONCE_SALT',       'Cf(/jKK0.QBiw_+;#5(tZEqAy%qav:IE0-1ZJ|g~XEH@+H~8<oZ09P.YXrJ?-.S#');

/**#@-*/

/**
 * WordPress Database Table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 */
$table_prefix  = 'wp_';

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

/* That's all, stop editing! Happy blogging. */

/** Absolute path to the WordPress directory. */
if ( !defined('ABSPATH') )
	define('ABSPATH', dirname(__FILE__) . '/');

/** Sets up WordPress vars and included files. */
require_once(ABSPATH . 'wp-settings.php');

/* Move all media to an /assets folder */
define('UPLOADS', ''.'assets');
