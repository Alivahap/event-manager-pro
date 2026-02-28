<?php
if ( ! defined('ABSPATH') ) { exit; }

class EMP_Taxonomy_Event_Type {

    const TAXONOMY = 'event_type';

    public static function register() {
        $labels = [
            'name'              => __('Event Types', 'event-manager-pro'),
            'singular_name'     => __('Event Type', 'event-manager-pro'),
            'search_items'      => __('Search Event Types', 'event-manager-pro'),
            'all_items'         => __('All Event Types', 'event-manager-pro'),
            'edit_item'         => __('Edit Event Type', 'event-manager-pro'),
            'update_item'       => __('Update Event Type', 'event-manager-pro'),
            'add_new_item'      => __('Add New Event Type', 'event-manager-pro'),
            'new_item_name'     => __('New Event Type Name', 'event-manager-pro'),
            'menu_name'         => __('Event Types', 'event-manager-pro'),
        ];

        register_taxonomy(self::TAXONOMY, ['event'], [
            'labels'            => $labels,
            'public'            => true,
            'show_in_rest'      => true,
            'show_admin_column' => true,
            'hierarchical'      => true,
            'rewrite'           => ['slug' => 'event-type'],
        ]);
    }
}