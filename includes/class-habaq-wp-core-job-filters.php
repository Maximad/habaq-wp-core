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

        if (!self::is_job_archive_context($query)) {
            return;
        }

        $tax_query = self::build_tax_query();
        if (!empty($tax_query)) {
            $query->set('tax_query', self::merge_tax_query($query->get('tax_query'), $tax_query));
        }

        $keyword = self::get_keyword();
        if ($keyword !== '') {
            $query->set('s', $keyword);
            $query->set('post_type', 'job');
        }

        $meta_query = self::build_active_job_meta_query();
        $query->set('meta_query', self::merge_meta_query($query->get('meta_query'), $meta_query));
    }

    /**
     * Filter Query Loop blocks to hide expired/closed jobs.
     *
     * @param array $query_vars Query vars.
     * @return array
     */
    public static function filter_query_loop($query_vars) {
        if (is_admin()) {
            return $query_vars;
        }

        $post_type = isset($query_vars['post_type']) ? $query_vars['post_type'] : 'post';
        $is_job = false;

        if (is_array($post_type)) {
            $is_job = in_array('job', $post_type, true);
        } else {
            $is_job = ($post_type === 'job');
        }

        if (!$is_job) {
            return $query_vars;
        }

        $meta_query = self::build_active_job_meta_query();
        $query_vars['meta_query'] = self::merge_meta_query(isset($query_vars['meta_query']) ? $query_vars['meta_query'] : array(), $meta_query);

        return $query_vars;
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

        self::enqueue_styles();

        $keyword = self::get_keyword();
        $output = '<form class="habaq-job-filters" method="get" action="' . esc_url($action) . '">';
        $output .= '<div class="habaq-job-filters__header">' . esc_html__('تصفية الفرص', 'habaq-wp-core') . '</div>';
        $output .= '<div class="habaq-job-filters__section">';
        $output .= '<label for="habaq-job-q">' . esc_html__('كلمات البحث', 'habaq-wp-core') . '</label>';
        $output .= '<input type="search" id="habaq-job-q" name="job_q" value="' . esc_attr($keyword) . '" placeholder="' . esc_attr__('ابحث عن فرصة', 'habaq-wp-core') . '" />';
        $output .= '</div>';

        foreach (self::get_taxonomies() as $taxonomy) {
            $output .= self::render_taxonomy_filter($taxonomy);
        }

        $output .= '<div class="habaq-job-filters__actions">';
        $output .= '<button type="submit">' . esc_html__('تطبيق التصفية', 'habaq-wp-core') . '</button>';
        $output .= '</div>';
        $output .= '</form>';

        return $output;
    }

    /**
     * Render a taxonomy accordion filter.
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
        $output = '<details class="habaq-job-filters__accordion">';
        $output .= '<summary>' . esc_html($label) . '</summary>';
        $output .= '<div class="habaq-job-filters__options">';

        foreach ($terms as $term) {
            $checked = in_array($term->slug, $selected, true);
            $output .= '<label class="habaq-job-filters__option">';
            $output .= '<input type="checkbox" name="' . esc_attr($taxonomy) . '[]" value="' . esc_attr($term->slug) . '"' . checked($checked, true, false) . ' />';
            $output .= '<span>' . esc_html($term->name) . '</span>';
            $output .= '<em>' . esc_html((string) $term->count) . '</em>';
            $output .= '</label>';
        }

        $output .= '</div>';
        $output .= '</details>';

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

    /**
     * Check if the main query is a job archive or taxonomy.
     *
     * @param WP_Query $query Query instance.
     * @return bool
     */
    private static function is_job_archive_context($query) {
        if ($query->is_post_type_archive('job')) {
            return true;
        }

        if ($query->is_tax()) {
            $object = get_queried_object();
            if ($object && !empty($object->taxonomy)) {
                return in_array($object->taxonomy, self::get_taxonomies(), true);
            }
        }

        return false;
    }

    /**
     * Build tax query from selected terms.
     *
     * @return array
     */
    private static function build_tax_query() {
        $tax_query = array('relation' => 'AND');

        foreach (self::get_taxonomies() as $taxonomy) {
            $terms = self::get_selected_terms($taxonomy);
            if (!empty($terms)) {
                $tax_query[] = array(
                    'taxonomy' => $taxonomy,
                    'field' => 'slug',
                    'terms' => $terms,
                );
            }
        }

        return count($tax_query) > 1 ? $tax_query : array();
    }

    /**
     * Build meta query to hide expired or closed jobs.
     *
     * @return array
     */
    public static function build_active_job_meta_query() {
        $today = current_time('Y-m-d');

        return array(
            'relation' => 'AND',
            array(
                'relation' => 'OR',
                array(
                    'key' => 'habaq_job_status',
                    'value' => 'closed',
                    'compare' => '!=',
                ),
                array(
                    'key' => 'habaq_job_status',
                    'compare' => 'NOT EXISTS',
                ),
            ),
            array(
                'relation' => 'OR',
                array(
                    'key' => 'habaq_deadline',
                    'compare' => 'NOT EXISTS',
                ),
                array(
                    'key' => 'habaq_deadline',
                    'value' => '',
                    'compare' => '=',
                ),
                array(
                    'key' => 'habaq_deadline',
                    'value' => $today,
                    'compare' => '>=',
                    'type' => 'DATE',
                ),
            ),
        );
    }

    /**
     * Merge tax query with existing.
     *
     * @param mixed $existing Existing query.
     * @param array $incoming Incoming query.
     * @return array
     */
    private static function merge_tax_query($existing, $incoming) {
        if (empty($existing)) {
            return $incoming;
        }

        if (!isset($existing['relation'])) {
            $existing = array_merge(array('relation' => 'AND'), array($existing));
        }

        return array_merge($existing, $incoming);
    }

    /**
     * Merge meta query with existing.
     *
     * @param mixed $existing Existing query.
     * @param array $incoming Incoming query.
     * @return array
     */
    private static function merge_meta_query($existing, $incoming) {
        if (empty($existing)) {
            return $incoming;
        }

        if (!isset($existing['relation'])) {
            $existing = array_merge(array('relation' => 'AND'), array($existing));
        }

        return array_merge($existing, $incoming);
    }

    /**
     * Get job taxonomies.
     *
     * @return string[]
     */
    private static function get_taxonomies() {
        return array('job_unit', 'job_type', 'job_location', 'job_level');
    }

    /**
     * Enqueue frontend styles.
     *
     * @return void
     */
    private static function enqueue_styles() {
        $css = '.habaq-job-filters{border:1px solid rgba(0,0,0,.08);border-radius:16px;padding:16px;display:grid;gap:16px;background:#fff;box-shadow:0 2px 10px rgba(0,0,0,.04)}
.habaq-job-filters__header{font-weight:700;font-size:1.05rem}
.habaq-job-filters__section label{display:block;font-size:.9rem;margin-bottom:6px;color:#444}
.habaq-job-filters__section input[type="search"]{width:100%;padding:10px 12px;border:1px solid #d5d5d5;border-radius:10px}
.habaq-job-filters__accordion{border:1px solid rgba(0,0,0,.06);border-radius:12px;padding:10px;background:#fafafa}
.habaq-job-filters__accordion summary{font-weight:600;cursor:pointer;list-style:none}
.habaq-job-filters__options{display:grid;gap:8px;margin-top:12px}
.habaq-job-filters__option{display:flex;align-items:center;justify-content:space-between;gap:8px;background:#fff;padding:8px 10px;border-radius:10px;border:1px solid rgba(0,0,0,.05)}
.habaq-job-filters__option input{margin-inline-end:8px}
.habaq-job-filters__option span{flex:1}
.habaq-job-filters__option em{font-style:normal;color:#777;font-size:.85rem}
.habaq-job-filters__actions{display:flex;justify-content:flex-start}
.habaq-job-filters__actions button{background:#111;color:#fff;border:0;border-radius:10px;padding:10px 16px;cursor:pointer}
@media (max-width:720px){.habaq-job-filters{padding:12px}}';

        Habaq_WP_Core_Helpers::enqueue_inline_style($css);
    }
}
