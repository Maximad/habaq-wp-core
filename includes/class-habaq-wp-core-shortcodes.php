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

        $output = '<div class="habaq-job-fields">';
        foreach ($rows as $row) {
            if (!$row[1]) {
                continue;
            }
            $output .= '<div class="habaq-job-fields__row"><span class="k">' . esc_html($row[0]) . '</span><span class="v">' . esc_html($row[1]) . '</span></div>';
        }
        $output .= '</div>';

        $output .= '<style>
            .habaq-job-fields{border:1px solid rgba(0,0,0,.08);border-radius:14px;padding:12px;margin:14px 0;display:grid;gap:8px}
            .habaq-job-fields__row{display:flex;justify-content:space-between;gap:12px;flex-wrap:wrap}
            .habaq-job-fields .k{opacity:.7;font-size:13px}
            .habaq-job-fields .v{font-weight:600}
        </style>';

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

        if ($status === 'closed' || $expired) {
            return '<div style="padding:12px;border:1px solid rgba(0,0,0,.12);border-radius:12px;margin-top:16px;">انتهى التقديم لهذه الفرصة.</div>';
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

        $output = '<div style="display:flex;gap:12px;flex-wrap:wrap;margin-top:20px;">';
        if ($mailto) {
            $output .= '<a class="wp-block-button__link wp-element-button" href="' . esc_url($mailto) . '">' . esc_html($attrs['email_label']) . '</a>';
        }
        $output .= '<a class="wp-block-button__link wp-element-button" href="' . $form_link . '">' . esc_html($attrs['form_label']) . '</a>';
        $output .= '</div>';

        return $output;
    }
}
