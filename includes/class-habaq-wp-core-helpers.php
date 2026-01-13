<?php

if (!defined('ABSPATH')) {
    exit;
}

class Habaq_WP_Core_Helpers {
    /**
     * Inline styles added.
     *
     * @var array
     */
    private static $inline_styles = array();

    /**
     * Get job status (open|closed).
     *
     * @param int $post_id Job post ID.
     * @return string
     */
    public static function job_get_status($post_id) {
        $status = get_post_meta($post_id, 'habaq_job_status', true);
        return in_array($status, array('open', 'closed'), true) ? $status : 'open';
    }

    /**
     * Check if a job is expired based on deadline.
     *
     * @param int $post_id Job post ID.
     * @return bool
     */
    public static function job_is_expired($post_id) {
        $deadline = get_post_meta($post_id, 'habaq_deadline', true);
        if (!$deadline) {
            return false;
        }

        $timestamp = self::deadline_to_timestamp($deadline);
        if (!$timestamp) {
            return false;
        }

        return current_time('timestamp') > $timestamp;
    }

    /**
     * Format a Y-m-d date string.
     *
     * @param string $ymd Date string.
     * @return string
     */
    public static function job_format_date($ymd) {
        if (!$ymd) {
            return '';
        }

        $timestamp = strtotime($ymd . ' 00:00:00');
        if (!$timestamp) {
            return '';
        }

        return date_i18n('j F Y', $timestamp);
    }

    /**
     * Get the job deadline string.
     *
     * @param int $post_id Job post ID.
     * @return string
     */
    public static function get_job_deadline($post_id) {
        $deadline = get_post_meta($post_id, 'habaq_deadline', true);
        return (string) $deadline;
    }

    /**
     * Determine if a job is closed or expired.
     *
     * @param int $post_id Job post ID.
     * @return bool
     */
    public static function job_is_closed($post_id) {
        $status = self::job_get_status($post_id);
        if ($status === 'closed') {
            return true;
        }

        return self::job_is_expired($post_id);
    }

    /**
     * Convert a deadline to end-of-day timestamp in site timezone.
     *
     * @param string $deadline Y-m-d deadline.
     * @return int
     */
    public static function deadline_to_timestamp($deadline) {
        if (!$deadline) {
            return 0;
        }

        $timezone = wp_timezone();
        try {
            $date = new DateTimeImmutable($deadline . ' 23:59:59', $timezone);
        } catch (Exception $e) {
            return 0;
        }

        return $date->getTimestamp();
    }

    /**
     * Register inline styles once per page.
     *
     * @param string $css Styles.
     * @return void
     */
    public static function enqueue_inline_style($css) {
        if (trim($css) === '') {
            return;
        }

        $hash = md5($css);
        if (isset(self::$inline_styles[$hash])) {
            return;
        }

        self::$inline_styles[$hash] = true;

        if (!wp_style_is('habaq-wp-core-inline', 'registered')) {
            wp_register_style('habaq-wp-core-inline', false, array(), HABAQ_WP_CORE_VERSION);
        }

        wp_enqueue_style('habaq-wp-core-inline');
        wp_add_inline_style('habaq-wp-core-inline', $css);
    }
}
