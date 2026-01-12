<?php

if (!defined('ABSPATH')) {
    exit;
}

class Habaq_WP_Core_Access {
    /**
     * Enforce access zones by page slug and descendants.
     *
     * @return void
     */
    public static function enforce_zones() {
        if (is_admin() || wp_doing_ajax() || wp_doing_cron()) {
            return;
        }

        if (!is_singular('page')) {
            return;
        }

        $post_id = get_queried_object_id();
        if (!$post_id) {
            return;
        }

        $zones = array(
            'insider-hub' => 'habaq_insider_access',
            'core-ops' => 'habaq_core_access',
            'restricted' => 'habaq_restricted_access',
            'executive' => 'habaq_executive_access',
        );

        $ancestors = get_post_ancestors($post_id);
        $ids = array_merge(array($post_id), $ancestors);

        foreach ($zones as $slug => $capability) {
            $zone = get_page_by_path($slug);
            if (!$zone) {
                continue;
            }

            if (in_array((int) $zone->ID, array_map('intval', $ids), true)) {
                if (!is_user_logged_in()) {
                    $login = get_page_by_path('login');
                    $login_url = $login ? get_permalink($login->ID) : wp_login_url();
                    $request_uri = isset($_SERVER['REQUEST_URI']) ? wp_unslash($_SERVER['REQUEST_URI']) : '';
                    $request_uri = sanitize_text_field($request_uri);
                    $target = esc_url_raw(home_url($request_uri));
                    $redirect_url = add_query_arg('redirect_to', rawurlencode($target), $login_url);
                    wp_safe_redirect($redirect_url);
                    exit;
                }

                if (!current_user_can($capability)) {
                    global $wp_query;
                    $wp_query->set_404();
                    status_header(404);
                    nocache_headers();
                    include get_404_template();
                    exit;
                }

                return;
            }
        }
    }
}
