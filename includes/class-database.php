<?php

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * CMP_Database Class
 * 
 * Handles the creation of necessary tables and other database-related functions.
 */
class CMP_Database {

    /**
     * Create the necessary tables for the plugin
     */
    public static function create_tables() {
        global $wpdb;
    
        // Define table names
        $clients_table = $wpdb->prefix . 'cmp_clients';
        $people_table = $wpdb->prefix . 'cmp_people';
    
        // Define character set and collation
        $charset_collate = $wpdb->get_charset_collate();
    
        // SQL query to create the 'cmp_clients' table
        $sql_clients = "CREATE TABLE $clients_table (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            name varchar(255) NOT NULL,
            email varchar(255) NOT NULL,
            account_key varchar(255) NOT NULL,
            PRIMARY KEY (id)
        ) $charset_collate;";
    
        // SQL query to create the 'cmp_people' table
        $sql_people = "CREATE TABLE $people_table (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            client_id mediumint(9) NOT NULL,
            name varchar(255) NOT NULL,
            email varchar(255) NOT NULL,
            PRIMARY KEY (id),
            FOREIGN KEY (client_id) REFERENCES $clients_table(id) ON DELETE CASCADE
        ) $charset_collate;";
    
        // Include the WordPress upgrade function
        require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
    
        // Create the tables
        dbDelta( $sql_clients );
        dbDelta( $sql_people );
    
        // Log the success or failure of the table creation
        if ( ! empty( $wpdb->last_error ) ) {
            error_log( 'CMP Database Error: ' . $wpdb->last_error );
        } else {
            error_log( 'CMP Database Tables Created Successfully' );
        }
    }
    

    /**
     * Insert a new client into the database
     *
     * @param string $name Client name
     * @param string $email Client email
     * @param string $account_key Unique account key for the client
     * 
     * @return bool|int The ID of the inserted client or false if insertion fails
     */
    public static function insert_client( $name, $email, $account_key ) {
        global $wpdb;

        // Table name for clients
        $table_name = $wpdb->prefix . 'cmp_clients';

        // Validate email format
        if ( ! is_email( $email ) ) {
            return false;
        }

        // Insert the new client
        $inserted = $wpdb->insert(
            $table_name,
            [
                'name' => sanitize_text_field( $name ),
                'email' => sanitize_email( $email ),
                'account_key' => sanitize_text_field( $account_key ),
            ]
        );

        // Check if the insertion was successful
        if ( $inserted === false ) {
            error_log( 'CMP Database Error: Could not insert client. ' . $wpdb->last_error );
            return false;
        }

        return $wpdb->insert_id; // Return the client ID
    }

    /**
     * Insert a person under a specific client
     *
     * @param string $name Person's name
     * @param string $email Person's email
     * @param int $client_id ID of the associated client
     * 
     * @return bool|int The ID of the inserted person or false if insertion fails
     */
    public static function insert_person( $name, $email, $client_id ) {
        global $wpdb;

        // Table name for people
        $table_name = $wpdb->prefix . 'cmp_people';

        // Validate email format
        if ( ! is_email( $email ) ) {
            return false;
        }

        // Ensure that the client exists before inserting a person
        $client_exists = $wpdb->get_var( $wpdb->prepare(
            "SELECT COUNT(*) FROM {$wpdb->prefix}cmp_clients WHERE id = %d",
            $client_id
        ));

        if ( !$client_exists ) {
            return false; // No such client exists
        }

        // Insert the new person under the specified client
        $inserted = $wpdb->insert(
            $table_name,
            [
                'name' => sanitize_text_field( $name ),
                'email' => sanitize_email( $email ),
                'client_id' => (int) $client_id,
            ]
        );

        // Check if the insertion was successful
        if ( $inserted === false ) {
            error_log( 'CMP Database Error: Could not insert person. ' . $wpdb->last_error );
            return false;
        }

        return $wpdb->insert_id; // Return the person ID
    }

    /**
     * Retrieve all clients from the database
     * 
     * @return array|false Array of clients or false if no clients are found
     */
    public static function get_all_clients() {
        global $wpdb;

        $table_name = $wpdb->prefix . 'cmp_clients';

        // Get all clients
        $results = $wpdb->get_results( "SELECT id, name, email, account_key FROM $table_name" );

        if ( !empty($wpdb->last_error) ) {
            error_log( 'CMP Database Error: ' . $wpdb->last_error );
            return false;
        }

        return $results;
    }

    /**
     * Retrieve all people under a specific client
     *
     * @param int $client_id ID of the client
     * 
     * @return array|false Array of people under the client or false if no people are found
     */
    public static function get_people_by_client( $client_id ) {
        global $wpdb;

        $table_name = $wpdb->prefix . 'cmp_people';

        // Get all people for a specific client
        $results = $wpdb->get_results( $wpdb->prepare(
            "SELECT id, name, email FROM $table_name WHERE client_id = %d",
            $client_id
        ));

        if ( !empty($wpdb->last_error) ) {
            error_log( 'CMP Database Error: ' . $wpdb->last_error );
            return false;
        }

        return $results;
    }

    /**
     * Retrieve a client by account key
     *
     * @param string $account_key The account key of the client
     * 
     * @return object|false Client data or false if not found
     */
    public static function get_client_by_key( $account_key ) {
        global $wpdb;

        $table_name = $wpdb->prefix . 'cmp_clients';

        // Get the client by account key
        $result = $wpdb->get_row( $wpdb->prepare(
            "SELECT id, name, email, account_key FROM $table_name WHERE account_key = %s",
            $account_key
        ));

        if ( !empty($wpdb->last_error) ) {
            error_log( 'CMP Database Error: ' . $wpdb->last_error );
            return false;
        }

        return $result;
    }
}