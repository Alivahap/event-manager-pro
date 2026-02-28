<?php
if ( ! defined('ABSPATH') ) { exit; }

class EMP_Admin_Columns {

    public static function init() {
        add_filter('manage_event_posts_columns', [__CLASS__, 'add_columns']);
        add_action('manage_event_posts_custom_column', [__CLASS__, 'render_columns'], 10, 2);

        add_filter('manage_edit-event_sortable_columns', [__CLASS__, 'sortable_columns']);
        add_action('pre_get_posts', [__CLASS__, 'handle_sorting']);

        add_action('restrict_manage_posts', [__CLASS__, 'add_taxonomy_filter']);
        add_action('pre_get_posts', [__CLASS__, 'apply_taxonomy_filter']);
    }

    public static function add_columns($columns) {
        // Title sonrası kolon eklemek için ufak düzen
        $new = [];
        foreach ($columns as $key => $label) {
            $new[$key] = $label;
            if ($key === 'title') {
                $new['emp_event_date'] = __('Event Date', 'event-manager-pro');
                $new['emp_location']   = __('Location', 'event-manager-pro');
                $new['emp_event_type'] = __('Event Type', 'event-manager-pro');
            }
        }
        return $new;
    }

    public static function render_columns($column, $post_id) {
        if ($column === 'emp_event_date') {
            $date = get_post_meta($post_id, '_emp_event_date', true);
            echo $date ? esc_html($date) : '—';
            return;
        }

        if ($column === 'emp_location') {
            $loc = get_post_meta($post_id, '_emp_location', true);
            echo $loc ? esc_html($loc) : '—';
            return;
        }

        if ($column === 'emp_event_type') {
            $terms = get_the_terms($post_id, 'event_type');
            if (empty($terms) || is_wp_error($terms)) {
                echo '—';
                return;
            }
            $names = wp_list_pluck($terms, 'name');
            echo esc_html(implode(', ', $names));
            return;
        }
    }

    public static function sortable_columns($columns) {
        $columns['emp_event_date'] = 'emp_event_date';
        return $columns;
    }

    public static function handle_sorting($query) {
        if ( ! is_admin() || ! $query->is_main_query() ) return;

        $screen = function_exists('get_current_screen') ? get_current_screen() : null;
        if ( ! $screen || $screen->post_type !== 'event' ) return;

        if ( $query->get('orderby') === 'emp_event_date' ) {
            $query->set('meta_key', '_emp_event_date');
            $query->set('orderby', 'meta_value'); // YYYY-MM-DD olduğu için string sıralama yeterli
        }
    }

    public static function add_taxonomy_filter($post_type) {
        if ($post_type !== 'event') return;

        $tax = 'event_type';
        $taxonomy_obj = get_taxonomy($tax);
        if ( ! $taxonomy_obj ) return;

        $selected = isset($_GET[$tax]) ? sanitize_text_field(wp_unslash($_GET[$tax])) : '';
		/* translators: %s: Event title */
        wp_dropdown_categories([
            'show_option_all' => sprintf(__('All %s', 'event-manager-pro'), $taxonomy_obj->labels->name),
            'taxonomy'        => $tax,
            'name'            => $tax,
            'orderby'         => 'name',
            'selected'        => $selected,
            'hierarchical'    => true,
            'show_count'      => false,
            'hide_empty'      => false,
            'value_field'     => 'slug',
        ]);
    }

    public static function apply_taxonomy_filter($query) {
        if ( ! is_admin() || ! $query->is_main_query() ) return;

        $screen = function_exists('get_current_screen') ? get_current_screen() : null;
        if ( ! $screen || $screen->post_type !== 'event' ) return;

        $tax = 'event_type';
        if ( empty($_GET[$tax]) ) return;

        $slug = sanitize_text_field(wp_unslash($_GET[$tax]));
        if ($slug === '0' || $slug === '') return;

        $query->set('tax_query', [
            [
                'taxonomy' => $tax,
                'field'    => 'slug',
                'terms'    => [$slug],
            ]
        ]);
    }
}