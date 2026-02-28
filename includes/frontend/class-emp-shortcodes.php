<?php
if ( ! defined('ABSPATH') ) { exit; }

class EMP_Shortcodes {

    public static function init() {
        add_shortcode('events', [__CLASS__, 'events_shortcode']);
    }

    public static function events_shortcode($atts) {
        $atts = shortcode_atts([
            'type'  => '',      // event_type slug
            'limit' => 10,
            'order' => 'ASC',
        ], $atts, 'events');

        $limit = absint($atts['limit']);
        if ($limit <= 0) $limit = 10;

        $order = strtoupper(sanitize_text_field($atts['order']));
        if ( ! in_array($order, ['ASC','DESC'], true) ) $order = 'ASC';

        $args = [
            'post_type'      => 'event',
            'post_status'    => 'publish',
            'posts_per_page' => $limit,
            'meta_key'       => '_emp_event_date',
            'orderby'        => 'meta_value',
            'order'          => $order,
            'no_found_rows'  => true,
        ];

        $type = sanitize_text_field($atts['type']);
        if ($type !== '') {
            $args['tax_query'] = [
                [
                    'taxonomy' => 'event_type',
                    'field'    => 'slug',
                    'terms'    => [$type],
                ]
            ];
        }

        $q = new WP_Query($args);

        ob_start();

        if ($q->have_posts()) {
            echo '<div class="emp-grid">';
            while ($q->have_posts()) {
                $q->the_post();
                $event_date = get_post_meta(get_the_ID(), '_emp_event_date', true);
                $location   = get_post_meta(get_the_ID(), '_emp_location', true);

                echo '<article class="emp-card">';
                echo '<h3 class="emp-card-title"><a href="' . esc_url(get_permalink()) . '">' . esc_html(get_the_title()) . '</a></h3>';
                echo '<div class="emp-meta">';
                if ($event_date) echo '<span class="emp-pill">' . esc_html($event_date) . '</span>';
                if ($location)   echo '<span class="emp-pill">' . esc_html($location) . '</span>';
                echo '</div>';
                echo '</article>';
            }
            echo '</div>';
            wp_reset_postdata();
        } else {
            echo '<p>' . esc_html__('No events found.', 'event-manager-pro') . '</p>';
        }

        return ob_get_clean();
    }
}