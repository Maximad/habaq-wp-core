<?php

if (!defined('ABSPATH')) {
    exit;
}

class Habaq_Training_Files {
    private static $media_cache = array();

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
            'videos_dir' => trailingslashit($root_dir) . 'videos',
            'videos_url' => trailingslashit($root_url) . 'videos',
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
            $paths['videos_dir'],
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
        $media = self::discover_media($slug);

        return array(
            'audio_map' => $media['audio_map'],
            'duplicates' => $media['audio_duplicates'],
            'source_paths' => array(
                array('dir' => $media['paths']['audio_dir'], 'url' => $media['paths']['audio_url'], 'source' => 'training-audio-dir'),
            ),
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

        $media = self::discover_media($slug);
        $paths = $media['paths'];
        $filename = sanitize_file_name(basename($image_url));
        $candidate = trailingslashit($paths['images_dir']) . $filename;
        if (is_file($candidate) && self::is_allowed_image_file($candidate, $filename)) {
            return esc_url_raw(trailingslashit($paths['images_url']) . rawurlencode($filename));
        }

        return '';
    }

    public static function discover_images_count($slug) {
        $media = self::discover_media($slug);

        return count($media['image_files']);
    }

    public static function discover_media($slug) {
        $safe_slug = sanitize_title($slug);
        if (isset(self::$media_cache[$safe_slug])) {
            return self::$media_cache[$safe_slug];
        }

        $paths = self::get_training_paths($safe_slug);
        $audio_scan = self::scan_indexed_media_dir($paths['audio_dir'], $paths['audio_url'], 'audio');
        $image_scan = self::scan_indexed_media_dir($paths['images_dir'], $paths['images_url'], 'image');
        $video_scan = self::scan_indexed_media_dir($paths['videos_dir'], $paths['videos_url'], 'video');

        $payload = array(
            'paths' => $paths,
            'audio_map' => $audio_scan['map'],
            'image_map' => $image_scan['map'],
            'audio_duplicates' => $audio_scan['duplicates'],
            'image_duplicates' => $image_scan['duplicates'],
            'video_map' => $video_scan['map'],
            'video_duplicates' => $video_scan['duplicates'],
            'image_files' => $image_scan['files'],
        );

        self::$media_cache[$safe_slug] = $payload;

        return $payload;
    }

    private static function scan_indexed_media_dir($dir, $url, $kind) {
        $map = array();
        $duplicates = array();
        $files = array();

        if (!is_dir($dir)) {
            return array('map' => $map, 'duplicates' => $duplicates, 'files' => $files);
        }

        $entries = scandir($dir);
        if (!is_array($entries)) {
            return array('map' => $map, 'duplicates' => $duplicates, 'files' => $files);
        }

        sort($entries, SORT_NATURAL | SORT_FLAG_CASE);

        foreach ($entries as $entry) {
            if ($entry === '.' || $entry === '..') {
                continue;
            }

            $path = trailingslashit($dir) . $entry;
            if (!is_file($path)) {
                continue;
            }

            $check = wp_check_filetype_and_ext($path, $entry);
            $mime = isset($check['type']) ? (string) $check['type'] : '';
            if ($kind === 'audio' && strpos($mime, 'audio/') !== 0) {
                continue;
            }
            if ($kind === 'image' && strpos($mime, 'image/') !== 0) {
                continue;
            }
            if ($kind === 'video' && strpos($mime, 'video/') !== 0) {
                continue;
            }

            $filename = sanitize_file_name($entry);
            $files[$filename] = esc_url_raw(trailingslashit($url) . rawurlencode($entry));

            $normalized_name = self::normalize_digits(pathinfo($entry, PATHINFO_FILENAME));
            if (!preg_match('/^(\d+)/', $normalized_name, $matches)) {
                continue;
            }

            $index = (int) $matches[1];
            if ($index < 0) {
                continue;
            }

            $candidate = array(
                'index' => $index,
                'url' => esc_url_raw(trailingslashit($url) . rawurlencode($entry)),
                'filename' => $filename,
                'priority' => self::media_extension_priority($filename, $kind),
            );

            if (!isset($map[$index])) {
                $map[$index] = $candidate;
                continue;
            }

            $winner = self::pick_better_media_candidate($map[$index], $candidate);
            $loser = ($winner === $candidate) ? $map[$index] : $candidate;
            $map[$index] = $winner;
            $duplicates[] = array(
                $kind . '_index' => $index,
                'kept' => $winner['filename'],
                'ignored' => $loser['filename'],
            );
        }

        ksort($map, SORT_NUMERIC);

        $sanitized_map = array();
        foreach ($map as $index => $item) {
            $sanitized_map[(int) $index] = array(
                'url' => $item['url'],
                'filename' => $item['filename'],
            );
        }

        return array(
            'map' => $sanitized_map,
            'duplicates' => $duplicates,
            'files' => $files,
        );
    }

    private static function pick_better_media_candidate($current, $incoming) {
        if ((int) $incoming['priority'] < (int) $current['priority']) {
            return $incoming;
        }

        if ((int) $incoming['priority'] > (int) $current['priority']) {
            return $current;
        }

        if (strcmp((string) $incoming['filename'], (string) $current['filename']) < 0) {
            return $incoming;
        }

        return $current;
    }

    private static function media_extension_priority($filename, $kind) {
        $ext = strtolower((string) pathinfo((string) $filename, PATHINFO_EXTENSION));
        $rankings = array(
            'image' => array('avif', 'webp', 'png', 'jpg', 'jpeg', 'gif'),
            'audio' => array('mp3', 'm4a', 'aac', 'ogg', 'wav'),
            'video' => array('mp4', 'webm', 'ogv', 'mov'),
        );

        if (!isset($rankings[$kind])) {
            return 999;
        }

        $priority = array_search($ext, $rankings[$kind], true);

        return ($priority === false) ? 900 : (int) $priority;
    }
}
