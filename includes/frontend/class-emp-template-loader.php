<?php
if ( ! defined('ABSPATH') ) { exit; }

class EMP_Template_Loader {

    public static function init() {
        add_filter('template_include', [__CLASS__, 'template_include']);
        add_action('wp_enqueue_scripts', [__CLASS__, 'enqueue_assets']);
    }

    public static function enqueue_assets() {
    if ( is_singular('event') || is_post_type_archive('event') ) {
        wp_enqueue_style(
            'emp-frontend',
            EMP_URL . 'assets/css/frontend.css',
            [],
            EMP_VERSION
        );
    }

    if ( is_post_type_archive('event') ) {
        wp_enqueue_script(
            'emp-filters',
            EMP_URL . 'assets/js/filters.js',
            [],
            EMP_VERSION,
            true
        );
    }
	if ( is_singular('event') ) {
    wp_enqueue_script(
        'emp-rsvp',
        EMP_URL . 'assets/js/rsvp.js',
        [],
        EMP_VERSION,
        true
    );

    wp_localize_script('emp-rsvp', 'empAjax', [
        'ajaxUrl' => admin_url('admin-ajax.php'),
    ]);
}
}

    public static function template_include($template) {
        if ( is_singular('event') ) {
            $candidate = self::locate_theme_template('single-event.php');
            if ( $candidate ) return $candidate;

            $plugin_template = EMP_PATH . 'templates/single-event.php';
            if ( file_exists($plugin_template) ) return $plugin_template;
        }

        if ( is_post_type_archive('event') ) {
            $candidate = self::locate_theme_template('archive-event.php');
            if ( $candidate ) return $candidate;

            $plugin_template = EMP_PATH . 'templates/archive-event.php';
            if ( file_exists($plugin_template) ) return $plugin_template;
        }

        return $template;
    }

    /**
     * Theme override support:
     * - yourtheme/event-manager-pro/single-event.php
     * - yourtheme/event-manager-pro/archive-event.php
     */
    private static function locate_theme_template($filename) {
        $paths = [
            'event-manager-pro/' . $filename,
        ];

        foreach ($paths as $path) {
            $found = locate_template($path);
            if ( $found ) return $found;
        }

        return '';
    }
}