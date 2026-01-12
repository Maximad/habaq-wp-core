<?php

if (!defined('ABSPATH')) {
    exit;
}

class Habaq_WP_Core_Settings {
    /**
     * Register settings page and hooks.
     *
     * @return void
     */
    public static function register() {
        add_action('admin_menu', array(__CLASS__, 'add_menu'));
        add_action('admin_init', array(__CLASS__, 'register_settings'));
        add_filter('habaq_apply_to_email', array(__CLASS__, 'filter_apply_to_email'));
    }

    /**
     * Add settings menu.
     *
     * @return void
     */
    public static function add_menu() {
        add_options_page(
            __('إعدادات هَبَق', 'habaq-wp-core'),
            __('إعدادات هَبَق', 'habaq-wp-core'),
            'manage_options',
            'habaq-settings',
            array(__CLASS__, 'render_page')
        );
    }

    /**
     * Register settings.
     *
     * @return void
     */
    public static function register_settings() {
        register_setting('habaq_settings', 'habaq_settings', array(__CLASS__, 'sanitize_settings'));

        add_settings_section(
            'habaq_settings_main',
            __('إعدادات التقديم', 'habaq-wp-core'),
            '__return_false',
            'habaq-settings'
        );

        add_settings_field(
            'habaq_apply_to_email',
            __('البريد الافتراضي لاستقبال الطلبات', 'habaq-wp-core'),
            array(__CLASS__, 'render_apply_to_email_field'),
            'habaq-settings',
            'habaq_settings_main'
        );

        add_settings_field(
            'habaq_unit_emails',
            __('تخصيص البريد حسب الوحدة', 'habaq-wp-core'),
            array(__CLASS__, 'render_unit_emails_field'),
            'habaq-settings',
            'habaq_settings_main'
        );

        add_settings_field(
            'habaq_stats',
            __('إحصاءات سريعة', 'habaq-wp-core'),
            array(__CLASS__, 'render_stats_field'),
            'habaq-settings',
            'habaq_settings_main'
        );
    }

    /**
     * Sanitize settings.
     *
     * @param array $input Input.
     * @return array
     */
    public static function sanitize_settings($input) {
        $output = array();

        $output['apply_to_email'] = isset($input['apply_to_email']) ? sanitize_email($input['apply_to_email']) : '';

        $unit_emails = array();
        if (isset($input['unit_emails']) && is_array($input['unit_emails'])) {
            foreach ($input['unit_emails'] as $term_id => $email) {
                $term_id = absint($term_id);
                if (!$term_id) {
                    continue;
                }
                $email = sanitize_email($email);
                if ($email) {
                    $unit_emails[$term_id] = $email;
                }
            }
        }
        $output['unit_emails'] = $unit_emails;

        return $output;
    }

    /**
     * Render settings page.
     *
     * @return void
     */
    public static function render_page() {
        echo '<div class="wrap">';
        echo '<h1>' . esc_html__('إعدادات هَبَق', 'habaq-wp-core') . '</h1>';
        echo '<form method="post" action="options.php">';
        settings_fields('habaq_settings');
        do_settings_sections('habaq-settings');
        submit_button();
        echo '</form>';
        echo '</div>';
    }

    /**
     * Render apply-to email field.
     *
     * @return void
     */
    public static function render_apply_to_email_field() {
        $settings = get_option('habaq_settings', array());
        $value = isset($settings['apply_to_email']) ? $settings['apply_to_email'] : '';
        echo '<input type="email" name="habaq_settings[apply_to_email]" value="' . esc_attr($value) . '" class="regular-text" />';
        echo '<p class="description">' . esc_html__('يتم استخدامه في حال عدم وجود تخصيص حسب الوحدة.', 'habaq-wp-core') . '</p>';
    }

    /**
     * Render unit email mapping fields.
     *
     * @return void
     */
    public static function render_unit_emails_field() {
        $settings = get_option('habaq_settings', array());
        $mapping = isset($settings['unit_emails']) && is_array($settings['unit_emails']) ? $settings['unit_emails'] : array();

        if (!taxonomy_exists('job_unit')) {
            echo '<p>' . esc_html__('لا توجد وحدات مسجلة بعد.', 'habaq-wp-core') . '</p>';
            return;
        }

        $terms = get_terms(array(
            'taxonomy' => 'job_unit',
            'hide_empty' => false,
        ));

        if (is_wp_error($terms) || empty($terms)) {
            echo '<p>' . esc_html__('لا توجد وحدات مسجلة بعد.', 'habaq-wp-core') . '</p>';
            return;
        }

        echo '<table class="widefat striped">';
        echo '<thead><tr><th>' . esc_html__('الوحدة', 'habaq-wp-core') . '</th><th>' . esc_html__('البريد', 'habaq-wp-core') . '</th></tr></thead>';
        echo '<tbody>';
        foreach ($terms as $term) {
            $value = isset($mapping[$term->term_id]) ? $mapping[$term->term_id] : '';
            echo '<tr>';
            echo '<td>' . esc_html($term->name) . '</td>';
            echo '<td><input type="email" name="habaq_settings[unit_emails][' . esc_attr($term->term_id) . ']" value="' . esc_attr($value) . '" class="regular-text" /></td>';
            echo '</tr>';
        }
        echo '</tbody>';
        echo '</table>';
    }

    /**
     * Render stats field.
     *
     * @return void
     */
    public static function render_stats_field() {
        $stats = self::get_stats();
        echo '<ul>';
        echo '<li>' . esc_html__('الفرص النشطة: ', 'habaq-wp-core') . esc_html($stats['active_jobs']) . '</li>';
        echo '<li>' . esc_html__('الفرص المنتهية/المغلقة: ', 'habaq-wp-core') . esc_html($stats['inactive_jobs']) . '</li>';
        echo '<li>' . esc_html__('إجمالي الطلبات: ', 'habaq-wp-core') . esc_html($stats['total_applications']) . '</li>';
        echo '<li>' . esc_html__('طلبات آخر 7 أيام: ', 'habaq-wp-core') . esc_html($stats['applications_7']) . '</li>';
        echo '<li>' . esc_html__('طلبات آخر 30 يومًا: ', 'habaq-wp-core') . esc_html($stats['applications_30']) . '</li>';
        echo '<li>' . esc_html__('أكثر الوحدات نشاطًا: ', 'habaq-wp-core') . esc_html($stats['top_units']) . '</li>';
        echo '</ul>';
    }

    /**
     * Filter apply-to email.
     *
     * @param string $email Default.
     * @return string
     */
    public static function filter_apply_to_email($email) {
        $settings = get_option('habaq_settings', array());
        $setting_email = isset($settings['apply_to_email']) ? sanitize_email($settings['apply_to_email']) : '';
        return $setting_email ? $setting_email : $email;
    }

    /**
     * Build stats data.
     *
     * @return array
     */
    private static function get_stats() {
        $active_query = new WP_Query(array(
            'post_type' => 'job',
            'post_status' => 'publish',
            'posts_per_page' => 1,
            'fields' => 'ids',
            'meta_query' => Habaq_WP_Core_Job_Filters::build_active_job_meta_query(),
        ));
        $active_jobs = (int) $active_query->found_posts;

        $total_jobs = (int) wp_count_posts('job')->publish;
        $inactive_jobs = max(0, $total_jobs - $active_jobs);

        $total_applications = (int) wp_count_posts('job_application')->private;
        $applications_7 = self::count_applications_since(7);
        $applications_30 = self::count_applications_since(30);

        $top_units = self::get_top_units();

        return array(
            'active_jobs' => $active_jobs,
            'inactive_jobs' => $inactive_jobs,
            'total_applications' => $total_applications,
            'applications_7' => $applications_7,
            'applications_30' => $applications_30,
            'top_units' => $top_units,
        );
    }

    /**
     * Count applications since days.
     *
     * @param int $days Days.
     * @return int
     */
    private static function count_applications_since($days) {
        $query = new WP_Query(array(
            'post_type' => 'job_application',
            'post_status' => 'private',
            'posts_per_page' => 1,
            'fields' => 'ids',
            'date_query' => array(
                array(
                    'after' => sprintf('-%d days', $days),
                    'inclusive' => true,
                ),
            ),
        ));

        return (int) $query->found_posts;
    }

    /**
     * Get top units by active job counts.
     *
     * @return string
     */
    private static function get_top_units() {
        if (!taxonomy_exists('job_unit')) {
            return __('غير متوفر', 'habaq-wp-core');
        }

        global $wpdb;
        $today = current_time('Y-m-d');

        $sql = "
            SELECT t.name, COUNT(DISTINCT p.ID) as term_count
            FROM {$wpdb->posts} p
            INNER JOIN {$wpdb->term_relationships} tr ON p.ID = tr.object_id
            INNER JOIN {$wpdb->term_taxonomy} tt ON tr.term_taxonomy_id = tt.term_taxonomy_id
            INNER JOIN {$wpdb->terms} t ON tt.term_id = t.term_id
            LEFT JOIN {$wpdb->postmeta} statusmeta ON (p.ID = statusmeta.post_id AND statusmeta.meta_key = 'habaq_job_status')
            LEFT JOIN {$wpdb->postmeta} deadlinemeta ON (p.ID = deadlinemeta.post_id AND deadlinemeta.meta_key = 'habaq_deadline')
            WHERE p.post_type = 'job'
              AND p.post_status = 'publish'
              AND tt.taxonomy = 'job_unit'
              AND (statusmeta.meta_value IS NULL OR statusmeta.meta_value != 'closed')
              AND (
                    deadlinemeta.meta_value IS NULL
                    OR deadlinemeta.meta_value = ''
                    OR deadlinemeta.meta_value >= %s
                  )
            GROUP BY t.term_id
            ORDER BY term_count DESC
            LIMIT 3
        ";

        $rows = $wpdb->get_results($wpdb->prepare($sql, $today));
        if (empty($rows)) {
            return __('غير متوفر', 'habaq-wp-core');
        }

        $labels = array();
        foreach ($rows as $row) {
            $labels[] = sprintf('%s (%d)', $row->name, $row->term_count);
        }

        return implode('، ', $labels);
    }
}
