<?php

if (!defined('ABSPATH')) {
    exit;
}

class Habaq_WP_Core_Job_Admin {
    /**
     * Register admin metaboxes.
     *
     * @return void
     */
    public static function register_metaboxes() {
        global $wp_meta_boxes;

        if (!isset($wp_meta_boxes['job_application']['normal']['default']['habaq_job_application_details'])) {
            add_meta_box(
                'habaq_job_application_details',
                'تفاصيل طلب التقديم',
                array(__CLASS__, 'render_details_metabox'),
                'job_application',
                'normal',
                'default'
            );
        }

        if (!isset($wp_meta_boxes['job_application']['side']['default']['habaq_job_application_links'])) {
            add_meta_box(
                'habaq_job_application_links',
                'روابط سريعة',
                array(__CLASS__, 'render_links_metabox'),
                'job_application',
                'side',
                'default'
            );
        }
    }

    /**
     * Add application list table columns.
     *
     * @param array $columns Columns.
     * @return array
     */
    public static function add_columns($columns) {
        $new_columns = array();
        foreach ($columns as $key => $label) {
            $new_columns[$key] = $label;
            if ($key === 'title') {
                $new_columns['job_title'] = __('الفرصة', 'habaq-wp-core');
                $new_columns['job_deadline'] = __('آخر موعد', 'habaq-wp-core');
                $new_columns['applicant_email'] = __('البريد', 'habaq-wp-core');
                $new_columns['cv_link'] = __('CV', 'habaq-wp-core');
            }
        }

        if (!isset($new_columns['date']) && isset($columns['date'])) {
            $new_columns['date'] = $columns['date'];
        }

        return $new_columns;
    }

    /**
     * Render application list table columns.
     *
     * @param string $column Column name.
     * @param int    $post_id Post ID.
     * @return void
     */
    public static function render_columns($column, $post_id) {
        if ($column === 'job_title') {
            $title = get_post_meta($post_id, 'job_title', true);
            $job_url = get_post_meta($post_id, 'job_url', true);
            if ($job_url) {
                echo '<a href="' . esc_url($job_url) . '">' . esc_html($title ? $title : __('غير متوفر', 'habaq-wp-core')) . '</a>';
            } else {
                echo esc_html($title ? $title : __('غير متوفر', 'habaq-wp-core'));
            }
            return;
        }

        if ($column === 'job_deadline') {
            $deadline = get_post_meta($post_id, 'habaq_deadline', true);
            if ($deadline === '') {
                $job_id = (int) get_post_meta($post_id, 'job_id', true);
                if ($job_id) {
                    $deadline = get_post_meta($job_id, 'habaq_deadline', true);
                }
            }
            $display = Habaq_WP_Core_Helpers::job_format_date($deadline);
            echo esc_html($display ? $display : __('غير متوفر', 'habaq-wp-core'));
            return;
        }

        if ($column === 'applicant_email') {
            $email = get_post_meta($post_id, 'email', true);
            echo esc_html($email ? $email : __('غير متوفر', 'habaq-wp-core'));
            return;
        }

        if ($column === 'cv_link') {
            $cv_url = get_post_meta($post_id, 'cv_url', true);
            if ($cv_url) {
                echo '<a href="' . esc_url($cv_url) . '" target="_blank" rel="noopener noreferrer">' . esc_html__('عرض', 'habaq-wp-core') . '</a>';
            } else {
                echo esc_html__('غير متوفر', 'habaq-wp-core');
            }
        }
    }

    /**
     * Render the details metabox.
     *
     * @param WP_Post $post Post object.
     * @return void
     */
    public static function render_details_metabox($post) {
        $fields = array(
            'full_name' => __('الاسم الكامل', 'habaq-wp-core'),
            'email' => __('البريد الإلكتروني', 'habaq-wp-core'),
            'phone' => __('رقم الهاتف', 'habaq-wp-core'),
            'city' => __('المدينة', 'habaq-wp-core'),
            'availability' => __('التفرغ المتوقع', 'habaq-wp-core'),
            'portfolio' => __('الرابط/الملف الشخصي', 'habaq-wp-core'),
            'motivation' => __('الدافع', 'habaq-wp-core'),
            'consent' => __('الموافقة', 'habaq-wp-core'),
            'job_unit' => __('الوحدة', 'habaq-wp-core'),
            'job_type' => __('نوع الفرصة', 'habaq-wp-core'),
            'job_location' => __('الموقع', 'habaq-wp-core'),
            'job_level' => __('المستوى', 'habaq-wp-core'),
        );

        $deadline = get_post_meta($post->ID, 'habaq_deadline', true);
        if ($deadline === '') {
            $job_id = (int) get_post_meta($post->ID, 'job_id', true);
            if ($job_id) {
                $deadline = get_post_meta($job_id, 'habaq_deadline', true);
            }
        }

        $deadline_display = Habaq_WP_Core_Helpers::job_format_date($deadline);

        echo '<dl class="habaq-job-application__details">';
        echo '<dt>' . esc_html__('آخر موعد للتقديم', 'habaq-wp-core') . '</dt>';
        echo '<dd>' . esc_html($deadline_display ? $deadline_display : __('غير متوفر', 'habaq-wp-core')) . '</dd>';

        foreach ($fields as $key => $label) {
            $value = get_post_meta($post->ID, $key, true);
            if ($key === 'consent') {
                $value = $value ? __('نعم', 'habaq-wp-core') : __('لا', 'habaq-wp-core');
            }

            if ($value === '') {
                $value = __('غير متوفر', 'habaq-wp-core');
            }

            echo '<dt>' . esc_html($label) . '</dt>';
            echo '<dd>' . esc_html($value) . '</dd>';
        }
        echo '</dl>';
    }

    /**
     * Render the quick links metabox.
     *
     * @param WP_Post $post Post object.
     * @return void
     */
    public static function render_links_metabox($post) {
        $job_url = get_post_meta($post->ID, 'job_url', true);
        $email = get_post_meta($post->ID, 'email', true);
        $cv_url = get_post_meta($post->ID, 'cv_url', true);
        $deadline = get_post_meta($post->ID, 'habaq_deadline', true);
        if ($deadline === '') {
            $job_id = (int) get_post_meta($post->ID, 'job_id', true);
            if ($job_id) {
                $deadline = get_post_meta($job_id, 'habaq_deadline', true);
            }
        }
        $deadline_display = Habaq_WP_Core_Helpers::job_format_date($deadline);

        echo '<ul class="habaq-job-application__links">';
        if ($job_url) {
            echo '<li><a href="' . esc_url($job_url) . '" target="_blank" rel="noopener noreferrer">' . esc_html__('صفحة الفرصة', 'habaq-wp-core') . '</a></li>';
        }
        if ($email) {
            echo '<li><a href="mailto:' . esc_attr($email) . '">' . esc_html__('مراسلة المتقدم', 'habaq-wp-core') . '</a></li>';
        }
        if ($cv_url) {
            echo '<li><a href="' . esc_url($cv_url) . '" target="_blank" rel="noopener noreferrer">' . esc_html__('فتح السيرة الذاتية', 'habaq-wp-core') . '</a></li>';
        }
        if ($deadline_display) {
            echo '<li><span class="habaq-job-application__badge">' . esc_html__('آخر موعد: ', 'habaq-wp-core') . esc_html($deadline_display) . '</span></li>';
        }
        echo '</ul>';
    }
}
