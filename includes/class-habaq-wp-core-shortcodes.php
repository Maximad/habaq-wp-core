<?php

if (!defined('ABSPATH')) {
    exit;
}

class Habaq_WP_Core_Shortcodes {
    /**
     * Register shortcodes.
     *
     * @return void
     */
    public static function register() {
        if (!shortcode_exists('habaq_job_fields')) {
            add_shortcode('habaq_job_fields', array(__CLASS__, 'render_job_fields'));
        }

        if (!shortcode_exists('job_apply')) {
            add_shortcode('job_apply', array(__CLASS__, 'render_job_apply'));
        }

        if (!shortcode_exists('habaq_deadline')) {
            add_shortcode('habaq_deadline', array(__CLASS__, 'render_deadline'));
        }

        if (!shortcode_exists('habaq_job_meta')) {
            add_shortcode('habaq_job_meta', array(__CLASS__, 'render_job_meta'));
        }

        if (!shortcode_exists('habaq_job_terms')) {
            add_shortcode('habaq_job_terms', array(__CLASS__, 'render_job_terms'));
        }
    }

    /**
     * Render job fields on single job posts.
     *
     * @return string
     */
    public static function render_job_fields() {
        if (!is_singular('job')) {
            return '';
        }

        $post_id = get_the_ID();

        $deadline = get_post_meta($post_id, 'habaq_deadline', true);
        $start = get_post_meta($post_id, 'habaq_start_date', true);
        $commit = get_post_meta($post_id, 'habaq_time_commitment', true);
        $comp = get_post_meta($post_id, 'habaq_compensation', true);
        $status = Habaq_WP_Core_Helpers::job_get_status($post_id);

        $rows = array();

        if ($deadline) {
            $rows[] = array('آخر موعد للتقديم', Habaq_WP_Core_Helpers::job_format_date($deadline));
        }
        if ($start) {
            $rows[] = array('تاريخ البدء', Habaq_WP_Core_Helpers::job_format_date($start));
        }
        if ($commit) {
            $rows[] = array('التفرغ', $commit);
        }
        if ($comp) {
            $rows[] = array('التعويضات', $comp);
        }

        $label_status = ($status === 'closed') ? 'مغلق' : 'مفتوح';
        if (Habaq_WP_Core_Helpers::job_is_expired($post_id)) {
            $label_status = 'منتهي';
        }

        $rows[] = array('الحالة', $label_status);

        self::enqueue_styles();

        $output = '<div class="habaq-job-fields">';
        foreach ($rows as $row) {
            if (!$row[1]) {
                continue;
            }
            $output .= '<div class="habaq-job-fields__row"><span class="k">' . esc_html($row[0]) . '</span><span class="v">' . esc_html($row[1]) . '</span></div>';
        }
        $output .= '</div>';

        return $output;
    }

    /**
     * Render apply buttons on single job posts.
     *
     * @param array $atts Shortcode attributes.
     * @return string
     */
    public static function render_job_apply($atts) {
        if (!is_singular('job')) {
            return '';
        }

        $post_id = get_the_ID();
        $status = Habaq_WP_Core_Helpers::job_get_status($post_id);
        $expired = Habaq_WP_Core_Helpers::job_is_expired($post_id);

        self::enqueue_styles();

        if ($status === 'closed' || $expired) {
            return '<div class="habaq-job-apply__ended">' . esc_html__('انتهى التقديم لهذه الفرصة.', 'habaq-wp-core') . '</div>';
        }

        $attrs = shortcode_atts(array(
            'email' => '',
            'form_url' => '/apply',
            'email_label' => __('التقديم عبر البريد', 'habaq-wp-core'),
            'form_label' => __('التقديم عبر النموذج', 'habaq-wp-core'),
        ), $atts);

        $title = get_the_title();
        $slug = get_post_field('post_name', get_post());
        $subject = rawurlencode(sprintf(__('طلب تقديم: %s', 'habaq-wp-core'), $title));
        $body = rawurlencode(
            __('الاسم الكامل:', 'habaq-wp-core') . "\n" .
            __('البريد الإلكتروني:', 'habaq-wp-core') . "\n" .
            __('الرابط/الملف الشخصي:', 'habaq-wp-core') . "\n" .
            __('السيرة الذاتية:', 'habaq-wp-core') . "\n" .
            __('ملاحظات:', 'habaq-wp-core') . "\n"
        );

        $mailto = $attrs['email'] ? "mailto:{$attrs['email']}?subject={$subject}&body={$body}" : '';
        $form_link = esc_url(trailingslashit(home_url($attrs['form_url'])) . '?job=' . $slug);

        $output = '<div class="habaq-job-apply">';
        if ($mailto) {
            $output .= '<a class="habaq-job-apply__button" href="' . esc_url($mailto) . '">' . esc_html($attrs['email_label']) . '</a>';
        }
        $output .= '<a class="habaq-job-apply__button" href="' . $form_link . '">' . esc_html($attrs['form_label']) . '</a>';
        $output .= '</div>';

        return $output;
    }

    /**
     * Render deadline shortcode.
     *
     * @return string
     */
    public static function render_deadline($atts) {
        $post_id = self::resolve_job_post_id($atts);
        if (!$post_id) {
            return '';
        }

        $deadline = get_post_meta($post_id, 'habaq_deadline', true);
        if (!$deadline) {
            return '';
        }

        return esc_html(Habaq_WP_Core_Helpers::job_format_date($deadline));
    }

    /**
     * Render a single job meta field.
     *
     * @param array $atts Shortcode attributes.
     * @return string
     */
    public static function render_job_meta($atts) {
        $post_id = self::resolve_job_post_id($atts);
        if (!$post_id) {
            return '';
        }

        $attrs = shortcode_atts(array(
            'key' => '',
            'post_id' => 0,
        ), $atts);

        $allowed = array(
            'habaq_deadline',
            'habaq_start_date',
            'habaq_time_commitment',
            'habaq_compensation',
            'habaq_job_status',
        );

        $key = sanitize_key($attrs['key']);
        if (!in_array($key, $allowed, true)) {
            return '';
        }

        $value = get_post_meta($post_id, $key, true);
        if ($value === '') {
            return '';
        }

        if (in_array($key, array('habaq_deadline', 'habaq_start_date'), true)) {
            $value = Habaq_WP_Core_Helpers::job_format_date($value);
        }

        if ($key === 'habaq_job_status') {
            $value = ($value === 'closed') ? __('مغلق', 'habaq-wp-core') : __('مفتوح', 'habaq-wp-core');
        }

        return esc_html($value);
    }

    /**
     * Render job taxonomy terms.
     *
     * @param array $atts Shortcode attributes.
     * @return string
     */
    public static function render_job_terms($atts) {
        $post_id = self::resolve_job_post_id($atts);
        if (!$post_id) {
            return '';
        }

        $attrs = shortcode_atts(array(
            'taxonomy' => '',
            'separator' => '، ',
            'post_id' => 0,
        ), $atts);

        $taxonomy = sanitize_key($attrs['taxonomy']);
        if (!taxonomy_exists($taxonomy)) {
            return '';
        }

        $terms = get_the_terms($post_id, $taxonomy);
        if (is_wp_error($terms) || empty($terms)) {
            return '';
        }

        $names = wp_list_pluck($terms, 'name');
        return esc_html(implode($attrs['separator'], $names));
    }

    /**
     * Resolve job post ID for shortcodes.
     *
     * @param array $atts Shortcode attributes.
     * @return int
     */
    private static function resolve_job_post_id($atts) {
        $post_id = 0;

        if (is_array($atts) && isset($atts['post_id'])) {
            $post_id = absint($atts['post_id']);
        }

        if (!$post_id) {
            $post_id = get_the_ID();
        }

        if (!$post_id) {
            $post_id = get_queried_object_id();
        }

        if (!$post_id || get_post_type($post_id) !== 'job') {
            return 0;
        }

        return $post_id;
    }

    /**
     * Enqueue frontend styles.
     *
     * @return void
     */
    private static function enqueue_styles() {
        $css = '.habaq-job-fields{border:1px solid rgba(0,0,0,.08);border-radius:14px;padding:14px;margin:16px 0;display:grid;gap:8px;box-shadow:0 2px 10px rgba(0,0,0,.04)}
.habaq-job-fields__row{display:flex;justify-content:space-between;gap:12px;flex-wrap:wrap}
.habaq-job-fields .k{opacity:.7}
.habaq-job-fields .v{font-weight:600}
.habaq-job-apply{display:flex;gap:12px;flex-wrap:wrap;margin-top:20px}
.habaq-job-apply__button{border:1px solid rgba(0,0,0,.2);padding:10px 16px;border-radius:10px;text-decoration:none;display:inline-flex;align-items:center;color:inherit}
.habaq-job-apply__button:focus{outline:2px solid currentColor;outline-offset:2px}
.habaq-job-apply__ended{padding:14px;border:1px solid rgba(0,0,0,.12);border-radius:12px;margin-top:16px}';

        Habaq_WP_Core_Helpers::enqueue_inline_style($css);
    }
}
