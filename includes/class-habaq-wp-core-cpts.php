<?php

if (!defined('ABSPATH')) {
    exit;
}

class Habaq_WP_Core_CPTs {
    /**
     * Register CPTs and taxonomies.
     *
     * @return void
     */
    public static function register() {
        if (!post_type_exists('project')) {
            register_post_type('project', array(
                'labels' => array(
                    'name' => 'Projects',
                    'singular_name' => 'Project',
                ),
                'public' => true,
                'has_archive' => true,
                'rewrite' => array('slug' => 'projects'),
                'menu_icon' => 'dashicons-portfolio',
                'supports' => array('title', 'editor', 'excerpt', 'thumbnail', 'revisions'),
                'show_in_rest' => true,
            ));
        }

        if (!post_type_exists('job')) {
            register_post_type('job', array(
                'labels' => array(
                    'name' => 'Jobs',
                    'singular_name' => 'Job',
                ),
                'public' => true,
                'has_archive' => true,
                'rewrite' => array('slug' => 'jobs'),
                'menu_icon' => 'dashicons-id',
                'supports' => array('title', 'editor', 'excerpt', 'thumbnail', 'revisions'),
                'show_in_rest' => true,
            ));
        }

        $taxonomies = array(
            'project_unit' => array('project', 'Project Unit', 'project-unit', true),
            'project_status' => array('project', 'Project Status', 'project-status', true),
            'project_type' => array('project', 'Project Type', 'project-type', true),
            'job_type' => array('job', 'Job Type', 'job-type', true),
            'job_location' => array('job', 'Location', 'job-location', true),
            'job_unit' => array('job', 'Unit', 'job-unit', true),
            'job_level' => array('job', 'Level', 'job-level', true),
        );

        foreach ($taxonomies as $taxonomy => $config) {
            if (taxonomy_exists($taxonomy)) {
                continue;
            }

            list($post_type, $label, $slug, $hierarchical) = $config;

            register_taxonomy($taxonomy, array($post_type), array(
                'labels' => array(
                    'name' => $label,
                    'singular_name' => $label,
                ),
                'public' => true,
                'hierarchical' => (bool) $hierarchical,
                'rewrite' => array('slug' => $slug),
                'show_in_rest' => true,
            ));
        }
    }
}
