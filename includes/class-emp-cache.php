<?php
if ( ! defined('ABSPATH') ) { exit; }

class EMP_Cache {

    const GROUP = 'emp_events';

    /**
     * Deterministic cache key for args/params.
     */
    public static function key($prefix, array $data = []) {
        // Normalize
        ksort($data);
        $hash = md5(wp_json_encode($data));
        return self::GROUP . '_' . sanitize_key($prefix) . '_' . $hash;
    }

    /**
     * Remember keys to invalidate later.
     */
    public static function remember_key($key) {
        $keys = get_option(self::GROUP . '_keys', []);
        if (!is_array($keys)) $keys = [];
        if (!in_array($key, $keys, true)) {
            $keys[] = $key;
            // keep option small
            if (count($keys) > 300) {
                $keys = array_slice($keys, -300);
            }
            update_option(self::GROUP . '_keys', $keys, false);
        }
    }

    public static function get($key) {
        return get_transient($key);
    }

    public static function set($key, $value, $ttl = 300) {
        set_transient($key, $value, $ttl);
        self::remember_key($key);
    }

    /**
     * Flush all EMP cache keys we know.
     */
    public static function flush_all() {
        $keys = get_option(self::GROUP . '_keys', []);
        if (is_array($keys)) {
            foreach ($keys as $k) {
                delete_transient($k);
            }
        }
        delete_option(self::GROUP . '_keys');
    }
}