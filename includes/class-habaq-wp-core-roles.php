<?php

if (!defined('ABSPATH')) {
    exit;
}

class Habaq_WP_Core_Roles {
    /**
     * Register roles and capabilities.
     *
     * @return void
     */
    public static function register() {
        $roles = array(
            'habaq_insider' => 'Habaq Insider',
            'habaq_core' => 'Habaq Core Ops',
            'habaq_restricted' => 'Habaq Restricted',
            'habaq_executive' => 'Habaq Executive',
        );

        foreach ($roles as $key => $name) {
            if (!get_role($key)) {
                add_role($key, $name, array('read' => true));
            }
        }

        $insider = get_role('habaq_insider');
        $core = get_role('habaq_core');
        $restricted = get_role('habaq_restricted');
        $executive = get_role('habaq_executive');

        if ($insider) {
            $insider->add_cap('habaq_insider_access');
        }

        if ($core) {
            $core->add_cap('habaq_insider_access');
            $core->add_cap('habaq_core_access');
        }

        if ($restricted) {
            $restricted->add_cap('habaq_insider_access');
            $restricted->add_cap('habaq_core_access');
            $restricted->add_cap('habaq_restricted_access');
        }

        if ($executive) {
            $executive->add_cap('habaq_insider_access');
            $executive->add_cap('habaq_core_access');
            $executive->add_cap('habaq_restricted_access');
            $executive->add_cap('habaq_executive_access');
        }
    }
}
