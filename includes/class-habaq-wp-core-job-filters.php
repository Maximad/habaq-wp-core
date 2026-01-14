<?php

if (!defined('ABSPATH')) {
    exit;
}

class Habaq_WP_Core_Job_Filters {
    /**
     * Check if debug logging is enabled.
     *
     * @return bool
     */
    private static function debug_enabled() {
        return defined('WP_DEBUG') && WP_DEBUG && defined('HABAQ_DEBUG_FILTERS') && HABAQ_DEBUG_FILTERS;
    }

    /**
     * Log debug data to error_log when enabled.
     *
     * @param string $message Message prefix.
     * @param array  $context Context data.
     * @return void
     */
    private static function log_debug($message, $context = array()) {
        if (!self::debug_enabled()) {
            return;
        }

        $entry = $message;
        if (!empty($context)) {
            $entry .= ' ' . wp_json_encode($context);
        }

        error_log($entry);
    }

    /**
     * Get the current URL for debug logging.
     *
     * @return string
     */
    private static function get_current_url() {
        $request_uri = isset($_SERVER['REQUEST_URI']) ? wp_unslash($_SERVER['REQUEST_URI']) : '';
        if ($request_uri === '') {
            return home_url('/');
        }

        return esc_url_raw(home_url($request_uri));
    }

    /**
     * Normalize term value to a canonical slug.
     *
     * @param mixed $value Raw value.
     * @return string
     */
    private static function normalize_term_value($value) {
        if (!is_string($value)) {
            $value = (string) $value;
        }

        $decoded = $value;
        for ($i = 0; $i < 3; $i++) {
            $next = rawurldecode($decoded);
            if ($next === $decoded) {
                break;
            }
            $decoded = $next;
        }

        return sanitize_title($decoded);
    }

    /**
     * Normalize term inputs to slugs.
     *
     * @param mixed $raw Raw term input.
     * @return string[]
     */
    private static function parse_term_inputs($raw) {
        $values = is_array($raw) ? $raw : array($raw);
        $slugs = array();

        foreach ($values as $value) {
            $parts = is_string($value) ? explode(',', $value) : array($value);
            foreach ($parts as $part) {
                $slug = self::normalize_term_value($part);
                if ($slug !== '') {
                    $slugs[] = $slug;
                }
            }
        }

        return array_values(array_unique(array_filter($slugs)));
    }
    /**
     * Filter for job requests with non-slug values and redirect.
     *
     * @return void
     */
    public static function maybe_redirect_bad_filters() {
        if (is_admin() || !is_post_type_archive('job')) {
            return;
        }

        if (empty($_GET)) {
            return;
        }

        $taxonomies = self::get_taxonomies();
        $mapped = array();

        foreach ($taxonomies as $taxonomy) {
            if (!isset($_GET[$taxonomy])) {
                continue;
            }

            $raw = wp_unslash($_GET[$taxonomy]);
            $values = is_array($raw) ? $raw : array($raw);

            if (empty($values)) {
                continue;
            }

            $name_map = self::get_term_name_to_slug_map($taxonomy);
            $updated = array();
            $changed = false;
            foreach ($values as $value) {
                $parts = is_string($value) ? explode(',', $value) : array($value);
                foreach ($parts as $part) {
                    $sanitized = sanitize_text_field($part);
                    if ($sanitized === '') {
                        continue;
                    }
                    $slug = self::normalize_term_value($sanitized);
                    if (isset($name_map[$sanitized])) {
                        $slug = $name_map[$sanitized];
                        $changed = true;
                    } elseif ($slug !== $sanitized) {
                        $changed = true;
                    }
                    $updated[] = $slug;
                }
            }

            $updated = array_values(array_unique(array_filter($updated)));
            if ($changed) {
                $mapped[$taxonomy] = $updated;
            }
        }

        if (empty($mapped)) {
            return;
        }

        $query = array();
        foreach ($mapped as $taxonomy => $values) {
            if (!empty($values)) {
                $query[$taxonomy] = $values;
            }
        }

        if (isset($_GET['job_q'])) {
            $query['job_q'] = sanitize_text_field(wp_unslash($_GET['job_q']));
        }

        $action = get_post_type_archive_link('job');
        if (!$action) {
            $action = home_url('/jobs/');
        }

        $redirect = add_query_arg($query, $action);
        wp_safe_redirect($redirect, 301);
        exit;
    }
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

        if (self::debug_enabled()) {
            $debug_get = map_deep(wp_unslash($_GET), 'sanitize_text_field');
            self::log_debug('Habaq job filters: pre_get_posts', array(
                'url' => self::get_current_url(),
                'get' => $debug_get,
                'is_main_query' => $query->is_main_query(),
                'is_post_type_archive_job' => $query->is_post_type_archive('job'),
            ));
        }

        $query->set('post_type', 'job');
        $query->set('orderby', 'date');
        $query->set('order', 'DESC');

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

        if (self::debug_enabled()) {
            self::log_debug('Habaq job filters: applied', array(
                'tax_query' => $query->get('tax_query'),
                'search' => $query->get('s'),
                'meta_query' => $query->get('meta_query'),
            ));
        }
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

        if (!is_post_type_archive('job')) {
            return $query_vars;
        }

        $post_type = isset($query_vars['post_type']) ? $query_vars['post_type'] : 'post';
        $inherit = isset($query_vars['inherit']) ? (bool) $query_vars['inherit'] : false;
        $is_job = false;

        if (is_array($post_type)) {
            $is_job = in_array('job', $post_type, true);
        } else {
            $is_job = ($post_type === 'job');
        }

        if (!$is_job && !$inherit) {
            return $query_vars;
        }

        $query_vars['post_type'] = 'job';
        $query_vars['orderby'] = 'date';
        $query_vars['order'] = 'DESC';

        $tax_query = self::build_tax_query();
        if (!empty($tax_query)) {
            $query_vars['tax_query'] = self::merge_tax_query(isset($query_vars['tax_query']) ? $query_vars['tax_query'] : array(), $tax_query);
        }

        $keyword = self::get_keyword();
        if ($keyword !== '') {
            $query_vars['s'] = $keyword;
        }

        $meta_query = self::build_active_job_meta_query();
        $query_vars['meta_query'] = self::merge_meta_query(isset($query_vars['meta_query']) ? $query_vars['meta_query'] : array(), $meta_query);

        if (self::debug_enabled()) {
            $debug_get = map_deep(wp_unslash($_GET), 'sanitize_text_field');
            self::log_debug('Habaq job filters: query loop', array(
                'url' => self::get_current_url(),
                'get' => $debug_get,
                'tax_query' => isset($query_vars['tax_query']) ? $query_vars['tax_query'] : array(),
                'search' => isset($query_vars['s']) ? $query_vars['s'] : '',
            ));
        }

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
        $counts = self::get_term_counts();

        $keyword = self::get_keyword();
        $output = '<form class="habaq-job-filters" method="get" action="' . esc_url($action) . '">';
        $output .= '<div class="habaq-job-filters__header">' . esc_html__('تصفية الفرص', 'habaq-wp-core') . '</div>';
        $output .= '<div class="habaq-job-filters__section">';
        $output .= '<label for="habaq-job-q">' . esc_html__('كلمات البحث', 'habaq-wp-core') . '</label>';
        $output .= '<input type="search" id="habaq-job-q" name="job_q" value="' . esc_attr($keyword) . '" placeholder="' . esc_attr__('ابحث عن فرصة', 'habaq-wp-core') . '" />';
        $output .= '</div>';

        foreach (self::get_taxonomies() as $taxonomy) {
            $output .= self::render_taxonomy_filter($taxonomy, $counts);
        }

        $output .= '<div class="habaq-job-filters__actions">';
        $output .= '<button type="submit">' . esc_html__('تطبيق التصفية', 'habaq-wp-core') . '</button>';
        $output .= '<a class="habaq-job-filters__reset" href="' . esc_url($action) . '">' . esc_html__('إعادة ضبط', 'habaq-wp-core') . '</a>';
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
    private static function render_taxonomy_filter($taxonomy, $counts) {
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
            $count = isset($counts[$taxonomy][$term->term_id]) ? (int) $counts[$taxonomy][$term->term_id] : 0;
            $checked = in_array($term->slug, $selected, true);
            $disabled = (!$checked && $count === 0) ? ' disabled' : '';
            $output .= '<label class="habaq-job-filters__option">';
            $output .= '<input type="checkbox" name="' . esc_attr($taxonomy) . '[]" value="' . esc_attr(rawurldecode($term->slug)) . '"' . checked($checked, true, false) . $disabled . ' />';
            $output .= '<span>' . esc_html($term->name) . '</span>';
            $output .= '<em>' . esc_html((string) $count) . '</em>';
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
        $terms = self::parse_term_inputs($raw);

        if (empty($terms)) {
            return array();
        }

        $valid = array();
        foreach ($terms as $term) {
            if (term_exists($term, $taxonomy)) {
                $valid[] = $term;
            }
        }

        return array_values(array_unique($valid));
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
                    'operator' => 'IN',
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
     * Get term counts for active job listings.
     *
     * @return array
     */
    private static function get_term_counts() {
        $locale = get_locale();
        $today = current_time('Y-m-d');
        $key = 'habaq_job_term_counts_' . md5($today . '|' . $locale);
        $cached = get_transient($key);
        if ($cached && is_array($cached)) {
            return $cached;
        }

        global $wpdb;
        $taxonomies = self::get_taxonomies();
        $placeholders = implode(',', array_fill(0, count($taxonomies), '%s'));

        $sql = "
            SELECT tt.taxonomy, tt.term_id, COUNT(DISTINCT p.ID) as term_count
            FROM {$wpdb->posts} p
            INNER JOIN {$wpdb->term_relationships} tr ON p.ID = tr.object_id
            INNER JOIN {$wpdb->term_taxonomy} tt ON tr.term_taxonomy_id = tt.term_taxonomy_id
            LEFT JOIN {$wpdb->postmeta} statusmeta ON (p.ID = statusmeta.post_id AND statusmeta.meta_key = 'habaq_job_status')
            LEFT JOIN {$wpdb->postmeta} deadlinemeta ON (p.ID = deadlinemeta.post_id AND deadlinemeta.meta_key = 'habaq_deadline')
            WHERE p.post_type = 'job'
              AND p.post_status = 'publish'
              AND tt.taxonomy IN ($placeholders)
              AND (statusmeta.meta_value IS NULL OR statusmeta.meta_value != 'closed')
              AND (
                    deadlinemeta.meta_value IS NULL
                    OR deadlinemeta.meta_value = ''
                    OR deadlinemeta.meta_value >= %s
                  )
            GROUP BY tt.taxonomy, tt.term_id
        ";

        $prepared = $wpdb->prepare($sql, array_merge($taxonomies, array($today)));
        $rows = $wpdb->get_results($prepared);

        $counts = array();
        foreach ($taxonomies as $taxonomy) {
            $counts[$taxonomy] = array();
        }

        if (!empty($rows)) {
            foreach ($rows as $row) {
                $taxonomy = $row->taxonomy;
                if (!isset($counts[$taxonomy])) {
                    $counts[$taxonomy] = array();
                }
                $counts[$taxonomy][(int) $row->term_id] = (int) $row->term_count;
            }
        }

        set_transient($key, $counts, 15 * MINUTE_IN_SECONDS);

        return $counts;
    }

    /**
     * Build map of term name to slug.
     *
     * @param string $taxonomy Taxonomy slug.
     * @return array
     */
    private static function get_term_name_to_slug_map($taxonomy) {
        $terms = get_terms(array(
            'taxonomy' => $taxonomy,
            'hide_empty' => false,
        ));

        $map = array();
        if (!is_wp_error($terms)) {
            foreach ($terms as $term) {
                $map[$term->name] = $term->slug;
            }
        }

        return $map;
    }

    /**
     * Enqueue frontend styles.
     *
     * @return void
     */
    private static function enqueue_styles() {
        $css = '.habaq-job-filters{border:1px solid rgba(0,0,0,.08);border-radius:16px;padding:16px;display:grid;gap:16px;box-shadow:0 2px 10px rgba(0,0,0,.04)}
.habaq-job-filters__section label{display:block;margin-bottom:6px;color:#444}
.habaq-job-filters__section input[type="search"]{width:100%;padding:10px 12px;border:1px solid #d5d5d5;border-radius:10px}
.habaq-job-filters__accordion{border:1px solid rgba(0,0,0,.06);border-radius:12px;padding:10px}
.habaq-job-filters__accordion summary{cursor:pointer;list-style:none}
.habaq-job-filters__options{display:grid;gap:8px;margin-top:12px}
.habaq-job-filters__option{display:flex;align-items:center;justify-content:space-between;gap:8px;padding:8px 10px;border-radius:10px;border:1px solid rgba(0,0,0,.05)}
.habaq-job-filters__option input{margin-inline-end:8px}
.habaq-job-filters__option span{flex:1}
.habaq-job-filters__option em{font-style:normal;color:#777}
.habaq-job-filters__actions{display:flex;flex-wrap:wrap;gap:10px}
.habaq-job-filters__actions button,.habaq-job-filters__actions a{border:1px solid rgba(0,0,0,.2);border-radius:10px;padding:10px 16px;cursor:pointer;text-decoration:none;color:inherit}
.habaq-job-filters__actions button:focus,.habaq-job-filters__actions a:focus{outline:2px solid currentColor;outline-offset:2px}
@media (max-width:720px){.habaq-job-filters{padding:12px}}';

        Habaq_WP_Core_Helpers::enqueue_inline_style($css);
    }
}
