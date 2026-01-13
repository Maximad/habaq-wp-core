<?php

if (!defined('ABSPATH')) {
    exit;
}

class Habaq_WP_Core_Blocks {
    /**
     * Register plugin blocks.
     *
     * @return void
     */
    public static function register() {
        $block_dir = HABAQ_WP_CORE_DIR . '/blocks/job-dates';
        if (!is_dir($block_dir)) {
            return;
        }

        $script_handle = 'habaq-job-dates-editor';
        if (!wp_script_is($script_handle, 'registered')) {
            wp_register_script(
                $script_handle,
                HABAQ_WP_CORE_URL . 'blocks/job-dates/editor.js',
                array('wp-blocks', 'wp-element', 'wp-block-editor'),
                HABAQ_WP_CORE_VERSION,
                true
            );
        }

        register_block_type($block_dir, array(
            'editor_script' => $script_handle,
            'render_callback' => array(__CLASS__, 'render_job_dates'),
        ));
    }

    /**
     * Render job dates block.
     *
     * @param array    $attributes Block attributes.
     * @param string   $content Block content.
     * @param WP_Block $block Block instance.
     * @return string
     */
    public static function render_job_dates($attributes, $content, $block) {
        $post_id = 0;
        if (is_object($block) && isset($block->context['postId'])) {
            $post_id = (int) $block->context['postId'];
        }

        if (!$post_id || get_post_type($post_id) !== 'job') {
            return '';
        }

        $deadline = get_post_meta($post_id, 'habaq_deadline', true);
        $start = get_post_meta($post_id, 'habaq_start_date', true);

        if ($deadline === '' && $start === '') {
            return '';
        }

        $rows = array();
        if ($deadline) {
            $rows[] = array(__('آخر موعد للتقديم', 'habaq-wp-core'), Habaq_WP_Core_Helpers::job_format_date($deadline));
        }
        if ($start) {
            $rows[] = array(__('تاريخ البدء', 'habaq-wp-core'), Habaq_WP_Core_Helpers::job_format_date($start));
        }

        if (empty($rows)) {
            return '';
        }

        $output = '<div class="habaq-job-dates">';
        foreach ($rows as $row) {
            if (!$row[1]) {
                continue;
            }
            $output .= '<div class="habaq-job-dates__row">';
            $output .= '<span class="habaq-job-dates__label">' . esc_html($row[0]) . '</span>';
            $output .= '<span class="habaq-job-dates__value">' . esc_html($row[1]) . '</span>';
            $output .= '</div>';
        }
        $output .= '</div>';

        return $output;
    }
}
