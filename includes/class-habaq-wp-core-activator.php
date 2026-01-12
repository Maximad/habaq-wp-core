<?php

if (!defined('ABSPATH')) {
    exit;
}

class Habaq_WP_Core_Activator {
    /**
     * Activation hook.
     *
     * @return void
     */
    public static function activate() {
        Habaq_WP_Core_CPTs::register();
        Habaq_WP_Core_Job_Applications::register_cpt();
        flush_rewrite_rules();
    }
}
