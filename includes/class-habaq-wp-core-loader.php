<?php

if (!defined('ABSPATH')) {
    exit;
}

class Habaq_WP_Core_Loader {
    /**
     * Registered actions.
     *
     * @var array
     */
    protected $actions = array();

    /**
     * Register an action with WordPress.
     *
     * @param string $hook Hook name.
     * @param object $component Component instance.
     * @param string $callback Callback method.
     * @param int    $priority Priority.
     * @param int    $accepted_args Accepted args.
     * @return void
     */
    public function add_action($hook, $component, $callback, $priority = 10, $accepted_args = 1) {
        $this->actions[] = array(
            'hook' => $hook,
            'component' => $component,
            'callback' => $callback,
            'priority' => $priority,
            'accepted_args' => $accepted_args,
        );
    }

    /**
     * Run the loader to register all actions.
     *
     * @return void
     */
    public function run() {
        foreach ($this->actions as $hook) {
            add_action(
                $hook['hook'],
                array($hook['component'], $hook['callback']),
                $hook['priority'],
                $hook['accepted_args']
            );
        }
    }
}
