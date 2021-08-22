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
define( 'DB_NAME', 'cerl' );

/** MySQL database username */
define( 'DB_USER', 'root' );

/** MySQL database password */
define( 'DB_PASSWORD', '' );

/** MySQL hostname */
define( 'DB_HOST', 'localhost' );

/** Database Charset to use in creating database tables. */
define( 'DB_CHARSET', 'utf8mb4' );

/** The Database Collate type. Don't change this if in doubt. */
define( 'DB_COLLATE', '' );

/**#@+
 * Authentication Unique Keys and Salts.
 *
 * Change these to different unique phrases!
 * You can generate these using the {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service}
 * You can change these at any point in time to invalidate all existing cookies. This will force all users to have to log in again.
 *
 * @since 2.6.0
 */
define( 'AUTH_KEY',         'H_4O/P8eX[K({.uTHJ%)ec2fLZyBT5fYj,6_Pp8QujW]|t!i][te](Kq9-yvNwh0' );
define( 'SECURE_AUTH_KEY',  '+6Ak!_VQ9.7Pkq0 _%34xo}Z6kCN{i|n6a3&Hp2zKJFfiUJTJGRM>!&nNh_`Eb<(' );
define( 'LOGGED_IN_KEY',    'V7TC0l50eWa`.`R[pY+FjPzX~]-JgO<n&471fZta^TOel fk{q~7C~K,zBe|vS&t' );
define( 'NONCE_KEY',        'x#?M#_19@F{u_dX<pbObLjKSt+/nM1t^U_gm^=05}M77Xi>Ta@{-%9b<t|Zt)S4m' );
define( 'AUTH_SALT',        '&5?D4n- Hvko|[:cjp6$JD4/atiVl]L;Z:An5jr`IgU-OE_7/(p%zL;|i@y=xGOC' );
define( 'SECURE_AUTH_SALT', 'mS:t|rh;A}NJvmzwR_oJQZv?,;aMI~3d(Jgt6fdF1o|0:K]<6jyB@BcVlg<vrXLw' );
define( 'LOGGED_IN_SALT',   '%jT Lo(4b3~(`{Am1/(v=^* GvJnwb6p7_{R(F%/SKmX_!BXB3.0Bj E+nWg>Dth' );
define( 'NONCE_SALT',       'a.bDh0PQpqu{/Oh;llx:EP_k*5/,$N|BfwB j7%Tg/1ed(qIzzDtbr42c*{s/:+D' );

/**#@-*/

/**
 * WordPress Database Table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
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
 * visit the Codex.
 *
 * @link https://codex.wordpress.org/Debugging_in_WordPress
 */
define( 'WP_DEBUG', false );

/* That's all, stop editing! Happy publishing. */

/** Absolute path to the WordPress directory. */
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', dirname( __FILE__ ) . '/' );
}

/** Sets up WordPress vars and included files. */
require_once( ABSPATH . 'wp-settings.php' );
