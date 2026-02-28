<?php
/**
 * Plugin Name: Event Manager Pro
 * Description: Custom Event post type, taxonomies, templates, RSVP, REST, etc.
 * Version: 0.1.0
 * Author: Ali Vahap Aydın
 * Text Domain: event-manager-pro
 * Domain Path: /languages
 */

if ( ! defined('ABSPATH') ) {
    exit;
}

define('EMP_VERSION', '0.1.0');
define('EMP_PATH', plugin_dir_path(__FILE__));
define('EMP_URL', plugin_dir_url(__FILE__));

add_action('plugins_loaded', function () {
    load_plugin_textdomain('event-manager-pro', false, dirname(plugin_basename(__FILE__)) . '/languages');
});

require_once EMP_PATH . 'includes/post-types/class-emp-post-type-event.php';
require_once EMP_PATH . 'includes/taxonomies/class-emp-taxonomy-event-type.php';

add_action('init', function () {
    EMP_Post_Type_Event::register();
    EMP_Taxonomy_Event_Type::register();
});
require_once EMP_PATH . 'includes/admin/class-emp-admin-meta-boxes.php';

add_action('plugins_loaded', function () {
    EMP_Admin_Meta_Boxes::init();
});

require_once EMP_PATH . 'includes/admin/class-emp-admin-columns.php';

add_action('plugins_loaded', function () {
    EMP_Admin_Columns::init();
});

require_once EMP_PATH . 'includes/frontend/class-emp-template-loader.php';

add_action('plugins_loaded', function () {
    EMP_Template_Loader::init();
});

require_once EMP_PATH . 'includes/frontend/class-emp-shortcodes.php';

add_action('plugins_loaded', function () {
    EMP_Shortcodes::init();
});
require_once EMP_PATH . 'includes/class-emp-cache.php';
require_once EMP_PATH . 'includes/class-emp-cache-invalidator.php';

add_action('plugins_loaded', function () {
    EMP_Cache_Invalidator::init();
});

require_once EMP_PATH . 'includes/notifications/class-emp-notifications.php';

add_action('plugins_loaded', function () {
    EMP_Notifications::init();
});
add_filter('wp_mail', function($args){
    if ( defined('WP_DEBUG') && WP_DEBUG ) {
        $log  = "\n==== EMP wp_mail DEBUG ====\n";
        $log .= "To: " . print_r($args['to'], true) . "\n";
        $log .= "Subject: " . $args['subject'] . "\n";
        $log .= "Headers: " . print_r($args['headers'], true) . "\n";
        $log .= "Message:\n" . $args['message'] . "\n";
        $log .= "===========================\n";
        error_log($log);
    }
    return $args;
});

require_once EMP_PATH . 'includes/rsvp/class-emp-rsvp.php';

add_action('plugins_loaded', function () {
    EMP_RSVP::init();
});

require_once EMP_PATH . 'includes/rest/class-emp-rest-meta.php';

add_action('plugins_loaded', function () {
    EMP_REST_Meta::init();
});

require_once EMP_PATH . 'includes/rest/class-emp-rest-rsvp.php';

add_action('plugins_loaded', function () {
    EMP_REST_RSVP::init();
});

