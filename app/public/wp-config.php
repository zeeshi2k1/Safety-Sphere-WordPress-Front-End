<?php
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
define( 'DB_NAME', 'local' );

/** Database username */
define( 'DB_USER', 'root' );

/** Database password */
define( 'DB_PASSWORD', 'root' );

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
define( 'AUTH_KEY',          '2B=1q%;xfueP48;z<Y`#(bS7!3TqeS5L)s|PRS!HS:IRQJPh*UL)[{HTUsw5<:-#' );
define( 'SECURE_AUTH_KEY',   ';EvEm0x5zCXq ( xI#dz_<m#3|mvYta7Sol6J)i<5e{QYJxvNW@MsA;c?G&=K!eG' );
define( 'LOGGED_IN_KEY',     '#)H}Lg>D!k?u_P%7;gSO6jlV@ a!,IosHrp5?=l2kZob:Lt]!+Y#;ygNe6hQp!~H' );
define( 'NONCE_KEY',         '51cbtcmRXju{4J%UE>TYetIJwN@*,auH%oqE&?d>0m*DIe=cK:f-MwVH*A}oiS5%' );
define( 'AUTH_SALT',         '_.u _=D9LhITV+fFcNX-CJ1/XuE(U|7;)TXY.IL95W6Z#5njuUqB8yY_?IGr`XY=' );
define( 'SECURE_AUTH_SALT',  'f`/Ts[OOXg1*Om`q`]% (u]:#mJqf>woUU/5~~Ht/EF(oFz=e;~{Qw7KuXiZ:#|X' );
define( 'LOGGED_IN_SALT',    '&n`PCmsI5IN`Vw@eVUx&TEvTR7&6c mLy/N@f`:MFz$]G:x=~.=_=^)B;f9iQJVF' );
define( 'NONCE_SALT',        '3I^o)-KexT-P./C]dsr=CK%h*hvo7+s.46ie)[,/H=gzzC<w&2S##(~_[@,P&Bjx' );
define( 'WP_CACHE_KEY_SALT', 'eR0tMH1]7dupxU2wbXoSo:|Af^1_@r*0:P*w9EkiJad%DA%:|L(x1Kg9-&+b)e>)' );


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

define( 'WP_ENVIRONMENT_TYPE', 'local' );
/* That's all, stop editing! Happy publishing. */

/** Absolute path to the WordPress directory. */
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/' );
}

/** Sets up WordPress vars and included files. */
require_once ABSPATH . 'wp-settings.php';
