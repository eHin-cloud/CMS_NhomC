<?php
/**
 * The base configuration for WordPress
 *
 * The wp-config.php creation script uses this file during the installation.
 * You don't have to use the website, you can copy this file to "wp-config.php"
 * and fill in the values.
 *
 * This file contains the following configurations:
 *
 * * Database settings
 * * Secret keys
 * * Database table prefix
 * * ABSPATH
 *
 * @link https://developer.wordpress.org/advanced-administration/wordpress/wp-config/
 *
 * @package WordPress
 */

// ** Database settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define( 'DB_NAME', getenv( 'WORDPRESS_DB_NAME' ) ?: 'cms_nhomc' );

/** Database username */
define( 'DB_USER', getenv( 'WORDPRESS_DB_USER' ) ?: 'root' );

/** Database password */
define( 'DB_PASSWORD', getenv( 'WORDPRESS_DB_PASSWORD' ) !== false ? getenv( 'WORDPRESS_DB_PASSWORD' ) : '' );

/** Database hostname */
define( 'DB_HOST', getenv( 'WORDPRESS_DB_HOST' ) ?: 'localhost' );

/** Database charset to use in creating database tables. */
define( 'DB_CHARSET', 'utf8mb4' );

/** The database collate type. Don't change this if in doubt. */
define( 'DB_COLLATE', '' );

/** Cấu hình Virtual Host wordpressc.local */
if ( ! defined( 'WP_HOME' ) ) {
    define( 'WP_HOME', 'http://wordpressc.local' );
    define( 'WP_SITEURL', 'http://wordpressc.local' );
}

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
define( 'AUTH_KEY',         ' CA*F>kDwqXJDK-S )|]R>d%}U7VnW]N/Et%iif^,JcP#G#+tL.{@]5 A,B*t/`p' );
define( 'SECURE_AUTH_KEY',  'z7lPb4*ba&uDa42{N:?y|n)m2@J^9gk.h_b1&Xr,Dy!;2iS)}>eq?O`r)HU)<7A~' );
define( 'LOGGED_IN_KEY',    'U2Dswb%gJ|XD 4VT&a>LArEXHg,8/q%Rq9vh2/s_Tx^9r]yA.{9R]Hvd= 5=!c?<' );
define( 'NONCE_KEY',        'l]K;-XE*h$|u28qB8 :!c~j*E;uAJn3Rk rE]$m*Z63d!IWsjVL^Oa2v{%mvJK, ' );
define( 'AUTH_SALT',        '_D}MM^e$]_{XX3S`wp#g`DU2:YAN_MrafaE.F8Y9QI=T!Z3M_ZSK@j;}T1#%6zYb' );
define( 'SECURE_AUTH_SALT', 'S~B#laPLYJZqSmkRm/CaLv;o]yB=W5Ns/-xl78NMi/@NmRe{e~Tweu_^Qg}>s^}]' );
define( 'LOGGED_IN_SALT',   'JR}GQn=`=)q1v0A>4d#5C9WtyAvZ^%}_S,tbNI[&}q]*v[0|8Rrf0Y~f6hOj4PTI' );
define( 'NONCE_SALT',       'F1;ku]gX1_t@e@O=%R)40SH47*Wpe(94sIfSo>&Egp >`@6;m]6([ gHp!DK?}%%' );

/**#@-*/

/**
 * WordPress database table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 *
 * At the installation time, database tables are created with the specified prefix.
 * Changing this value after WordPress is installed will make your site think
 * it has not been installed.
 *
 * @link https://developer.wordpress.org/advanced-administration/wordpress/wp-config/#table-prefix
 */
$table_prefix = 'wp_';

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
 * @link https://developer.wordpress.org/advanced-administration/debug/debug-wordpress/
 */
define( 'WP_DEBUG', false );

/* Add any custom values between this line and the "stop editing" line. */
// Tắt tự động cập nhật ngầm để tránh làm thay đổi file core trong môi trường Git / Docker
define( 'AUTOMATIC_UPDATER_DISABLED', true );
define( 'WP_AUTO_UPDATE_CORE', false );

/* That's all, stop editing! Happy publishing. */

/** Absolute path to the WordPress directory. */
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/' );
}

/** Sets up WordPress vars and included files. */
require_once ABSPATH . 'wp-settings.php';
