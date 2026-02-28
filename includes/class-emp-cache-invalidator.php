<?php
if ( ! defined('ABSPATH') ) { exit; }

class EMP_Cache_Invalidator {

    public static function init() {
        add_action('save_post_event', [__CLASS__, 'flush_on_change'], 20, 2);
        add_action('deleted_post', [__CLASS__, 'flush_on_delete'], 20, 1);
        add_action('update_option_WPLANG', [__CLASS__, 'flush_on_option_change'], 20, 2);
        // taxonomy changes
        add_action('set_object_terms', [__CLASS__, 'flush_on_terms'], 20, 6);
    }
    public static function flush_on_option_change($old_value, $value) {
        EMP_Cache::flush_all();
    }
    public static function flush_on_change($post_id, $post) {
        if ( defined('DOING_AUTOSAVE') && DOING_AUTOSAVE ) return;
        if ( wp_is_post_revision($post_id) ) return;

        EMP_Cache::flush_all();
    }

    public static function flush_on_delete($post_id) {
        if ( get_post_type($post_id) === 'event' ) {
            EMP_Cache::flush_all();
        }
    }

    public static function flush_on_terms($object_id, $terms, $tt_ids, $taxonomy, $append, $old_tt_ids) {
        if ( $taxonomy !== 'event_type' ) return;
        if ( get_post_type($object_id) !== 'event' ) return;

        EMP_Cache::flush_all();
    }
}