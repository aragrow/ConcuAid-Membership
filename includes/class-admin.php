<?php

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * CMP_Admin Class
 * 
 * Handles the WordPress admin pages and form processing for the plugin.
 */
class CMP_Admin {

    // Hook to enqueue scripts and styles
    public static function enqueue_admin_assets() {
        // Ensure we're in the admin area
        if ( is_admin() ) {
            // Enqueue admin styles
            wp_enqueue_style(
                'cmp-admin-style', // Handle
                plugin_dir_url( __FILE__ ) . 'assets/css/admin.css', // Path to CSS file
                array(), // Dependencies (empty array means no dependencies)
                '1.0.0' // Version number
            );

            // Enqueue admin scripts
            wp_enqueue_script(
                'cmp-admin-script', // Handle
                plugin_dir_url( __FILE__ ) . 'assets/js/admin.js', // Path to JS file
                array('jquery'), // Dependencies (make sure jQuery is loaded first)
                '1.0.0', // Version number
                true // Load in the footer
            );

        }
    }

    /**
     * Add the plugin menu pages to the WordPress admin
     */
    public static function add_admin_menu() {
        // Add main menu page for the plugin
        add_menu_page(
            'ConcuAid Membership', // Page title
            'ConcuAid Membership', // Menu title
            'manage_options',      // Required capability
            'cmp_dashboard',       // Menu slug
            array( 'CMP_Admin', 'dashboard_page' ), // Callback function
            'dashicons-groups',    // Icon
            20                     // Position in the menu
        );

        // Add submenu page for adding a new client
        add_submenu_page(
            'cmp_dashboard',        // Parent slug
            'Add Client',           // Page title
            'Add Client',           // Menu title
            'manage_options',       // Required capability
            'cmp_add_client',       // Menu slug
            array( 'CMP_Admin', 'add_client_page' ) // Callback function
        );

        // Add submenu page for managing people
        add_submenu_page(
            'cmp_dashboard',        // Parent slug
            'Manage People',        // Page title
            'Manage People',        // Menu title
            'manage_options',       // Required capability
            'cmp_manage_people',    // Menu slug
            array( 'CMP_Admin', 'manage_people_page' ) // Callback function
        );
    }

    /**
     * Display the plugin dashboard page
     */
    public static function dashboard_page() {
        echo '<div class="wrap"><h1>ConcuAid Membership Dashboard</h1>';
        echo '<p>Welcome to ConcuAid Membership. From here, you can manage your clients and the people associated with them.</p>';
        echo '</div>';
    }

    /**
     * Display the Add Client page and handle the form submission
     */
    public static function add_client_page() {
        // Handle form submission for adding a client
        CMP_Form_Handler::handle_add_client_form();
    }

    /**
     * Display the Manage People page and handle the form submission
     */
    public static function manage_people_page() {
        // Handle form submission for managing people
        CMP_Form_Handler::handle_manage_people_form();
    }

    /**
     * Enqueue admin scripts and styles
     */
    public static function enqueue_scripts() {
        wp_enqueue_script( 'cmp-admin-js', CMP_PLUGIN_URL . 'admin.js', array('jquery'), '1.0', true );
        wp_enqueue_style( 'cmp-admin-css', CMP_PLUGIN_URL . 'admin.css' );
    }
}

