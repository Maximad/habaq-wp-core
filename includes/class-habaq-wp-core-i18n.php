<?php

if (!defined('ABSPATH')) {
    exit;
}

class Habaq_WP_Core_I18n {
    /**
     * Load plugin textdomain.
     *
     * @return void
     */
    public function load_textdomain() {
        load_plugin_textdomain(
            'habaq-wp-core',
            false,
            dirname(plugin_basename(HABAQ_WP_CORE_FILE)) . '/languages/'
        );
    }
}
