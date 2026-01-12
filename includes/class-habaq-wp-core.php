<?php

if (!defined('ABSPATH')) {
    exit;
}

class Habaq_WP_Core {
    /**
     * Loader instance.
     *
     * @var Habaq_WP_Core_Loader
     */
    private $loader;

    /**
     * Initialize the plugin.
     */
    public function __construct() {
        $this->loader = new Habaq_WP_Core_Loader();

        $this->set_locale();
        $this->define_admin_hooks();
        $this->define_public_hooks();
        $this->register_lifecycle_hooks();
    }

    /**
     * Register the textdomain loader.
     *
     * @return void
     */
    private function set_locale() {
        $i18n = new Habaq_WP_Core_I18n();
        $this->loader->add_action('plugins_loaded', $i18n, 'load_textdomain');
    }

    /**
     * Register admin hooks.
     *
     * @return void
     */
    private function define_admin_hooks() {
        $this->loader->add_action('init', 'Habaq_WP_Core_Roles', 'register');
        $this->loader->add_action('add_meta_boxes', 'Habaq_WP_Core_Job_Admin', 'register_metaboxes');
        $this->loader->add_action('manage_job_application_posts_custom_column', 'Habaq_WP_Core_Job_Admin', 'render_columns', 10, 2);
        $this->loader->add_filter('manage_job_application_posts_columns', 'Habaq_WP_Core_Job_Admin', 'add_columns');
    }

    /**
     * Register public hooks.
     *
     * @return void
     */
    private function define_public_hooks() {
        $this->loader->add_action('init', 'Habaq_WP_Core_CPTs', 'register');
        $this->loader->add_action('init', 'Habaq_WP_Core_Shortcodes', 'register');
        $this->loader->add_action('init', 'Habaq_WP_Core_Job_Applications', 'register_cpt');
        $this->loader->add_action('init', 'Habaq_WP_Core_Job_Applications', 'register_shortcodes');
        $this->loader->add_action('init', 'Habaq_WP_Core_Job_Filters', 'register_shortcodes');
        $this->loader->add_action('pre_get_posts', 'Habaq_WP_Core_Job_Filters', 'filter_job_archive');
        $this->loader->add_action('template_redirect', 'Habaq_WP_Core_Job_Applications', 'handle_submission');
        $this->loader->add_action('wp_footer', 'Habaq_WP_Core_Job_Applications', 'render_notice');
        $this->loader->add_action('template_redirect', 'Habaq_WP_Core_Access', 'enforce_zones');
    }

    /**
     * Register activation/deactivation hooks.
     *
     * @return void
     */
    private function register_lifecycle_hooks() {
        register_activation_hook(HABAQ_WP_CORE_FILE, array('Habaq_WP_Core_Activator', 'activate'));
        register_deactivation_hook(HABAQ_WP_CORE_FILE, array('Habaq_WP_Core_Deactivator', 'deactivate'));
    }

    /**
     * Run the loader.
     *
     * @return void
     */
    public function run() {
        $this->loader->run();
    }
}
