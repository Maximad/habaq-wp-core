<?php

if (!defined('ABSPATH')) {
    exit;
}

class Habaq_WP_Core_Activator {
    /**
     * Activation hook placeholder.
     *
     * @return void
     */
    public static function activate() {
        Habaq_WP_Core_CPTs::register();
        flush_rewrite_rules();
    }
}
