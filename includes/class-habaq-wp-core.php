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
        // Placeholder for admin hooks.
    }

    /**
     * Register public hooks.
     *
     * @return void
     */
    private function define_public_hooks() {
        // Placeholder for public hooks.
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
