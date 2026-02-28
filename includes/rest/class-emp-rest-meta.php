<?php
if ( ! defined('ABSPATH') ) { exit; }

class EMP_REST_Meta {

    public static function init() {
        add_action('init', [__CLASS__, 'register_meta']);
    }

    public static function register_meta() {
        register_post_meta('event', '_emp_event_date', [
            'type'              => 'string',
            'single'            => true,
            'show_in_rest'      => true,
            'sanitize_callback' => [__CLASS__, 'sanitize_date'],
            'auth_callback'     => function() { return current_user_can('read'); },
        ]);

        register_post_meta('event', '_emp_location', [
            'type'              => 'string',
            'single'            => true,
            'show_in_rest'      => true,
            'sanitize_callback' => 'sanitize_text_field',
            'auth_callback'     => function() { return current_user_can('read'); },
        ]);
    }

    public static function sanitize_date($value) {
        $value = sanitize_text_field($value);
        if ($value !== '' && ! preg_match('/^\d{4}-\d{2}-\d{2}$/', $value)) {
            return '';
        }
        return $value;
    }
}