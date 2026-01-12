<?php

if (!defined('ABSPATH')) {
    exit;
}

class Habaq_WP_Core_Deactivator {
    /**
     * Deactivation hook placeholder.
     *
     * @return void
     */
    public static function deactivate() {
        flush_rewrite_rules();
    }
}
