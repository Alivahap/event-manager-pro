<?php
if ( ! defined('ABSPATH') ) { exit; }

class EMP_RSVP {

    public static function init() {
        add_action('wp_ajax_emp_rsvp', [__CLASS__, 'handle']);
        add_action('wp_ajax_nopriv_emp_rsvp', [__CLASS__, 'handle']);
    }

    public static function handle() {
        // Basic required fields
        $event_id = isset($_POST['event_id']) ? absint($_POST['event_id']) : 0;
        $nonce    = isset($_POST['nonce']) ? sanitize_text_field(wp_unslash($_POST['nonce'])) : '';
        $name     = isset($_POST['name']) ? sanitize_text_field(wp_unslash($_POST['name'])) : '';
        $email    = isset($_POST['email']) ? sanitize_email(wp_unslash($_POST['email'])) : '';

        if ( ! $event_id || get_post_type($event_id) !== 'event' ) {
            wp_send_json_error(['message' => __('Invalid event.', 'event-manager-pro')], 400);
        }

        if ( ! wp_verify_nonce($nonce, 'emp_rsvp') ) {
            wp_send_json_error(['message' => __('Security check failed.', 'event-manager-pro')], 403);
        }

        if ( $name === '' ) {
            wp_send_json_error(['message' => __('Name is required.', 'event-manager-pro')], 422);
        }

        if ( ! is_email($email) ) {
            wp_send_json_error(['message' => __('Valid email is required.', 'event-manager-pro')], 422);
        }

        // Store RSVP in post meta (fast MVP). Keyed by email to prevent duplicates.
        $key = '_emp_rsvps';
        $rsvps = get_post_meta($event_id, $key, true);
        if ( ! is_array($rsvps) ) $rsvps = [];

        $email_lc = strtolower($email);
        if ( isset($rsvps[$email_lc]) ) {
            wp_send_json_error(['message' => __('You are already registered for this event.', 'event-manager-pro')], 409);
        }

        $rsvps[$email_lc] = [
            'name'  => $name,
            'email' => $email,
            'ts'    => time(),
        ];

        update_post_meta($event_id, $key, $rsvps);

        wp_send_json_success(['message' => __('Registration received. Thank you!', 'event-manager-pro')]);
    }
}