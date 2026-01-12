<?php

if (!defined('ABSPATH')) {
    exit;
}

class Habaq_WP_Core_CPTs {
    const REWRITE_OPTION = 'habaq_rewrite_flushed_version';
    /**
     * Register CPTs and taxonomies.
     *
     * @return void
     */
    public static function register() {
        if (!post_type_exists('project')) {
            register_post_type('project', array(
                'labels' => array(
                    'name' => __('المشاريع', 'habaq-wp-core'),
                    'singular_name' => __('مشروع', 'habaq-wp-core'),
                ),
                'public' => true,
                'has_archive' => true,
                'rewrite' => array('slug' => 'projects'),
                'menu_icon' => 'dashicons-portfolio',
                'supports' => array('title', 'editor', 'excerpt', 'thumbnail', 'revisions'),
                'show_in_rest' => true,
            ));
        }

        if (!post_type_exists('job')) {
            register_post_type('job', array(
                'labels' => array(
                    'name' => __('الفرص', 'habaq-wp-core'),
                    'singular_name' => __('فرصة', 'habaq-wp-core'),
                ),
                'public' => true,
                'has_archive' => true,
                'rewrite' => array('slug' => 'jobs'),
                'menu_icon' => 'dashicons-id',
                'supports' => array('title', 'editor', 'excerpt', 'thumbnail', 'revisions'),
                'show_in_rest' => true,
            ));
        }

        $taxonomies = array(
            'project_unit' => array('project', __('وحدة المشروع', 'habaq-wp-core'), 'project-unit', true),
            'project_status' => array('project', __('حالة المشروع', 'habaq-wp-core'), 'project-status', true),
            'project_type' => array('project', __('نوع المشروع', 'habaq-wp-core'), 'project-type', true),
            'job_unit' => array('job', __('الوحدة', 'habaq-wp-core'), 'job-unit', true),
            'job_type' => array('job', __('نوع الفرصة', 'habaq-wp-core'), 'job-type', true),
            'job_location' => array('job', __('الموقع', 'habaq-wp-core'), 'job-location', true),
            'job_level' => array('job', __('المستوى', 'habaq-wp-core'), 'job-level', true),
        );

        foreach ($taxonomies as $taxonomy => $config) {
            if (taxonomy_exists($taxonomy)) {
                continue;
            }

            list($post_type, $label, $slug, $hierarchical) = $config;

            register_taxonomy($taxonomy, array($post_type), array(
                'labels' => array(
                    'name' => $label,
                    'singular_name' => $label,
                ),
                'public' => true,
                'hierarchical' => (bool) $hierarchical,
                'rewrite' => array('slug' => $slug),
                'show_in_rest' => true,
            ));
        }
    }

    /**
     * Show admin notice if job CPT or rewrites missing.
     *
     * @return void
     */
    public static function admin_notice() {
        if (!current_user_can('manage_options')) {
            return;
        }

        if (!post_type_exists('job')) {
            self::render_notice();
            return;
        }

        $version = get_option(self::REWRITE_OPTION, '');
        $archive_link = get_post_type_archive_link('job');
        $needs_flush = ($version !== HABAQ_WP_CORE_VERSION) || !$archive_link || !self::has_job_rewrite_rule();

        if ($needs_flush) {
            self::render_notice();
        }
    }

    /**
     * Check if job rewrite rules exist.
     *
     * @return bool
     */
    private static function has_job_rewrite_rule() {
        $rules = get_option('rewrite_rules');
        if (!is_array($rules)) {
            return false;
        }

        foreach ($rules as $rule => $query) {
            if (strpos($query, 'post_type=job') !== false) {
                return true;
            }
        }

        return false;
    }

    /**
     * Render the rewrite notice.
     *
     * @return void
     */
    private static function render_notice() {
        echo '<div class="notice notice-warning"><p>' . esc_html__('قد تحتاج إلى إعادة حفظ إعدادات الروابط الدائمة: الإعدادات > الروابط الدائمة > حفظ التغييرات.', 'habaq-wp-core') . '</p></div>';
    }

    /**
     * Mark rewrite rules as flushed for the current version.
     *
     * @return void
     */
    public static function mark_rewrite_flushed() {
        update_option(self::REWRITE_OPTION, HABAQ_WP_CORE_VERSION);
    }
}
