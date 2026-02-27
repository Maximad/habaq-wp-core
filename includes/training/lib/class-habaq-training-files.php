<?php

if (!defined('ABSPATH')) {
    exit;
}

class Habaq_Training_Files {

    public static function get_registry() {
        $registry = get_option('habaq_training_registry', array());

        return is_array($registry) ? $registry : array();
    }

    public static function save_registry($registry) {
        update_option('habaq_training_registry', is_array($registry) ? $registry : array(), false);
    }

    public static function get_registry_item($slug) {
        $registry = self::get_registry();

        return isset($registry[$slug]) && is_array($registry[$slug]) ? $registry[$slug] : array();
    }

    public static function get_base_paths() {
        $uploads = wp_upload_dir();

        return array(
            'base_dir' => trailingslashit($uploads['basedir']) . 'habaq-training',
            'base_url' => trailingslashit($uploads['baseurl']) . 'habaq-training',
        );
    }

    public static function get_training_paths($slug) {
        $base = self::get_base_paths();
        $safe_slug = sanitize_title($slug);
        $root_dir = trailingslashit($base['base_dir']) . $safe_slug;
        $root_url = trailingslashit($base['base_url']) . rawurlencode($safe_slug);

        return array(
            'slug' => $safe_slug,
            'root_dir' => $root_dir,
            'root_url' => $root_url,
            'json_path' => trailingslashit($root_dir) . 'training.json',
            'audio_dir' => trailingslashit($root_dir) . 'audio',
            'audio_url' => trailingslashit($root_url) . 'audio',
            'images_dir' => trailingslashit($root_dir) . 'images',
            'images_url' => trailingslashit($root_url) . 'images',
            'captions_dir' => trailingslashit($root_dir) . 'captions',
            'captions_url' => trailingslashit($root_url) . 'captions',
            'attachments_dir' => trailingslashit($root_dir) . 'attachments',
            'attachments_url' => trailingslashit($root_url) . 'attachments',
        );
    }

    public static function ensure_training_dirs($slug) {
        $paths = self::get_training_paths($slug);
        $dirs = array(
            $paths['root_dir'],
            $paths['audio_dir'],
            $paths['images_dir'],
            $paths['captions_dir'],
            $paths['attachments_dir'],
        );

        foreach ($dirs as $dir) {
            if (!is_dir($dir)) {
                wp_mkdir_p($dir);
            }
        }

        return $paths;
    }

    public static function get_allowed_audio_extensions() {
        $allowed = get_allowed_mime_types();
        $exts = array();
        foreach ($allowed as $ext_group => $mime) {
            if (strpos((string) $mime, 'audio/') !== 0) {
                continue;
            }
            $pieces = explode('|', (string) $ext_group);
            foreach ($pieces as $piece) {
                $ext = strtolower(trim((string) $piece));
                if ($ext !== '') {
                    $exts[$ext] = true;
                }
            }
        }

        return array_keys($exts);
    }

    public static function is_allowed_audio_file($path, $filename) {
        $check = wp_check_filetype_and_ext($path, $filename);
        $type = isset($check['type']) ? (string) $check['type'] : '';

        return strpos($type, 'audio/') === 0;
    }

    public static function is_allowed_image_file($path, $filename) {
        $check = wp_check_filetype_and_ext($path, $filename);
        $type = isset($check['type']) ? (string) $check['type'] : '';
        if (strpos($type, 'image/') !== 0) {
            return false;
        }

        $ext = strtolower((string) pathinfo($filename, PATHINFO_EXTENSION));
        if ($ext !== 'svg') {
            return true;
        }

        $allowed = get_allowed_mime_types();
        foreach ($allowed as $ext_group => $mime) {
            if ($mime === 'image/svg+xml' && preg_match('/(^|\|)svg($|\|)/', (string) $ext_group)) {
                return true;
            }
        }

        return false;
    }

    public static function normalize_digits($value) {
        $western = array('0', '1', '2', '3', '4', '5', '6', '7', '8', '9');
        $arabic_indic = array('٠', '١', '٢', '٣', '٤', '٥', '٦', '٧', '٨', '٩');
        $eastern_arabic_indic = array('۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹');

        $value = str_replace($arabic_indic, $western, (string) $value);

        return str_replace($eastern_arabic_indic, $western, $value);
    }

    public static function discover_audio($slug) {
        $paths = self::get_training_paths($slug);
        $uploads = wp_upload_dir();
        $sources = array(
            array('dir' => $paths['audio_dir'], 'url' => $paths['audio_url'], 'source' => 'training-audio-dir'),
            array('dir' => $paths['root_dir'], 'url' => $paths['root_url'], 'source' => 'training-root-dir'),
            array('dir' => trailingslashit($uploads['basedir']) . 'audio', 'url' => trailingslashit($uploads['baseurl']) . 'audio', 'source' => 'legacy-audio-dir'),
        );

        $indexed = array();
        $duplicates = array();

        foreach ($sources as $source) {
            if (!is_dir($source['dir'])) {
                continue;
            }

            $entries = scandir($source['dir']);
            if (!is_array($entries)) {
                continue;
            }

            sort($entries, SORT_NATURAL | SORT_FLAG_CASE);

            foreach ($entries as $entry) {
                if ($entry === '.' || $entry === '..') {
                    continue;
                }

                $path = trailingslashit($source['dir']) . $entry;
                if (!is_file($path)) {
                    continue;
                }

                if (!self::is_allowed_audio_file($path, $entry)) {
                    continue;
                }

                $normalized_name = self::normalize_digits(pathinfo($entry, PATHINFO_FILENAME));
                if (!preg_match('/^(\d+)/', $normalized_name, $matches)) {
                    continue;
                }

                $index = (int) $matches[1];
                if ($index < 1) {
                    continue;
                }

                $item = array(
                    'audio_index' => $index,
                    'url' => trailingslashit($source['url']) . rawurlencode($entry),
                    'filename' => $entry,
                    'source' => $source['source'],
                );

                if (!isset($indexed[$index])) {
                    $indexed[$index] = $item;
                    continue;
                }

                $existing = $indexed[$index];
                $winner = (strcmp($item['filename'], $existing['filename']) < 0) ? $item : $existing;
                $loser = ($winner === $item) ? $existing : $item;
                $indexed[$index] = $winner;
                $duplicates[] = array(
                    'audio_index' => $index,
                    'kept' => $winner['filename'],
                    'ignored' => $loser['filename'],
                );
            }
        }

        ksort($indexed, SORT_NUMERIC);

        $audio_map = array();
        foreach ($indexed as $index => $item) {
            $audio_map[(int) $index] = array(
                'url' => esc_url_raw($item['url']),
                'filename' => sanitize_file_name($item['filename']),
            );
        }

        return array(
            'audio_map' => $audio_map,
            'duplicates' => $duplicates,
            'source_paths' => $sources,
        );
    }

    public static function resolve_image_url($slug, $image_url) {
        $image_url = trim((string) $image_url);
        if ($image_url === '') {
            return '';
        }

        if (preg_match('#^https?://#i', $image_url) || strpos($image_url, '/') === 0) {
            return esc_url_raw($image_url);
        }

        $paths = self::get_training_paths($slug);
        $filename = sanitize_file_name(basename($image_url));
        $candidate = trailingslashit($paths['images_dir']) . $filename;
        if (is_file($candidate) && self::is_allowed_image_file($candidate, $filename)) {
            return esc_url_raw(trailingslashit($paths['images_url']) . rawurlencode($filename));
        }

        return '';
    }

    public static function discover_images_count($slug) {
        $paths = self::get_training_paths($slug);
        if (!is_dir($paths['images_dir'])) {
            return 0;
        }

        $entries = scandir($paths['images_dir']);
        if (!is_array($entries)) {
            return 0;
        }

        $count = 0;
        foreach ($entries as $entry) {
            if ($entry === '.' || $entry === '..') {
                continue;
            }
            $path = trailingslashit($paths['images_dir']) . $entry;
            if (is_file($path) && self::is_allowed_image_file($path, $entry)) {
                $count++;
            }
        }

        return $count;
    }
}
