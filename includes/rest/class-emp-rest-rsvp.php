<?php
if ( ! defined('ABSPATH') ) { exit; }

class EMP_REST_RSVP {

    const NS = 'event-manager-pro/v1';

    public static function init() {
        add_action('rest_api_init', [__CLASS__, 'routes']);
    }

    public static function routes() {
        register_rest_route(self::NS, '/events/(?P<id>\d+)/register', [
            'methods'             => 'POST',
            'callback'            => [__CLASS__, 'register_attendee'],
            'permission_callback' => '__return_true', // Public endpoint, protected by nonce if used from WP
            'args'                => [
                'name'  => ['type' => 'string', 'required' => true],
                'email' => ['type' => 'string', 'required' => true],
            ],
        ]);

        register_rest_route(self::NS, '/events/(?P<id>\d+)/registrations/count', [
            'methods'             => 'GET',
            'callback'            => [__CLASS__, 'count'],
            'permission_callback' => function () {
                return current_user_can('edit_posts');
            },
        ]);
    }

    public static function register_attendee($request) {
        $event_id = absint($request['id']);
        if ( ! $event_id || get_post_type($event_id) !== 'event' ) {
            return new WP_Error('emp_invalid_event', __('Invalid event.', 'event-manager-pro'), ['status' => 400]);
        }

        // Optional nonce verification (recommended for same-site calls)
        $nonce = $request->get_header('X-WP-Nonce');
        if ( $nonce && ! wp_verify_nonce($nonce, 'wp_rest') ) {
            return new WP_Error('emp_bad_nonce', __('Security check failed.', 'event-manager-pro'), ['status' => 403]);
        }

        $name  = sanitize_text_field($request->get_param('name'));
        $email = sanitize_email($request->get_param('email'));

        if ( $name === '' ) {
            return new WP_Error('emp_name_required', __('Name is required.', 'event-manager-pro'), ['status' => 422]);
        }

        if ( ! is_email($email) ) {
            return new WP_Error('emp_email_required', __('Valid email is required.', 'event-manager-pro'), ['status' => 422]);
        }

        $key = '_emp_rsvps';
        $rsvps = get_post_meta($event_id, $key, true);
        if ( ! is_array($rsvps) ) $rsvps = [];

        $email_lc = strtolower($email);
        if ( isset($rsvps[$email_lc]) ) {
            return new WP_Error('emp_duplicate', __('You are already registered for this event.', 'event-manager-pro'), ['status' => 409]);
        }

        $rsvps[$email_lc] = [
            'name'  => $name,
            'email' => $email,
            'ts'    => time(),
        ];

        update_post_meta($event_id, $key, $rsvps);

        return rest_ensure_response([
            'success' => true,
            'message' => __('Registration received. Thank you!', 'event-manager-pro'),
        ]);
    }

    public static function count($request) {
        $event_id = absint($request['id']);
        if ( ! $event_id || get_post_type($event_id) !== 'event' ) {
            return new WP_Error('emp_invalid_event', __('Invalid event.', 'event-manager-pro'), ['status' => 400]);
        }

        $rsvps = get_post_meta($event_id, '_emp_rsvps', true);
        $count = is_array($rsvps) ? count($rsvps) : 0;

        return rest_ensure_response([
            'event_id' => $event_id,
            'count'    => $count,
        ]);
    }
}