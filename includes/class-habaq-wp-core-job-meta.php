<?php

if (!defined('ABSPATH')) {
    exit;
}

class Habaq_WP_Core_Job_Meta {
    /**
     * Register job meta fields.
     *
     * @return void
     */
    public static function register_meta() {
        $fields = array(
            'habaq_deadline' => array(
                'type' => 'string',
                'sanitize_callback' => array(__CLASS__, 'sanitize_date'),
            ),
            'habaq_job_status' => array(
                'type' => 'string',
                'sanitize_callback' => array(__CLASS__, 'sanitize_status'),
            ),
            'habaq_start_date' => array(
                'type' => 'string',
                'sanitize_callback' => array(__CLASS__, 'sanitize_date'),
            ),
            'habaq_time_commitment' => array(
                'type' => 'string',
                'sanitize_callback' => 'sanitize_text_field',
            ),
            'habaq_compensation' => array(
                'type' => 'string',
                'sanitize_callback' => 'sanitize_text_field',
            ),
        );

        foreach ($fields as $key => $config) {
            register_post_meta('job', $key, array(
                'type' => $config['type'],
                'single' => true,
                'show_in_rest' => true,
                'sanitize_callback' => $config['sanitize_callback'],
                'auth_callback' => function () {
                    return current_user_can('edit_posts');
                },
            ));
        }
    }

    /**
     * Register job details metabox.
     *
     * @return void
     */
    public static function register_metabox() {
        global $wp_meta_boxes;

        if (isset($wp_meta_boxes['job']['side']['default']['habaq_job_details'])) {
            return;
        }

        add_meta_box(
            'habaq_job_details',
            __('تفاصيل إضافية للوظيفة', 'habaq-wp-core'),
            array(__CLASS__, 'render_metabox'),
            'job',
            'side',
            'default'
        );
    }

    /**
     * Render job details metabox.
     *
     * @param WP_Post $post Post object.
     * @return void
     */
    public static function render_metabox($post) {
        $deadline = get_post_meta($post->ID, 'habaq_deadline', true);
        $status = Habaq_WP_Core_Helpers::job_get_status($post->ID);
        $start = get_post_meta($post->ID, 'habaq_start_date', true);
        $commit = get_post_meta($post->ID, 'habaq_time_commitment', true);
        $comp = get_post_meta($post->ID, 'habaq_compensation', true);

        wp_nonce_field('habaq_job_meta_save', 'habaq_job_meta_nonce');

        echo '<p>';
        echo '<label for="habaq-deadline">' . esc_html__('آخر موعد للتقديم', 'habaq-wp-core') . '</label>';
        echo '<input type="date" id="habaq-deadline" name="habaq_deadline" value="' . esc_attr($deadline) . '" class="widefat" />';
        echo '</p>';

        echo '<p>';
        echo '<label for="habaq-status">' . esc_html__('الحالة', 'habaq-wp-core') . '</label>';
        echo '<select id="habaq-status" name="habaq_job_status" class="widefat">';
        echo '<option value="open"' . selected($status, 'open', false) . '>' . esc_html__('مفتوح', 'habaq-wp-core') . '</option>';
        echo '<option value="closed"' . selected($status, 'closed', false) . '>' . esc_html__('مغلق', 'habaq-wp-core') . '</option>';
        echo '</select>';
        echo '</p>';

        echo '<p>';
        echo '<label for="habaq-start">' . esc_html__('تاريخ البدء', 'habaq-wp-core') . '</label>';
        echo '<input type="date" id="habaq-start" name="habaq_start_date" value="' . esc_attr($start) . '" class="widefat" />';
        echo '</p>';

        echo '<p>';
        echo '<label for="habaq-commit">' . esc_html__('التفرغ', 'habaq-wp-core') . '</label>';
        echo '<input type="text" id="habaq-commit" name="habaq_time_commitment" value="' . esc_attr($commit) . '" class="widefat" />';
        echo '</p>';

        echo '<p>';
        echo '<label for="habaq-comp">' . esc_html__('التعويضات', 'habaq-wp-core') . '</label>';
        echo '<input type="text" id="habaq-comp" name="habaq_compensation" value="' . esc_attr($comp) . '" class="widefat" />';
        echo '</p>';
    }

    /**
     * Save job meta data.
     *
     * @param int $post_id Post ID.
     * @return void
     */
    public static function save_meta($post_id) {
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }

        if (wp_is_post_revision($post_id)) {
            return;
        }

        if (!isset($_POST['habaq_job_meta_nonce']) || !wp_verify_nonce(wp_unslash($_POST['habaq_job_meta_nonce']), 'habaq_job_meta_save')) {
            return;
        }

        if (!current_user_can('edit_post', $post_id)) {
            return;
        }

        $fields = array(
            'habaq_deadline' => array(__CLASS__, 'sanitize_date'),
            'habaq_job_status' => array(__CLASS__, 'sanitize_status'),
            'habaq_start_date' => array(__CLASS__, 'sanitize_date'),
            'habaq_time_commitment' => 'sanitize_text_field',
            'habaq_compensation' => 'sanitize_text_field',
        );

        foreach ($fields as $key => $sanitize) {
            $value = isset($_POST[$key]) ? wp_unslash($_POST[$key]) : '';
            $value = is_callable($sanitize) ? call_user_func($sanitize, $value) : sanitize_text_field($value);
            if ($value === '') {
                delete_post_meta($post_id, $key);
            } else {
                update_post_meta($post_id, $key, $value);
            }
        }
    }

    /**
     * Sanitize date in Y-m-d.
     *
     * @param string $value Date string.
     * @return string
     */
    public static function sanitize_date($value) {
        $value = sanitize_text_field($value);
        if ($value === '') {
            return '';
        }

        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $value)) {
            return '';
        }

        list($year, $month, $day) = array_map('intval', explode('-', $value));
        if (!checkdate($month, $day, $year)) {
            return '';
        }

        return $value;
    }

    /**
     * Sanitize job status.
     *
     * @param string $value Status.
     * @return string
     */
    public static function sanitize_status($value) {
        $value = sanitize_text_field($value);
        return in_array($value, array('open', 'closed'), true) ? $value : 'open';
    }
}
