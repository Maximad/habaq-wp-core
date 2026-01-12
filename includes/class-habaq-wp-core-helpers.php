<?php

if (!defined('ABSPATH')) {
    exit;
}

class Habaq_WP_Core_Helpers {
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

        $timestamp = strtotime($deadline . ' 23:59:59');
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
     * Get the job deadline string with fallback.
     *
     * @param int $post_id Job post ID.
     * @return string
     */
    public static function get_job_deadline($post_id) {
        $deadline = get_post_meta($post_id, 'habaq_deadline', true);
        if ($deadline === '') {
            $deadline = get_post_meta($post_id, 'job_deadline', true);
        }

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
}
