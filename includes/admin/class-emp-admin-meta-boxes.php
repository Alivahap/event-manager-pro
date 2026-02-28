<?php
if ( ! defined('ABSPATH') ) { exit; }

class EMP_Admin_Meta_Boxes {

    const NONCE_ACTION = 'emp_save_event_meta';
    const NONCE_NAME   = 'emp_event_meta_nonce';

    public static function init() {
        add_action('add_meta_boxes', [__CLASS__, 'register_meta_boxes']);
        add_action('save_post_event', [__CLASS__, 'save_meta'], 10, 2);
    }

    public static function register_meta_boxes() {
        add_meta_box(
            'emp_event_details',
            __('Event Details', 'event-manager-pro'),
            [__CLASS__, 'render_meta_box'],
            'event',
            'side',
            'default'
        );
    }

    public static function render_meta_box($post) {
        wp_nonce_field(self::NONCE_ACTION, self::NONCE_NAME);

        $event_date = get_post_meta($post->ID, '_emp_event_date', true);
        $location   = get_post_meta($post->ID, '_emp_location', true);

        ?>
        <p>
            <label for="emp_event_date"><strong><?php echo esc_html__('Event Date', 'event-manager-pro'); ?></strong></label><br>
            <input type="date"
                   id="emp_event_date"
                   name="emp_event_date"
                   value="<?php echo esc_attr($event_date); ?>"
                   style="width:100%;">
        </p>

        <p>
            <label for="emp_location"><strong><?php echo esc_html__('Location', 'event-manager-pro'); ?></strong></label><br>
            <input type="text"
                   id="emp_location"
                   name="emp_location"
                   value="<?php echo esc_attr($location); ?>"
                   placeholder="<?php echo esc_attr__('e.g., Istanbul', 'event-manager-pro'); ?>"
                   style="width:100%;">
        </p>
        <?php
    }

    public static function save_meta($post_id, $post) {
        // Autosave / revisions
        if ( defined('DOING_AUTOSAVE') && DOING_AUTOSAVE ) return;
        if ( wp_is_post_revision($post_id) ) return;

        // Nonce check
        if ( ! isset($_POST[self::NONCE_NAME]) || ! wp_verify_nonce($_POST[self::NONCE_NAME], self::NONCE_ACTION) ) {
            return;
        }

        // Capability check
        if ( ! current_user_can('edit_post', $post_id) ) {
            return;
        }

        // Date sanitize: allow YYYY-MM-DD only
        $event_date = isset($_POST['emp_event_date']) ? sanitize_text_field(wp_unslash($_POST['emp_event_date'])) : '';
        if ( $event_date !== '' && ! preg_match('/^\d{4}-\d{2}-\d{2}$/', $event_date) ) {
            $event_date = '';
        }

        $location = isset($_POST['emp_location']) ? sanitize_text_field(wp_unslash($_POST['emp_location'])) : '';

        update_post_meta($post_id, '_emp_event_date', $event_date);
        update_post_meta($post_id, '_emp_location', $location);
    }
}