<?php
if ( ! defined('ABSPATH') ) { exit; }

class EMP_Post_Type_Event {

    const POST_TYPE = 'event';

    public static function register() {
        $labels = [
            'name'                  => __('Events', 'event-manager-pro'),
            'singular_name'         => __('Event', 'event-manager-pro'),
            'add_new'               => __('Add New', 'event-manager-pro'),
            'add_new_item'          => __('Add New Event', 'event-manager-pro'),
            'edit_item'             => __('Edit Event', 'event-manager-pro'),
            'new_item'              => __('New Event', 'event-manager-pro'),
            'view_item'             => __('View Event', 'event-manager-pro'),
            'search_items'          => __('Search Events', 'event-manager-pro'),
            'not_found'             => __('No events found', 'event-manager-pro'),
            'not_found_in_trash'    => __('No events found in Trash', 'event-manager-pro'),
            'all_items'             => __('All Events', 'event-manager-pro'),
            'archives'              => __('Event Archives', 'event-manager-pro'),
            'menu_name'             => __('Events', 'event-manager-pro'),
        ];

        $capabilities = [
            'edit_post'              => 'edit_event',
            'read_post'              => 'read_event',
            'delete_post'            => 'delete_event',
            'edit_posts'             => 'edit_events',
            'edit_others_posts'      => 'edit_others_events',
            'publish_posts'          => 'publish_events',
            'read_private_posts'     => 'read_private_events',
            'delete_posts'           => 'delete_events',
            'delete_private_posts'   => 'delete_private_events',
            'delete_published_posts' => 'delete_published_events',
            'delete_others_posts'    => 'delete_others_events',
            'edit_private_posts'     => 'edit_private_events',
            'edit_published_posts'   => 'edit_published_events',
            'create_posts'           => 'edit_events',
        ];

       register_post_type(self::POST_TYPE, [
  'labels'       => $labels,
  'public'       => true,
  'has_archive'  => true,
  'show_in_rest' => true,
  'menu_position'=> 20,
  'menu_icon'    => 'dashicons-calendar-alt',
  'supports'     => ['title','editor','thumbnail','excerpt','author'],
  'rewrite'      => ['slug' => 'events'],
]);
    }
}