<?php
define( 'DB_NAME', 'wordpress_test' );
define( 'DB_USER', 'root' );
define( 'DB_PASSWORD', '' );
define( 'DB_HOST', 'localhost' );

define( 'WP_TESTS_DOMAIN', 'localhost' );
define( 'WP_TESTS_EMAIL', 'admin@localhost.test' );
define( 'WP_TESTS_TITLE', 'WP Tests' );

define( 'WP_DEBUG', true );

$table_prefix = 'wptests_';

if ( ! defined( 'ABSPATH' ) ) {
    define( 'ABSPATH', 'C:\tmp\wordpress-develop\src\\' );
}

require_once 'C:\tmp\wordpress-develop\tests\phpunit\includes\functions.php';
require_once 'C:\tmp\wordpress-develop\tests\phpunit\includes\bootstrap.php';