<?php
/**
 * Plugin Name: Habaq Engine
 * Description: Core plugin scaffold for Habaq.
 * Version: 0.2.1
 * Author: Habaq
 * License: GPL-2.0-or-later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: habaq-wp-core
 */

if (!defined('ABSPATH')) {
    exit;
}

define('HABAQ_WP_CORE_VERSION', '0.2.1');
define('HABAQ_WP_CORE_FILE', __FILE__);
define('HABAQ_WP_CORE_DIR', __DIR__);
define('HABAQ_WP_CORE_URL', plugin_dir_url(__FILE__));
if (!defined('HABAQ_APPLY_TO')) {
    define('HABAQ_APPLY_TO', get_option('admin_email'));
}
if (!defined('HABAQ_WP_CORE_DEFAULT_CV_MAX_MB')) {
    define('HABAQ_WP_CORE_DEFAULT_CV_MAX_MB', 5);
}

require_once HABAQ_WP_CORE_DIR . '/includes/class-habaq-wp-core-loader.php';
require_once HABAQ_WP_CORE_DIR . '/includes/class-habaq-wp-core-activator.php';
require_once HABAQ_WP_CORE_DIR . '/includes/class-habaq-wp-core-deactivator.php';
require_once HABAQ_WP_CORE_DIR . '/includes/class-habaq-wp-core-i18n.php';
require_once HABAQ_WP_CORE_DIR . '/includes/class-habaq-wp-core-access.php';
require_once HABAQ_WP_CORE_DIR . '/includes/class-habaq-wp-core-cpts.php';
require_once HABAQ_WP_CORE_DIR . '/includes/class-habaq-wp-core-helpers.php';
require_once HABAQ_WP_CORE_DIR . '/includes/class-habaq-wp-core-job-admin.php';
require_once HABAQ_WP_CORE_DIR . '/includes/class-habaq-wp-core-job-applications.php';
require_once HABAQ_WP_CORE_DIR . '/includes/class-habaq-wp-core-job-filters.php';
require_once HABAQ_WP_CORE_DIR . '/includes/class-habaq-wp-core-job-meta.php';
require_once HABAQ_WP_CORE_DIR . '/includes/class-habaq-wp-core-settings.php';
require_once HABAQ_WP_CORE_DIR . '/includes/class-habaq-wp-core-roles.php';
require_once HABAQ_WP_CORE_DIR . '/includes/class-habaq-wp-core-shortcodes.php';
require_once HABAQ_WP_CORE_DIR . '/includes/class-habaq-wp-core.php';

if (!function_exists('habaq_wp_core')) {
    /**
     * Get the core plugin instance.
     *
     * @return Habaq_WP_Core
     */
    function habaq_wp_core() {
        static $plugin;

        if (!$plugin) {
            $plugin = new Habaq_WP_Core();
            $plugin->run();
        }

        return $plugin;
    }
}

habaq_wp_core();
