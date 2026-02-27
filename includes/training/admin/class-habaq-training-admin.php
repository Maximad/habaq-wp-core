<?php

if (!defined('ABSPATH')) {
    exit;
}

class Habaq_Training_Admin {

    public static function register() {
        add_action('admin_menu', array(__CLASS__, 'register_menu'));
        add_action('admin_enqueue_scripts', array(__CLASS__, 'enqueue_assets'));

        add_action('admin_post_habaq_training_save', array(__CLASS__, 'handle_save'));
        add_action('admin_post_habaq_training_upload_json', array(__CLASS__, 'handle_upload_json'));
        add_action('admin_post_habaq_training_upload_media', array(__CLASS__, 'handle_upload_media'));
        add_action('admin_post_habaq_training_import_zip', array(__CLASS__, 'handle_import_zip'));
        add_action('admin_post_habaq_training_delete', array(__CLASS__, 'handle_delete'));
    }

    public static function register_menu() {
        add_menu_page(
            __('Trainings', 'habaq-wp-core'),
            __('Trainings', 'habaq-wp-core'),
            'manage_options',
            'habaq-trainings',
            array(__CLASS__, 'render_page'),
            'dashicons-welcome-learn-more',
            58
        );
    }

    public static function enqueue_assets($hook) {
        if ($hook !== 'toplevel_page_habaq-trainings') {
            return;
        }

        wp_enqueue_style('habaq-training-admin', HABAQ_WP_CORE_URL . 'assets/training-admin/admin.css', array(), HABAQ_WP_CORE_VERSION);
        wp_enqueue_script('habaq-training-admin', HABAQ_WP_CORE_URL . 'assets/training-admin/admin.js', array(), HABAQ_WP_CORE_VERSION, true);
    }

    public static function render_page() {
        if (!current_user_can('manage_options')) {
            wp_die(esc_html__('غير مصرح.', 'habaq-wp-core'));
        }

        $action = isset($_GET['action']) ? sanitize_key(wp_unslash($_GET['action'])) : '';
        if ($action === 'edit' || $action === 'new') {
            self::render_edit_page();

            return;
        }

        self::render_list_page();
    }

    private static function render_list_page() {
        $registry = Habaq_Training_Files::get_registry();
        ?>
        <div class="wrap habaq-training-admin">
            <h1><?php esc_html_e('Trainings', 'habaq-wp-core'); ?>
                <a class="page-title-action" href="<?php echo esc_url(admin_url('admin.php?page=habaq-trainings&action=new')); ?>"><?php esc_html_e('Add New', 'habaq-wp-core'); ?></a>
            </h1>
            <?php self::render_notice(); ?>
            <table class="widefat striped">
                <thead><tr>
                    <th><?php esc_html_e('Title', 'habaq-wp-core'); ?></th>
                    <th><?php esc_html_e('Slug', 'habaq-wp-core'); ?></th>
                    <th><?php esc_html_e('Access', 'habaq-wp-core'); ?></th>
                    <th><?php esc_html_e('Language', 'habaq-wp-core'); ?></th>
                    <th><?php esc_html_e('Updated', 'habaq-wp-core'); ?></th>
                    <th><?php esc_html_e('Shortcode', 'habaq-wp-core'); ?></th>
                    <th><?php esc_html_e('Actions', 'habaq-wp-core'); ?></th>
                </tr></thead>
                <tbody>
                <?php if (empty($registry)) : ?>
                    <tr><td colspan="7"><?php esc_html_e('لا توجد تدريبات بعد.', 'habaq-wp-core'); ?></td></tr>
                <?php else : ?>
                    <?php foreach ($registry as $slug => $item) : ?>
                        <tr>
                            <td><?php echo esc_html(isset($item['title']) ? $item['title'] : $slug); ?></td>
                            <td><code><?php echo esc_html($slug); ?></code></td>
                            <td><?php echo esc_html(isset($item['access']) ? $item['access'] : 'public'); ?></td>
                            <td><?php echo esc_html(isset($item['lang']) ? $item['lang'] : 'ar'); ?></td>
                            <td><?php echo esc_html(isset($item['updated_at']) ? gmdate('Y-m-d H:i', (int) $item['updated_at']) : '-'); ?></td>
                            <td><code>[habaq_training slug="<?php echo esc_attr($slug); ?>"]</code></td>
                            <td>
                                <a href="<?php echo esc_url(admin_url('admin.php?page=habaq-trainings&action=edit&slug=' . rawurlencode($slug))); ?>"><?php esc_html_e('Edit', 'habaq-wp-core'); ?></a>
                                |
                                <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" style="display:inline">
                                    <?php wp_nonce_field('habaq_training_delete_' . $slug); ?>
                                    <input type="hidden" name="action" value="habaq_training_delete" />
                                    <input type="hidden" name="slug" value="<?php echo esc_attr($slug); ?>" />
                                    <button class="button-link delete-training" type="submit"><?php esc_html_e('Delete', 'habaq-wp-core'); ?></button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
        <?php
    }

    private static function render_edit_page() {
        $slug = isset($_GET['slug']) ? sanitize_title(wp_unslash($_GET['slug'])) : '';
        $is_new = $slug === '';
        $item = $is_new ? array() : Habaq_Training_Files::get_registry_item($slug);

        if (!$is_new && empty($item)) {
            echo '<div class="wrap"><p>' . esc_html__('التدريب غير موجود.', 'habaq-wp-core') . '</p></div>';

            return;
        }

        $lang = isset($item['lang']) ? $item['lang'] : 'ar';
        $languages = self::get_languages();
        $paths = $slug ? Habaq_Training_Files::ensure_training_dirs($slug) : array();
        $diagnostics = $slug ? self::get_diagnostics($slug) : array();
        ?>
        <div class="wrap habaq-training-admin">
            <h1><?php echo $is_new ? esc_html__('Add Training', 'habaq-wp-core') : esc_html__('Edit Training', 'habaq-wp-core'); ?></h1>
            <?php self::render_notice(); ?>

            <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" class="habaq-training-admin__card">
                <?php wp_nonce_field('habaq_training_save'); ?>
                <input type="hidden" name="action" value="habaq_training_save" />
                <input type="hidden" name="existing_slug" value="<?php echo esc_attr($slug); ?>" />
                <table class="form-table">
                    <tr><th><label for="title">Title</label></th><td><input name="title" id="title" class="regular-text" value="<?php echo esc_attr(isset($item['title']) ? $item['title'] : ''); ?>" required /></td></tr>
                    <tr><th><label for="slug">Slug</label></th><td><input name="slug" id="slug" class="regular-text" value="<?php echo esc_attr($slug); ?>" <?php echo $is_new ? '' : 'readonly'; ?> required /></td></tr>
                    <tr><th>Access</th><td><select name="access" id="access">
                        <?php foreach (array('public', 'logged_in', 'roles', 'cap') as $access) : ?>
                            <option value="<?php echo esc_attr($access); ?>" <?php selected(isset($item['access']) ? $item['access'] : 'public', $access); ?>><?php echo esc_html($access); ?></option>
                        <?php endforeach; ?>
                    </select></td></tr>
                    <tr><th>Roles</th><td><input name="roles" class="regular-text" value="<?php echo esc_attr(implode(',', isset($item['roles']) && is_array($item['roles']) ? $item['roles'] : array())); ?>" /></td></tr>
                    <tr><th>Capability</th><td><input name="cap" class="regular-text" value="<?php echo esc_attr(isset($item['cap']) ? $item['cap'] : ''); ?>" /></td></tr>
                    <tr><th>Language</th><td><select name="lang">
                        <?php foreach ($languages as $code => $name) : ?>
                            <option value="<?php echo esc_attr($code); ?>" <?php selected($lang, $code); ?>><?php echo esc_html($name); ?></option>
                        <?php endforeach; ?>
                    </select></td></tr>
                    <tr><th>RTL</th><td><label><input type="checkbox" name="rtl" value="1" <?php checked(!isset($item['rtl']) ? true : !empty($item['rtl'])); ?> /> <?php esc_html_e('Enable RTL', 'habaq-wp-core'); ?></label></td></tr>
                    <tr><th>Autoadvance</th><td><label><input type="checkbox" name="autoadvance" value="1" <?php checked(!empty($item['autoadvance'])); ?> /></label></td></tr>
                    <tr><th>Preview Slides</th><td><input type="number" min="0" name="preview_slides" value="<?php echo esc_attr(isset($item['preview_slides']) ? (int) $item['preview_slides'] : 0); ?>" /></td></tr>
                    <tr><th>Require Ack</th><td><label><input type="checkbox" name="require_ack" value="1" <?php checked(!empty($item['require_ack'])); ?> /></label></td></tr>
                    <tr><th>Version</th><td><input name="version" class="regular-text" value="<?php echo esc_attr(isset($item['version']) ? $item['version'] : '1'); ?>" /></td></tr>
                </table>
                <?php submit_button(__('Save Training', 'habaq-wp-core')); ?>
            </form>

            <?php if (!$is_new) : ?>
                <div class="habaq-training-admin__card">
                    <h2><?php esc_html_e('Shortcode', 'habaq-wp-core'); ?></h2>
                    <code>[habaq_training slug="<?php echo esc_html($slug); ?>" access="<?php echo esc_html(isset($item['access']) ? $item['access'] : 'public'); ?>" rtl="<?php echo !empty($item['rtl']) ? '1' : '0'; ?>" autoadvance="<?php echo !empty($item['autoadvance']) ? '1' : '0'; ?>" require_ack="<?php echo !empty($item['require_ack']) ? '1' : '0'; ?>" preview_slides="<?php echo isset($item['preview_slides']) ? (int) $item['preview_slides'] : 0; ?>" version="<?php echo esc_html(isset($item['version']) ? $item['version'] : '1'); ?>"]</code>
                </div>

                <div class="habaq-training-admin__card">
                    <h2><?php esc_html_e('Manage Files', 'habaq-wp-core'); ?></h2>

                    <form method="post" enctype="multipart/form-data" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
                        <?php wp_nonce_field('habaq_training_upload_json_' . $slug); ?>
                        <input type="hidden" name="action" value="habaq_training_upload_json" />
                        <input type="hidden" name="slug" value="<?php echo esc_attr($slug); ?>" />
                        <p><label>training.json <input type="file" name="training_json" accept="application/json,.json" required /></label></p>
                        <?php submit_button(__('Upload/Replace JSON', 'habaq-wp-core'), 'secondary', 'submit', false); ?>
                    </form>

                    <form method="post" enctype="multipart/form-data" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
                        <?php wp_nonce_field('habaq_training_upload_media_' . $slug); ?>
                        <input type="hidden" name="action" value="habaq_training_upload_media" />
                        <input type="hidden" name="slug" value="<?php echo esc_attr($slug); ?>" />
                        <p><label>Audio <input type="file" name="audio_files[]" multiple /></label></p>
                        <p><label>Images <input type="file" name="image_files[]" multiple /></label></p>
                        <?php submit_button(__('Upload Media', 'habaq-wp-core'), 'secondary', 'submit', false); ?>
                    </form>

                    <form method="post" enctype="multipart/form-data" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
                        <?php wp_nonce_field('habaq_training_import_zip_' . $slug); ?>
                        <input type="hidden" name="action" value="habaq_training_import_zip" />
                        <input type="hidden" name="slug" value="<?php echo esc_attr($slug); ?>" />
                        <p><label>ZIP <input type="file" name="training_zip" accept=".zip" required /></label></p>
                        <?php submit_button(__('Import ZIP', 'habaq-wp-core'), 'secondary', 'submit', false); ?>
                    </form>
                </div>

                <div class="habaq-training-admin__card">
                    <h2><?php esc_html_e('Status', 'habaq-wp-core'); ?></h2>
                    <ul>
                        <li>training.json: <?php echo !empty($diagnostics['json_exists']) ? 'yes' : 'no'; ?></li>
                        <li>slide count: <?php echo esc_html(isset($diagnostics['slide_count']) ? $diagnostics['slide_count'] : 0); ?></li>
                        <li>audio count: <?php echo esc_html(isset($diagnostics['audio_count']) ? $diagnostics['audio_count'] : 0); ?></li>
                        <li>first/last audio: <?php echo esc_html(isset($diagnostics['first_audio']) ? $diagnostics['first_audio'] : '-'); ?> / <?php echo esc_html(isset($diagnostics['last_audio']) ? $diagnostics['last_audio'] : '-'); ?></li>
                        <li>duplicates: <?php echo esc_html(isset($diagnostics['duplicates']) ? implode(' | ', $diagnostics['duplicates']) : '-'); ?></li>
                        <li>missing #1: <?php echo !empty($diagnostics['missing_one']) ? 'yes' : 'no'; ?></li>
                        <li>images count: <?php echo esc_html(isset($diagnostics['images_count']) ? $diagnostics['images_count'] : 0); ?></li>
                        <li>base folder: <code><?php echo esc_html($paths['root_dir']); ?></code></li>
                    </ul>
                </div>
            <?php endif; ?>
        </div>
        <?php
    }

    private static function get_languages() {
        if (function_exists('pll_languages_list')) {
            $langs = pll_languages_list(array('fields' => 'slug'));
            if (is_array($langs) && !empty($langs)) {
                $out = array();
                foreach ($langs as $lang) {
                    $out[$lang] = $lang;
                }

                return $out;
            }
        }

        return array('ar' => 'ar', 'en' => 'en');
    }

    private static function get_diagnostics($slug) {
        $paths = Habaq_Training_Files::get_training_paths($slug);
        $json_exists = is_file($paths['json_path']);
        $slide_count = 0;
        if ($json_exists) {
            $decoded = json_decode((string) file_get_contents($paths['json_path']), true);
            if (is_array($decoded) && isset($decoded['slides']) && is_array($decoded['slides'])) {
                $slide_count = count($decoded['slides']);
            }
        }

        $audio_discovery = Habaq_Training_Files::discover_audio($slug);
        $audio_map = $audio_discovery['audio_map'];
        $keys = array_keys($audio_map);
        sort($keys);
        $duplicates = array();
        foreach ($audio_discovery['duplicates'] as $row) {
            $duplicates[] = '#' . (int) $row['audio_index'] . ': ' . $row['ignored'];
        }

        return array(
            'json_exists' => $json_exists,
            'slide_count' => $slide_count,
            'audio_count' => count($audio_map),
            'first_audio' => !empty($keys) ? (int) $keys[0] : '-',
            'last_audio' => !empty($keys) ? (int) $keys[count($keys) - 1] : '-',
            'duplicates' => $duplicates,
            'missing_one' => !isset($audio_map[1]),
            'images_count' => Habaq_Training_Files::discover_images_count($slug),
        );
    }

    public static function handle_save() {
        self::assert_permission();
        check_admin_referer('habaq_training_save');

        $existing_slug = isset($_POST['existing_slug']) ? sanitize_title(wp_unslash($_POST['existing_slug'])) : '';
        $slug = isset($_POST['slug']) ? sanitize_title(wp_unslash($_POST['slug'])) : '';
        $title = isset($_POST['title']) ? sanitize_text_field(wp_unslash($_POST['title'])) : '';

        if ($slug === '' && $title !== '') {
            $slug = sanitize_title($title);
        }

        if ($existing_slug !== '') {
            $slug = $existing_slug;
        }

        if ($slug === '') {
            self::redirect_with_notice('error', 'invalid_slug');
        }

        $registry = Habaq_Training_Files::get_registry();
        if ($existing_slug === '' && isset($registry[$slug])) {
            self::redirect_with_notice('error', 'slug_exists');
        }

        $access = isset($_POST['access']) ? sanitize_key(wp_unslash($_POST['access'])) : 'public';
        if (!in_array($access, array('public', 'logged_in', 'roles', 'cap'), true)) {
            $access = 'public';
        }

        $roles_raw = isset($_POST['roles']) ? sanitize_text_field(wp_unslash($_POST['roles'])) : '';
        $roles = array_filter(array_map('sanitize_key', array_map('trim', explode(',', $roles_raw))));

        $lang = isset($_POST['lang']) ? sanitize_key(wp_unslash($_POST['lang'])) : 'ar';

        $registry[$slug] = array(
            'title' => $title,
            'slug' => $slug,
            'lang' => $lang,
            'access' => $access,
            'roles' => array_values($roles),
            'cap' => isset($_POST['cap']) ? sanitize_text_field(wp_unslash($_POST['cap'])) : '',
            'rtl' => isset($_POST['rtl']),
            'autoadvance' => isset($_POST['autoadvance']),
            'preview_slides' => isset($_POST['preview_slides']) ? max(0, (int) $_POST['preview_slides']) : 0,
            'require_ack' => isset($_POST['require_ack']),
            'version' => isset($_POST['version']) ? sanitize_text_field(wp_unslash($_POST['version'])) : '1',
            'updated_at' => time(),
        );

        Habaq_Training_Files::save_registry($registry);
        Habaq_Training_Files::ensure_training_dirs($slug);

        wp_safe_redirect(admin_url('admin.php?page=habaq-trainings&action=edit&slug=' . rawurlencode($slug) . '&notice=saved'));
        exit;
    }

    public static function handle_upload_json() {
        self::assert_permission();
        $slug = isset($_POST['slug']) ? sanitize_title(wp_unslash($_POST['slug'])) : '';
        check_admin_referer('habaq_training_upload_json_' . $slug);

        $paths = Habaq_Training_Files::ensure_training_dirs($slug);
        if (empty($_FILES['training_json']['tmp_name'])) {
            self::redirect_edit($slug, 'no_file');
        }

        $tmp = $_FILES['training_json']['tmp_name'];
        $contents = file_get_contents($tmp);
        $decoded = json_decode((string) $contents, true);
        if (!is_array($decoded)) {
            self::redirect_edit($slug, 'invalid_json');
        }

        file_put_contents($paths['json_path'], wp_json_encode($decoded, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
        self::redirect_edit($slug, 'json_uploaded');
    }

    public static function handle_upload_media() {
        self::assert_permission();
        $slug = isset($_POST['slug']) ? sanitize_title(wp_unslash($_POST['slug'])) : '';
        check_admin_referer('habaq_training_upload_media_' . $slug);

        $paths = Habaq_Training_Files::ensure_training_dirs($slug);

        self::process_multi_upload('audio_files', $paths['audio_dir'], array('Habaq_Training_Files', 'is_allowed_audio_file'));
        self::process_multi_upload('image_files', $paths['images_dir'], array('Habaq_Training_Files', 'is_allowed_image_file'));

        self::redirect_edit($slug, 'media_uploaded');
    }

    private static function process_multi_upload($field, $dest_dir, $validator) {
        if (empty($_FILES[$field]) || empty($_FILES[$field]['name']) || !is_array($_FILES[$field]['name'])) {
            return;
        }

        foreach ($_FILES[$field]['name'] as $index => $name) {
            if (!isset($_FILES[$field]['tmp_name'][$index]) || $_FILES[$field]['tmp_name'][$index] === '') {
                continue;
            }

            if ((int) $_FILES[$field]['error'][$index] !== UPLOAD_ERR_OK) {
                continue;
            }

            $tmp = $_FILES[$field]['tmp_name'][$index];
            $filename = sanitize_file_name((string) $name);
            if ($filename === '' || !call_user_func($validator, $tmp, $filename)) {
                continue;
            }

            move_uploaded_file($tmp, trailingslashit($dest_dir) . $filename);
        }
    }

    public static function handle_import_zip() {
        self::assert_permission();
        $slug = isset($_POST['slug']) ? sanitize_title(wp_unslash($_POST['slug'])) : '';
        check_admin_referer('habaq_training_import_zip_' . $slug);

        if (empty($_FILES['training_zip']['tmp_name'])) {
            self::redirect_edit($slug, 'zip_missing');
        }

        $name = isset($_FILES['training_zip']['name']) ? (string) $_FILES['training_zip']['name'] : '';
        if (strtolower((string) pathinfo($name, PATHINFO_EXTENSION)) !== 'zip') {
            self::redirect_edit($slug, 'zip_invalid');
        }

        $paths = Habaq_Training_Files::ensure_training_dirs($slug);
        $zip = new ZipArchive();
        if ($zip->open($_FILES['training_zip']['tmp_name']) !== true) {
            self::redirect_edit($slug, 'zip_open_failed');
        }

        for ($i = 0; $i < $zip->numFiles; $i++) {
            $entry = (string) $zip->getNameIndex($i);
            if ($entry === '' || substr($entry, -1) === '/') {
                continue;
            }

            $normalized = str_replace('\\', '/', $entry);
            if (strpos($normalized, '../') !== false || strpos($normalized, '..\\') !== false || preg_match('#^([a-zA-Z]:)?/#', $normalized)) {
                continue;
            }

            $basename = sanitize_file_name(basename($normalized));
            if ($basename === '') {
                continue;
            }

            $stream = $zip->getStream($entry);
            if (!$stream) {
                continue;
            }

            $tmp = wp_tempnam($basename);
            $out = fopen($tmp, 'wb');
            while (!feof($stream)) {
                fwrite($out, fread($stream, 8192));
            }
            fclose($out);
            fclose($stream);

            $target = '';
            if ($basename === 'training.json') {
                $decoded = json_decode((string) file_get_contents($tmp), true);
                if (is_array($decoded)) {
                    file_put_contents($paths['json_path'], wp_json_encode($decoded, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
                }
                @unlink($tmp);
                continue;
            }

            if (Habaq_Training_Files::is_allowed_audio_file($tmp, $basename)) {
                $target = trailingslashit($paths['audio_dir']) . $basename;
            } elseif (Habaq_Training_Files::is_allowed_image_file($tmp, $basename)) {
                $target = trailingslashit($paths['images_dir']) . $basename;
            } else {
                $ext = strtolower((string) pathinfo($basename, PATHINFO_EXTENSION));
                if (in_array($ext, array('vtt', 'srt', 'txt'), true)) {
                    $target = trailingslashit($paths['captions_dir']) . $basename;
                }
            }

            if ($target !== '') {
                copy($tmp, $target);
            }
            @unlink($tmp);
        }

        $zip->close();
        self::redirect_edit($slug, 'zip_imported');
    }

    public static function handle_delete() {
        self::assert_permission();
        $slug = isset($_POST['slug']) ? sanitize_title(wp_unslash($_POST['slug'])) : '';
        check_admin_referer('habaq_training_delete_' . $slug);

        $registry = Habaq_Training_Files::get_registry();
        if (isset($registry[$slug])) {
            unset($registry[$slug]);
            Habaq_Training_Files::save_registry($registry);
        }

        wp_safe_redirect(admin_url('admin.php?page=habaq-trainings&notice=deleted'));
        exit;
    }

    private static function render_notice() {
        if (empty($_GET['notice'])) {
            return;
        }

        $notice = sanitize_text_field(wp_unslash($_GET['notice']));
        echo '<div class="notice notice-success"><p>' . esc_html($notice) . '</p></div>';
    }

    private static function assert_permission() {
        if (!current_user_can('manage_options')) {
            wp_die(esc_html__('غير مصرح.', 'habaq-wp-core'));
        }
    }

    private static function redirect_with_notice($type, $notice) {
        $query = array(
            'page' => 'habaq-trainings',
            'notice' => $notice,
        );

        wp_safe_redirect(add_query_arg($query, admin_url('admin.php')));
        exit;
    }

    private static function redirect_edit($slug, $notice) {
        wp_safe_redirect(admin_url('admin.php?page=habaq-trainings&action=edit&slug=' . rawurlencode($slug) . '&notice=' . rawurlencode($notice)));
        exit;
    }
}
