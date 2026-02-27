<?php

if (!defined('ABSPATH')) {
    exit;
}

class Habaq_Training_Player {

    /**
     * Register shortcode.
     *
     * @return void
     */
    public static function register() {
        if (!shortcode_exists('habaq_training')) {
            add_shortcode('habaq_training', array(__CLASS__, 'render_shortcode'));
        }
    }

    /**
     * Render training shortcode.
     *
     * @param array $atts Shortcode attributes.
     * @return string
     */
    public static function render_shortcode($atts) {
        $defaults = array(
            'slug' => 'default',
            'folder' => 'audio',
            'rtl' => 'auto',
            'autoadvance' => '0',
        );

        $attrs = shortcode_atts($defaults, $atts, 'habaq_training');

        $slug = sanitize_title($attrs['slug']);
        if ($slug === '') {
            $slug = 'default';
        }

        $folder = sanitize_text_field((string) $attrs['folder']);
        $folder = trim(str_replace('..', '', $folder), '/\\');
        if ($folder === '') {
            $folder = 'audio';
        }

        $rtl = self::resolve_rtl($attrs['rtl']);
        $autoadvance = self::to_bool($attrs['autoadvance']);

        $audio_map = self::discover_audio($folder);
        $json_config = self::load_training_json($slug);
        $slides = self::build_slides($json_config, $audio_map);

        $config = array(
            'slug' => $slug,
            'meta' => array(
                'title' => isset($json_config['meta']['title']) ? sanitize_text_field((string) $json_config['meta']['title']) : __('التدريب التفاعلي', 'habaq-wp-core'),
                'rtl' => isset($json_config['meta']['rtl']) ? (bool) $json_config['meta']['rtl'] : $rtl,
                'autoadvance' => isset($json_config['meta']['autoadvance']) ? (bool) $json_config['meta']['autoadvance'] : $autoadvance,
            ),
            'slides' => $slides,
            'audioMap' => $audio_map,
            'messages' => array(),
        );

        $missing_indices = self::find_missing_audio_indices($audio_map);
        if (!empty($missing_indices)) {
            $display_missing = array_map(array(__CLASS__, 'to_arabic_digits'), array_map('strval', $missing_indices));
            $config['messages'][] = sprintf(
                __('هناك ملفات صوتية مفقودة للأرقام: %s', 'habaq-wp-core'),
                implode('، ', $display_missing)
            );
        }

        self::enqueue_assets();

        $container_attrs = sprintf(
            'class="habaq-training" dir="%s" data-habaq-training="1"',
            $config['meta']['rtl'] ? 'rtl' : 'ltr'
        );

        $output  = '<section ' . $container_attrs . '>';
        $output .= '<div class="habaq-training__app" aria-live="polite"></div>';
        $output .= self::render_fallback($config);
        $output .= '<script type="application/json" class="habaq-training-config">' . wp_json_encode($config, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . '</script>';
        $output .= '</section>';

        return $output;
    }

    /**
     * Resolve RTL preference.
     *
     * @param string $rtl_value Raw value.
     * @return bool
     */
    private static function resolve_rtl($rtl_value) {
        $normalized = strtolower(trim((string) $rtl_value));

        if ($normalized === '1' || $normalized === 'true') {
            return true;
        }

        if ($normalized === '0' || $normalized === 'false') {
            return false;
        }

        return is_rtl();
    }

    /**
     * Cast mixed value to bool.
     *
     * @param mixed $value Value.
     * @return bool
     */
    private static function to_bool($value) {
        $normalized = strtolower(trim((string) $value));

        return in_array($normalized, array('1', 'true', 'yes', 'on'), true);
    }

    /**
     * Enqueue training assets.
     *
     * @return void
     */
    private static function enqueue_assets() {
        wp_enqueue_style(
            'habaq-training-player',
            HABAQ_WP_CORE_URL . 'assets/training/habaq-training.css',
            array(),
            HABAQ_WP_CORE_VERSION
        );

        wp_enqueue_script(
            'habaq-training-player',
            HABAQ_WP_CORE_URL . 'assets/training/habaq-training.js',
            array(),
            HABAQ_WP_CORE_VERSION,
            true
        );
    }

    /**
     * Load JSON config from uploads.
     *
     * @param string $slug Training slug.
     * @return array
     */
    private static function load_training_json($slug) {
        $uploads = wp_upload_dir();
        $path = trailingslashit($uploads['basedir']) . 'habaq-training/' . $slug . '/training.json';

        if (!is_readable($path)) {
            return array();
        }

        $raw = file_get_contents($path);
        if ($raw === false || trim($raw) === '') {
            return array();
        }

        $decoded = json_decode($raw, true);

        return is_array($decoded) ? $decoded : array();
    }

    /**
     * Discover audio files and map by numeric index.
     *
     * @param string $folder Folder under uploads.
     * @return array
     */
    private static function discover_audio($folder) {
        $uploads = wp_upload_dir();
        $base_dir = trailingslashit($uploads['basedir']) . $folder;
        $base_url = trailingslashit($uploads['baseurl']) . str_replace('\\', '/', $folder);

        if (!is_dir($base_dir)) {
            return array();
        }

        $entries = scandir($base_dir);
        if (!is_array($entries)) {
            return array();
        }

        $files = array();

        foreach ($entries as $entry) {
            if ($entry === '.' || $entry === '..') {
                continue;
            }

            $file_path = trailingslashit($base_dir) . $entry;
            if (!is_file($file_path)) {
                continue;
            }

            $extension = strtolower((string) pathinfo($entry, PATHINFO_EXTENSION));
            if (!in_array($extension, array('mp3', 'm4a', 'wav', 'ogg'), true)) {
                continue;
            }

            $normalized_name = self::normalize_digits((string) pathinfo($entry, PATHINFO_FILENAME));
            if (!preg_match('/^(\d+)/', $normalized_name, $matches)) {
                continue;
            }

            $audio_index = (int) $matches[1];
            if ($audio_index < 1) {
                continue;
            }

            $files[] = array(
                'audio_index' => $audio_index,
                'url' => trailingslashit($base_url) . rawurlencode($entry),
                'filename' => $entry,
                'ext' => $extension,
            );
        }

        usort($files, array(__CLASS__, 'sort_audio_by_index'));

        $audio_map = array();
        foreach ($files as $file) {
            $audio_map[(int) $file['audio_index']] = array(
                'url' => esc_url_raw($file['url']),
                'filename' => sanitize_file_name($file['filename']),
                'ext' => sanitize_text_field($file['ext']),
            );
        }

        return $audio_map;
    }

    /**
     * Sort callback for audio files.
     *
     * @param array $left Left item.
     * @param array $right Right item.
     * @return int
     */
    private static function sort_audio_by_index($left, $right) {
        return (int) $left['audio_index'] <=> (int) $right['audio_index'];
    }

    /**
     * Convert Arabic digit variants to western digits.
     *
     * @param string $value Input string.
     * @return string
     */
    private static function normalize_digits($value) {
        $western = array('0', '1', '2', '3', '4', '5', '6', '7', '8', '9');
        $arabic_indic = array('٠', '١', '٢', '٣', '٤', '٥', '٦', '٧', '٨', '٩');
        $eastern_arabic_indic = array('۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹');

        $value = str_replace($arabic_indic, $western, $value);

        return str_replace($eastern_arabic_indic, $western, $value);
    }


    /**
     * Find missing audio indices in sorted range.
     *
     * @param array $audio_map Audio map.
     * @return array
     */
    private static function find_missing_audio_indices($audio_map) {
        if (empty($audio_map)) {
            return array(1);
        }

        $indices = array_map('intval', array_keys($audio_map));
        sort($indices);

        $missing = array();
        $max_index = (int) max($indices);

        for ($i = 1; $i <= $max_index; $i++) {
            if (!isset($audio_map[$i])) {
                $missing[] = $i;
            }
        }

        return $missing;
    }

    /**
     * Build slides from JSON config or from discovered audio.
     *
     * @param array $json_config Parsed config.
     * @param array $audio_map Audio map.
     * @return array
     */
    private static function build_slides($json_config, $audio_map) {
        $slides = array();

        if (isset($json_config['slides']) && is_array($json_config['slides']) && !empty($json_config['slides'])) {
            foreach ($json_config['slides'] as $index => $slide) {
                if (!is_array($slide)) {
                    continue;
                }

                $audio_index = isset($slide['audio_index']) ? (int) $slide['audio_index'] : ($index + 1);
                $slides[] = array(
                    'id' => isset($slide['id']) ? sanitize_key((string) $slide['id']) : 'slide-' . ($index + 1),
                    'title' => isset($slide['title']) ? sanitize_text_field((string) $slide['title']) : self::default_slide_title($index),
                    'body_html' => isset($slide['body_html']) ? wp_kses_post((string) $slide['body_html']) : '',
                    'audio_index' => max(1, $audio_index),
                    'image_url' => isset($slide['image_url']) ? esc_url_raw((string) $slide['image_url']) : '',
                );
            }

            if (!empty($slides)) {
                return $slides;
            }
        }

        if (empty($audio_map)) {
            return array();
        }

        $max_index = (int) max(array_keys($audio_map));

        for ($audio_index = 1; $audio_index <= $max_index; $audio_index++) {
            if (!isset($audio_map[$audio_index])) {
                continue;
            }

            $slide_position = $audio_index - 1;
            $slides[] = array(
                'id' => $slide_position === 0 ? 'intro' : 'slide-' . $slide_position,
                'title' => $slide_position === 0 ? 'مقدّمة' : 'الشريحة ' . self::to_arabic_digits((string) $slide_position),
                'body_html' => '',
                'audio_index' => $audio_index,
                'image_url' => '',
            );
        }

        return $slides;
    }

    /**
     * Default title from index.
     *
     * @param int $index Slide index.
     * @return string
     */
    private static function default_slide_title($index) {
        if ((int) $index === 0) {
            return 'مقدّمة';
        }

        return 'الشريحة ' . self::to_arabic_digits((string) $index);
    }

    /**
     * Render no-JS fallback list.
     *
     * @param array $config Training config.
     * @return string
     */
    private static function render_fallback($config) {
        $output = '<div class="habaq-training__fallback">';
        $output .= '<p class="habaq-training__fallback-note">' . esc_html__('نسخة أساسية تعمل بدون جافاسكربت:', 'habaq-wp-core') . '</p>';

        if (!empty($config['messages'])) {
            foreach ($config['messages'] as $message) {
                $output .= '<p class="habaq-training__message">' . esc_html($message) . '</p>';
            }
        }

        $output .= '<ol class="habaq-training__fallback-list">';

        foreach ($config['slides'] as $slide) {
            $audio_index = isset($slide['audio_index']) ? (int) $slide['audio_index'] : 0;
            $audio_url = isset($config['audioMap'][$audio_index]['url']) ? $config['audioMap'][$audio_index]['url'] : '';

            $output .= '<li class="habaq-training__fallback-item">';
            $output .= '<h3 class="habaq-training__fallback-title">' . esc_html($slide['title']) . '</h3>';
            if ($audio_url) {
                $output .= '<audio controls preload="none" src="' . esc_url($audio_url) . '"></audio>';
            } else {
                $output .= '<p class="habaq-training__message">' . esc_html__('لا يوجد ملف صوتي لهذه الشريحة.', 'habaq-wp-core') . '</p>';
            }
            $output .= '</li>';
        }

        $output .= '</ol>';
        $output .= '</div>';

        return $output;
    }

    /**
     * Convert western digits to Arabic-Indic.
     *
     * @param string $value Number.
     * @return string
     */
    private static function to_arabic_digits($value) {
        $western = array('0', '1', '2', '3', '4', '5', '6', '7', '8', '9');
        $arabic_indic = array('٠', '١', '٢', '٣', '٤', '٥', '٦', '٧', '٨', '٩');

        return str_replace($western, $arabic_indic, $value);
    }
}
