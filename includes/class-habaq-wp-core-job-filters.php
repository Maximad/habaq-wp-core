<?php

if (!defined('ABSPATH')) {
    exit;
}

class Habaq_WP_Core_Job_Filters {
    /**
     * Register shortcode.
     *
     * @return void
     */
    public static function register_shortcodes() {
        if (!shortcode_exists('habaq_job_filters')) {
            add_shortcode('habaq_job_filters', array(__CLASS__, 'render_filters'));
        }
    }

    /**
     * Filter the job archive query.
     *
     * @param WP_Query $query Query instance.
     * @return void
     */
    public static function filter_job_archive($query) {
        if (is_admin() || !$query->is_main_query()) {
            return;
        }

        if (!$query->is_post_type_archive('job')) {
            return;
        }

        $taxonomies = array('job_unit', 'job_type', 'job_location', 'job_level');
        $tax_query = array('relation' => 'AND');

        foreach ($taxonomies as $taxonomy) {
            $terms = self::get_selected_terms($taxonomy);
            if (!empty($terms)) {
                $tax_query[] = array(
                    'taxonomy' => $taxonomy,
                    'field' => 'slug',
                    'terms' => $terms,
                );
            }
        }

        if (count($tax_query) > 1) {
            $query->set('tax_query', $tax_query);
        }

        $keyword = self::get_keyword();
        if ($keyword !== '') {
            $query->set('s', $keyword);
            $query->set('post_type', 'job');
        }
    }

    /**
     * Render the filters UI.
     *
     * @return string
     */
    public static function render_filters() {
        $action = get_post_type_archive_link('job');
        if (!$action) {
            $action = home_url('/jobs/');
        }

        $keyword = self::get_keyword();
        $output = '<form class="habaq-job-filters" method="get" action="' . esc_url($action) . '">';
        $output .= '<details class="habaq-job-filters__panel" open>';
        $output .= '<summary>' . esc_html__('تصفية الفرص', 'habaq-wp-core') . '</summary>';
        $output .= '<div class="habaq-job-filters__section">';
        $output .= '<label for="habaq-job-q">' . esc_html__('كلمات البحث', 'habaq-wp-core') . '</label>';
        $output .= '<input type="search" id="habaq-job-q" name="job_q" value="' . esc_attr($keyword) . '" />';
        $output .= '</div>';

        foreach (array('job_unit', 'job_type', 'job_location', 'job_level') as $taxonomy) {
            $output .= self::render_taxonomy_filter($taxonomy);
        }

        $output .= '<div class="habaq-job-filters__actions">';
        $output .= '<button type="submit">' . esc_html__('تطبيق التصفية', 'habaq-wp-core') . '</button>';
        $output .= '</div>';
        $output .= '</details>';
        $output .= '</form>';

        return $output;
    }

    /**
     * Render a taxonomy multi-select field.
     *
     * @param string $taxonomy Taxonomy slug.
     * @return string
     */
    private static function render_taxonomy_filter($taxonomy) {
        if (!taxonomy_exists($taxonomy)) {
            return '';
        }

        $taxonomy_object = get_taxonomy($taxonomy);
        $label = $taxonomy_object ? $taxonomy_object->labels->name : $taxonomy;
        $terms = get_terms(array(
            'taxonomy' => $taxonomy,
            'hide_empty' => false,
        ));

        if (is_wp_error($terms) || empty($terms)) {
            return '';
        }

        $selected = self::get_selected_terms($taxonomy);
        $output = '<div class="habaq-job-filters__section">';
        $output .= '<label for="habaq-' . esc_attr($taxonomy) . '">' . esc_html($label) . '</label>';
        $output .= '<select id="habaq-' . esc_attr($taxonomy) . '" name="' . esc_attr($taxonomy) . '[]" multiple>';

        foreach ($terms as $term) {
            $output .= '<option value="' . esc_attr($term->slug) . '"';
            if (in_array($term->slug, $selected, true)) {
                $output .= ' selected';
            }
            $output .= '>' . esc_html($term->name) . '</option>';
        }

        $output .= '</select>';
        $output .= '</div>';

        return $output;
    }

    /**
     * Get selected terms from the query string.
     *
     * @param string $taxonomy Taxonomy slug.
     * @return string[]
     */
    private static function get_selected_terms($taxonomy) {
        if (!isset($_GET[$taxonomy])) {
            return array();
        }

        $raw = wp_unslash($_GET[$taxonomy]);
        $terms = is_array($raw) ? $raw : array($raw);
        $terms = array_map('sanitize_text_field', $terms);

        return array_values(array_filter($terms));
    }

    /**
     * Get the keyword query string.
     *
     * @return string
     */
    private static function get_keyword() {
        if (!isset($_GET['job_q'])) {
            return '';
        }

        return sanitize_text_field(wp_unslash($_GET['job_q']));
    }
}
