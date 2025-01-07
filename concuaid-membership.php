<?php
/**
 * Plugin Name: ConcuAid Membership
 * Description: A simple plugin for adding clients, associating account keys, and adding people under each client.
 * Version: 1.0
 * Author: Your Name
 * Text Domain: concuaid-membership
 */

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

// Define constants
define( 'CMP_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'CMP_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

/**
 * Main Plugin Class
 */
class ConcuAidMembership {

    // Constructor - Initialize the plugin
    public function __construct() {
        $this->load_dependencies();
        $this->initialize_hooks();
    }

    // Load required dependencies
    private function load_dependencies() {
        require_once CMP_PLUGIN_DIR . 'includes/class-database.php';
        require_once CMP_PLUGIN_DIR . 'includes/class-admin.php';
        require_once CMP_PLUGIN_DIR . 'includes/class-form-handler.php';
    }

    // Initialize the plugin hooks
    private function initialize_hooks() {
        register_activation_hook( __FILE__, array( 'CMP_Database', 'create_tables' ) );
        add_action( 'admin_menu', array( 'CMP_Admin', 'add_admin_menu' ) );
        add_action( 'admin_enqueue_scripts', array( 'CMP_Admin', 'enqueue_scripts' ) );
    }
}

// Initialize the plugin
new ConcuAidMembership();