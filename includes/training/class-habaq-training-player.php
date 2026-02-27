<?php

if (!defined('ABSPATH')) {
    exit;
}

require_once __DIR__ . '/lib/class-habaq-training-files.php';
require_once __DIR__ . '/admin/class-habaq-training-admin.php';

class Habaq_Training_Player {
    private static $registered = false;

    public static function register() {
        if (self::$registered) {
            return;
        }

        if (!shortcode_exists('habaq_training')) {
            add_shortcode('habaq_training', array(__CLASS__, 'render_shortcode'));
        }

        add_action('wp_ajax_habaq_training_save_progress', array(__CLASS__, 'ajax_save_progress'));
        add_action('wp_ajax_habaq_training_mark_complete', array(__CLASS__, 'ajax_mark_complete'));

        if (is_admin()) {
            Habaq_Training_Admin::register();
        }

        self::$registered = true;
    }

    public static function render_shortcode($atts) {
        $raw_atts = is_array($atts) ? $atts : array();
        $attrs = shortcode_atts(
            array(
                'slug' => 'default',
                'rtl' => 'auto',
                'autoadvance' => '0',
                'access' => 'public',
                'roles' => '',
                'cap' => '',
                'preview_slides' => '0',
                'version' => '',
                'require_ack' => '',
                'lang' => '',
                'folder' => '',
            ),
            $raw_atts,
            'habaq_training'
        );

        $slug = sanitize_title($attrs['slug']);
        if ($slug === '') {
            $slug = 'default';
        }

        $registry_item = Habaq_Training_Files::get_registry_item($slug);
        $json_config = self::load_training_json($slug);
        $meta = (isset($json_config['meta']) && is_array($json_config['meta'])) ? $json_config['meta'] : array();

        $lang_default = self::resolve_default_language();
        $lang = sanitize_key((string) self::resolve_source_value($raw_atts, 'lang', $meta, 'lang', isset($registry_item['lang']) ? $registry_item['lang'] : $lang_default));

        $access = self::normalize_access_mode(self::resolve_source_value($raw_atts, 'access', $meta, 'access', isset($registry_item['access']) ? $registry_item['access'] : 'public'));
        $roles = self::normalize_roles(self::resolve_source_value($raw_atts, 'roles', $meta, 'roles', isset($registry_item['roles']) ? $registry_item['roles'] : ''));
        $cap = sanitize_text_field((string) self::resolve_source_value($raw_atts, 'cap', $meta, 'cap', isset($registry_item['cap']) ? $registry_item['cap'] : ''));
        $version = sanitize_text_field((string) self::resolve_source_value($raw_atts, 'version', $meta, 'version', isset($registry_item['version']) ? $registry_item['version'] : '1'));
        if ($version === '') {
            $version = '1';
        }

        $preview_slides = max(0, (int) self::resolve_source_value($raw_atts, 'preview_slides', $meta, 'preview_slides', isset($registry_item['preview_slides']) ? $registry_item['preview_slides'] : '0'));
        $rtl = self::resolve_rtl(self::resolve_source_value($raw_atts, 'rtl', $meta, 'rtl', isset($registry_item['rtl']) ? ($registry_item['rtl'] ? '1' : '0') : 'auto'), $lang);
        $autoadvance = self::to_bool(self::resolve_source_value($raw_atts, 'autoadvance', $meta, 'autoadvance', isset($registry_item['autoadvance']) ? ($registry_item['autoadvance'] ? '1' : '0') : '0'));

        $default_require_ack = ($access === 'public') ? '0' : '1';
        if (isset($registry_item['require_ack'])) {
            $default_require_ack = $registry_item['require_ack'] ? '1' : '0';
        }
        $require_ack = self::to_bool(self::resolve_source_value($raw_atts, 'require_ack', $meta, 'require_ack', $default_require_ack));

        if (!self::evaluate_access($access, $roles, $cap)) {
            return self::render_login_gate();
        }

        $media_result = Habaq_Training_Files::discover_media($slug);
        $audio_map = $media_result['audio_map'];
        $image_map = $media_result['image_map'];
        $slides = self::build_slides($slug, $json_config, $audio_map, $image_map, $media_result['image_files']);
        $resume = self::get_resume_state($slug, $version);

        $config = array(
            'slug' => $slug,
            'meta' => array(
                'title' => isset($meta['title']) ? sanitize_text_field((string) $meta['title']) : (isset($registry_item['title']) ? sanitize_text_field((string) $registry_item['title']) : __('التدريب التفاعلي', 'habaq-wp-core')),
                'rtl' => $rtl,
                'autoadvance' => $autoadvance,
                'access' => $access,
                'roles' => $roles,
                'cap' => $cap,
                'preview_slides' => $preview_slides,
                'require_ack' => $require_ack,
                'version' => $version,
                'lang' => $lang,
            ),
            'slides' => $slides,
            'audioMap' => $audio_map,
            'messages' => array(),
            'resume' => $resume,
            'viewer' => array(
                'is_logged_in' => is_user_logged_in(),
                'can_track_server' => is_user_logged_in(),
            ),
            'login_url' => esc_url_raw(wp_login_url(get_permalink())),
        );

        $index_base = isset($meta['index_base']) ? (int) $meta['index_base'] : 1;
        if (!isset($audio_map[$index_base])) {
            $config['messages'][] = sprintf(
                __('لم يتم العثور على الملف الصوتي رقم %d (المقدمة).', 'habaq-wp-core'),
                (int) $index_base
            );
        }

        foreach ($media_result['audio_duplicates'] as $duplicate) {
            $config['messages'][] = sprintf(
                __('تكرار في الملف الصوتي رقم %d: تم اعتماد %s وتجاهل %s', 'habaq-wp-core'),
                (int) $duplicate['audio_index'],
                sanitize_file_name($duplicate['kept']),
                sanitize_file_name($duplicate['ignored'])
            );
        }

        self::enqueue_assets($slug, $version);

        $output  = '<section class="habaq-training" dir="' . ($rtl ? 'rtl' : 'ltr') . '" data-habaq-training="1">';
        $output .= '<div class="habaq-training__app" aria-live="polite"></div>';
        $output .= self::render_fallback($config);
        $output .= '<script type="application/json" class="habaq-training-config">' . wp_json_encode($config, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . '</script>';
        $output .= '</section>';

        return $output;
    }

    private static function resolve_default_language() {
        if (function_exists('pll_current_language')) {
            $lang = pll_current_language();
            if (is_string($lang) && $lang !== '') {
                return $lang;
            }
        }

        $locale = determine_locale();

        return sanitize_key(substr((string) $locale, 0, 2));
    }

    private static function resolve_source_value($raw_atts, $att_key, $meta, $meta_key, $fallback) {
        if (array_key_exists($att_key, $raw_atts) && $raw_atts[$att_key] !== '') {
            return $raw_atts[$att_key];
        }

        if (isset($meta[$meta_key]) && $meta[$meta_key] !== '') {
            return $meta[$meta_key];
        }

        return $fallback;
    }

    private static function normalize_access_mode($mode) {
        $mode = strtolower(trim((string) $mode));

        return in_array($mode, array('public', 'logged_in', 'roles', 'cap'), true) ? $mode : 'public';
    }

    private static function normalize_roles($roles) {
        $list = is_array($roles) ? $roles : explode(',', (string) $roles);
        $clean = array();

        foreach ($list as $role) {
            $key = sanitize_key((string) $role);
            if ($key !== '') {
                $clean[] = $key;
            }
        }

        return array_values(array_unique($clean));
    }

    private static function evaluate_access($access, $roles, $cap) {
        if ($access === 'public') {
            return true;
        }

        if (!is_user_logged_in()) {
            return false;
        }

        if ($access === 'logged_in') {
            return true;
        }

        if ($access === 'cap') {
            return $cap !== '' && current_user_can($cap);
        }

        if ($access === 'roles') {
            $user = wp_get_current_user();
            $user_roles = is_array($user->roles) ? $user->roles : array();

            foreach ($user_roles as $role) {
                if (in_array($role, $roles, true)) {
                    return true;
                }
            }

            return false;
        }

        return false;
    }

    private static function render_login_gate() {
        $login_url = wp_login_url(get_permalink());

        return '<section class="habaq-training habaq-training--gate"><div class="habaq-training__gate"><p class="habaq-training__gate-message">' .
            esc_html__('هذا التدريب متاح لأعضاء الفريق فقط.', 'habaq-wp-core') .
            '</p><a class="habaq-training__button habaq-training__button--primary" href="' . esc_url($login_url) . '">' .
            esc_html__('تسجيل الدخول', 'habaq-wp-core') .
            '</a></div></section>';
    }

    private static function resolve_rtl($rtl_value, $lang) {
        $normalized = strtolower(trim((string) $rtl_value));
        if ($normalized === '1' || $normalized === 'true') {
            return true;
        }
        if ($normalized === '0' || $normalized === 'false') {
            return false;
        }

        return $lang === 'ar' ? true : is_rtl();
    }

    private static function to_bool($value) {
        if (is_bool($value)) {
            return $value;
        }

        return in_array(strtolower(trim((string) $value)), array('1', 'true', 'yes', 'on'), true);
    }

    private static function enqueue_assets($slug, $version) {
        wp_enqueue_style('habaq-training-player', HABAQ_WP_CORE_URL . 'assets/training/habaq-training.css', array(), HABAQ_WP_CORE_VERSION);
        wp_enqueue_script('habaq-training-player', HABAQ_WP_CORE_URL . 'assets/training/habaq-training.js', array(), HABAQ_WP_CORE_VERSION, true);

        if (is_user_logged_in()) {
            $bootstrap = array(
                'ajaxUrl' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('habaq_training_progress'),
                'slug' => $slug,
                'version' => $version,
                'isLoggedIn' => true,
                'canTrackServer' => true,
            );
            wp_add_inline_script('habaq-training-player', 'window.habaqTraining = ' . wp_json_encode($bootstrap, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . ';', 'before');
        }
    }

    private static function load_training_json($slug) {
        $paths = Habaq_Training_Files::get_training_paths($slug);
        if (!is_readable($paths['json_path'])) {
            return array();
        }

        $raw = file_get_contents($paths['json_path']);
        if ($raw === false || trim($raw) === '') {
            return array();
        }

        $decoded = json_decode($raw, true);

        return is_array($decoded) ? $decoded : array();
    }

    private static function build_slides($slug, $json_config, $audio_map, $image_map, $image_files) {
        $slides = array();
        $meta = (isset($json_config['meta']) && is_array($json_config['meta'])) ? $json_config['meta'] : array();
        $index_base = isset($meta['index_base']) ? (int) $meta['index_base'] : 1;

        if (isset($json_config['slides']) && is_array($json_config['slides']) && !empty($json_config['slides'])) {
            foreach ($json_config['slides'] as $index => $slide) {
                if (!is_array($slide)) {
                    continue;
                }
                $fallback_index = (int) $index + $index_base;
                $audio_index = isset($slide['audio_index']) ? (int) $slide['audio_index'] : $fallback_index;
                $image_index = self::resolve_image_index($slide, $audio_index, $fallback_index);
                $image_url = self::resolve_slide_image_url($slide, $image_index, $image_map, $image_files);

                $slides[] = array(
                    'id' => isset($slide['id']) ? sanitize_key((string) $slide['id']) : 'slide-' . ($index + 1),
                    'title' => isset($slide['title']) ? sanitize_text_field((string) $slide['title']) : self::default_slide_title($index),
                    'body_html' => isset($slide['body_html']) ? wp_kses_post((string) $slide['body_html']) : '',
                    'audio_index' => max(0, $audio_index),
                    'image_url' => $image_url,
                );
            }

            if (!empty($slides)) {
                return $slides;
            }
        }

        if (empty($audio_map)) {
            return array();
        }

        $max = (int) max(array_keys($audio_map));
        for ($i = 0; $i <= $max; $i++) {
            if (!isset($audio_map[$i])) {
                continue;
            }
            $pos = $i - $index_base;
            $image_url = isset($image_map[$i]['url']) ? (string) $image_map[$i]['url'] : '';
            $slides[] = array(
                'id' => $pos === 0 ? 'intro' : 'slide-' . $pos,
                'title' => $pos === 0 ? 'مقدّمة' : 'الشريحة ' . self::to_arabic_digits((string) $pos),
                'body_html' => '',
                'audio_index' => $i,
                'image_url' => $image_url,
            );
        }

        return $slides;
    }

    private static function get_resume_state($slug, $version) {
        $resume = array('current_slide' => 0, 'completed' => false, 'completed_at' => 0, 'updated_at' => 0);

        if (!is_user_logged_in()) {
            return $resume;
        }

        $map = get_user_meta(get_current_user_id(), 'habaq_training_progress', true);
        if (!is_array($map) || !isset($map[$slug]) || !is_array($map[$slug])) {
            return $resume;
        }

        $item = $map[$slug];
        if (isset($item['version']) && sanitize_text_field((string) $item['version']) !== $version) {
            $item['current_slide'] = 0;
            $item['completed_at'] = 0;
            $item['completed'] = false;
            $item['version'] = $version;
            $item['updated_at'] = time();
            $map[$slug] = $item;
            update_user_meta(get_current_user_id(), 'habaq_training_progress', $map);
        }

        $resume['current_slide'] = isset($item['current_slide']) ? max(0, (int) $item['current_slide']) : 0;
        $resume['updated_at'] = isset($item['updated_at']) ? (int) $item['updated_at'] : 0;
        $resume['completed_at'] = isset($item['completed_at']) ? (int) $item['completed_at'] : 0;
        $resume['completed'] = $resume['completed_at'] > 0 || !empty($item['completed']);

        return $resume;
    }

    private static function default_slide_title($index) {
        if ((int) $index === 0) {
            return 'مقدّمة';
        }

        return 'الشريحة ' . self::to_arabic_digits((string) $index);
    }

    private static function resolve_image_index($slide, $audio_index, $fallback_index) {
        if (isset($slide['image_index']) && is_numeric($slide['image_index'])) {
            return (int) $slide['image_index'];
        }

        if (isset($slide['image_url'])) {
            $image_url = trim((string) $slide['image_url']);
            if ($image_url !== '' && preg_match('/^\d+$/', Habaq_Training_Files::normalize_digits($image_url))) {
                return (int) Habaq_Training_Files::normalize_digits($image_url);
            }
        }

        if (is_numeric($audio_index)) {
            return (int) $audio_index;
        }

        return (int) $fallback_index;
    }

    private static function resolve_slide_image_url($slide, $image_index, $image_map, $image_files) {
        if (isset($image_map[$image_index]['url'])) {
            return (string) $image_map[$image_index]['url'];
        }

        if (!isset($slide['image_url'])) {
            return '';
        }

        $image_url = trim((string) $slide['image_url']);
        if ($image_url === '' || preg_match('/^\d+$/', Habaq_Training_Files::normalize_digits($image_url))) {
            return '';
        }

        $legacy_filename = sanitize_file_name(basename($image_url));
        if ($legacy_filename === '') {
            return '';
        }

        return isset($image_files[$legacy_filename]) ? (string) $image_files[$legacy_filename] : '';
    }

    private static function render_fallback($config) {
        $output = '<div class="habaq-training__fallback"><p class="habaq-training__fallback-note">' . esc_html__('نسخة أساسية تعمل بدون جافاسكربت:', 'habaq-wp-core') . '</p><ol class="habaq-training__fallback-list">';
        foreach ($config['slides'] as $slide) {
            $audio_index = isset($slide['audio_index']) ? (int) $slide['audio_index'] : 0;
            $audio_url = isset($config['audioMap'][$audio_index]['url']) ? $config['audioMap'][$audio_index]['url'] : '';
            $output .= '<li class="habaq-training__fallback-item"><h3 class="habaq-training__fallback-title">' . esc_html($slide['title']) . '</h3>';
            if ($audio_url) {
                $output .= '<audio controls preload="none" src="' . esc_url($audio_url) . '"></audio>';
            }
            $output .= '</li>';
        }
        $output .= '</ol></div>';

        return $output;
    }

    public static function ajax_save_progress() {
        if (!is_user_logged_in()) {
            wp_send_json_error(array('message' => 'forbidden'), 403);
        }
        check_ajax_referer('habaq_training_progress', 'nonce');

        $slug = isset($_POST['slug']) ? sanitize_title(wp_unslash($_POST['slug'])) : '';
        if ($slug === '') {
            wp_send_json_error(array('message' => 'invalid_slug'), 400);
        }

        $map = get_user_meta(get_current_user_id(), 'habaq_training_progress', true);
        if (!is_array($map)) {
            $map = array();
        }

        $previous = isset($map[$slug]) && is_array($map[$slug]) ? $map[$slug] : array();
        $completed_at = isset($previous['completed_at']) ? (int) $previous['completed_at'] : 0;

        $map[$slug] = array(
            'current_slide' => isset($_POST['current_slide']) ? max(0, (int) $_POST['current_slide']) : 0,
            'updated_at' => time(),
            'completed_at' => $completed_at,
            'completed' => $completed_at > 0,
            'version' => isset($_POST['version']) ? sanitize_text_field(wp_unslash($_POST['version'])) : '1',
            'score' => null,
        );

        update_user_meta(get_current_user_id(), 'habaq_training_progress', $map);
        wp_send_json_success(array('saved' => true));
    }

    public static function ajax_mark_complete() {
        if (!is_user_logged_in()) {
            wp_send_json_error(array('message' => 'forbidden'), 403);
        }
        check_ajax_referer('habaq_training_progress', 'nonce');

        $slug = isset($_POST['slug']) ? sanitize_title(wp_unslash($_POST['slug'])) : '';
        if ($slug === '') {
            wp_send_json_error(array('message' => 'invalid_slug'), 400);
        }

        $map = get_user_meta(get_current_user_id(), 'habaq_training_progress', true);
        if (!is_array($map)) {
            $map = array();
        }

        $map[$slug] = array(
            'current_slide' => isset($_POST['current_slide']) ? max(0, (int) $_POST['current_slide']) : 0,
            'updated_at' => time(),
            'completed_at' => time(),
            'completed' => true,
            'version' => isset($_POST['version']) ? sanitize_text_field(wp_unslash($_POST['version'])) : '1',
            'score' => null,
        );

        update_user_meta(get_current_user_id(), 'habaq_training_progress', $map);
        wp_send_json_success(array('completed' => true));
    }

    private static function to_arabic_digits($value) {
        $western = array('0', '1', '2', '3', '4', '5', '6', '7', '8', '9');
        $arabic = array('٠', '١', '٢', '٣', '٤', '٥', '٦', '٧', '٨', '٩');

        return str_replace($western, $arabic, (string) $value);
    }
}
