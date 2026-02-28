<?php
if ( ! defined('ABSPATH') ) { exit; }

class EMP_Notifications {

    public static function init() {
        // New publish (draft -> publish)
        add_action('transition_post_status', [__CLASS__, 'on_status_transition'], 10, 3);

        // Updates (already published)
        add_action('post_updated', [__CLASS__, 'on_post_updated'], 10, 3);
    }

    public static function on_status_transition($new_status, $old_status, $post) {
        if ( $post->post_type !== 'event' ) return;
        if ( wp_is_post_revision($post->ID) ) return;

        // Only when it becomes published
        if ( $old_status !== 'publish' && $new_status === 'publish' ) {
            self::send_event_email('published', $post->ID);
        }
    }

    public static function on_post_updated($post_id, $post_after, $post_before) {
        if ( $post_after->post_type !== 'event' ) return;
        if ( wp_is_post_revision($post_id) ) return;

        // Only if it is published (avoid drafts)
        if ( get_post_status($post_id) !== 'publish' ) return;

        // Avoid duplicate: if transition already handled in same request
        // Also avoid firing if nothing changed
        $changed = (
            $post_after->post_title   !== $post_before->post_title ||
            $post_after->post_content !== $post_before->post_content
        );

  
        $date_after = get_post_meta($post_id, '_emp_event_date', true);
        $loc_after  = get_post_meta($post_id, '_emp_location', true);

 
        $last_sent = (int) get_post_meta($post_id, '_emp_last_update_email_ts', true);
        if ( $last_sent > 0 && (time() - $last_sent) < 30 ) {
            return;
        }

        
        if ( $changed || isset($_POST['emp_event_date']) || isset($_POST['emp_location']) ) {
            self::send_event_email('updated', $post_id);
            update_post_meta($post_id, '_emp_last_update_email_ts', time());
        }
    }

    private static function send_event_email($type, $post_id) {
        $title     = get_the_title($post_id);
        $permalink = get_permalink($post_id);
        $date      = get_post_meta($post_id, '_emp_event_date', true);
        $location  = get_post_meta($post_id, '_emp_location', true);

        $to = get_option('admin_email');

        $subject = ($type === 'published')
            ? sprintf(__('New Event Published: %s', 'event-manager-pro'), $title)
            : sprintf(__('Event Updated: %s', 'event-manager-pro'), $title);

        $lines = [];
        $lines[] = $subject;
        $lines[] = '';
        if ($date)     $lines[] = sprintf(__('Date: %s', 'event-manager-pro'), $date);
        if ($location) $lines[] = sprintf(__('Location: %s', 'event-manager-pro'), $location);
        $lines[] = '';
        $lines[] = sprintf(__('View: %s', 'event-manager-pro'), $permalink);

        $message = implode("\n", $lines);

        // Basic headers (plain text)
        $headers = ['Content-Type: text/plain; charset=UTF-8'];

        wp_mail($to, $subject, $message, $headers);
    }
}
add_action('wp_mail_failed', function($wp_error){
    error_log('EMP wp_mail_failed: ' . $wp_error->get_error_message());
});