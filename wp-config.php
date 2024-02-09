<?php
define( 'RSSSL_SAFE_MODE', TRUE );
define('FORCE_SSL_ADMIN', true);
if ($_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https')
 $_SERVER['HTTPS']='on';
/**
 * The base configuration for WordPress
 *
 * The wp-config.php creation script uses this file during the installation.
 * You don't have to use the web site, you can copy this file to "wp-config.php"
 * and fill in the values.
 *
 * This file contains the following configurations:
 *
 * * Database settings
 * * Secret keys
 * * Database table prefix
 * * Localized language
 * * ABSPATH
 *
 * @link https://wordpress.org/support/article/editing-wp-config-php/
 *
 * @package WordPress
 */
// ** Database settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define( 'DB_NAME', 'wordpress' );
/** Database username */
define( 'DB_USER', 'wordpress' );
/** Database password */
define( 'DB_PASSWORD', 'Hi9i7S8q' );
/** Database hostname */
define( 'DB_HOST', 'localhost' );
/** Database charset to use in creating database tables. */
define( 'DB_CHARSET', 'utf8' );
/** The database collate type. Don't change this if in doubt. */
define( 'DB_COLLATE', '' );
/**#@+
 * Authentication unique keys and salts.
 *
 * Change these to different unique phrases! You can generate these using
 * the {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service}.
 *
 * You can change these at any point in time to invalidate all existing cookies.
 * This will force all users to have to log in again.
 *
 * @since 2.6.0
 */
define( 'AUTH_KEY',          'XG |6`2-`L:G/7VfU7!~<X31Oic!Ay,0a$gTty +AGG)(>O.dw?/(&{nvM}baa%@' );
define( 'SECURE_AUTH_KEY',   'G,XqQ`*nf}(J=@W{C|sh/I/!|%0thv9#baf.:TsV2t+250`.IPdE|}EO[~5QD1;d' );
define( 'LOGGED_IN_KEY',     'q>F<.`Nnw_Avqr!7|=hm?4.#+_cx/7bQ>uv<&c2~gu{${8Ta(rbn+I)K~sIiBF4o' );
define( 'NONCE_KEY',         '9pTV!mbUz2w25)cC ;[$+kr(x2eC8CVkk!J*9E.oO8288~l)s1BasFU%@mG8$(lB' );
define( 'AUTH_SALT',         ')u%G+:KfDH-!{_tXm2_Xd<UBlH>iBMR37-l)!UN]yTVa$>bGrA|cf-V<t#w)LT`7' );
define( 'SECURE_AUTH_SALT',  'CtqW>l{ZO!4Pn+,^N)%=@EQT[tPZo~`HS|oc%cjS%&==o6nuyLjiShlyDL]=0o[q' );
define( 'LOGGED_IN_SALT',    '^m<s;;w=Q18CigdD|9(;0`vj@Fo|!8^#M+1vZ3dv<@T;i)TI.x&rwz0c!jeh0QE@' );
define( 'NONCE_SALT',        'mT,8S6?*sx:!~vUk_i$3GgIBBt-KJv]U6d55xZvMTuo l&d~k=(g6y.y| q<wk1y' );
define( 'WP_CACHE_KEY_SALT', '?G6TP}/C!5HCc=m3_B~+G@TY|cL: )k~|yOR|h9r-{3Eo=#,jzh#4,e<+[-^+jy!' );
define('WP_MEMORY_LIMIT', '256M');
define( 'WP_HOME', 'https://minime.today/' );
define( 'WP_SITEURL', 'https://minime.today/' );
/**#@-*/
/**
 * WordPress database table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 */
$table_prefix = 'wp_';
/* Add any custom values between this line and the "stop editing" line. */
/**
 * For developers: WordPress debugging mode.
 *
 * Change this to true to enable the display of notices during development.
 * It is strongly recommended that plugin and theme developers use WP_DEBUG
 * in their development environments.
 *
 * For information on other constants that can be used for debugging,
 * visit the documentation.
 *
 * @link https://wordpress.org/support/article/debugging-in-wordpress/
 */
if ( ! defined( 'WP_DEBUG' ) ) {
	define( 'WP_DEBUG', false );
}
/* That's all, stop editing! Happy publishing. */
/** Absolute path to the WordPress directory. */
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/' );
}
/** Sets up WordPress vars and included files. */
require_once ABSPATH . 'wp-settings.php';
if (isset($_SERVER['HTTP_X_FORWARDED_PROTO']))
{
    if (strpos($_SERVER['HTTP_X_FORWARDED_PROTO'], 'https') !== false)
    {
           $_SERVER['HTTPS'] = 'on';
    }
}