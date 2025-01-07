<?php

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * CMP_Form_Handler Class
 * 
 * Handles all form submissions for the plugin, such as adding clients and people.
 */
class CMP_Form_Handler {

    /**
     * Handle the form submission for adding a client
     */
    public static function handle_add_client_form() {
        // Check if the form is submitted
        if ( isset( $_POST['cmp_add_client'] ) && check_admin_referer( 'cmp_add_client_action', 'cmp_add_client_nonce' ) ) {
            // Sanitize and validate form fields
            $client_name = sanitize_text_field( $_POST['cmp_client_name'] );
            $client_email = sanitize_email( $_POST['cmp_client_email'] );
    
            // Check if the fields are not empty
            if ( empty( $client_name ) || empty( $client_email ) ) {
                echo '<div class="error"><p>Please fill in all the fields.</p></div>';
                return;
            }
    
            // Check if the email is valid
            if ( ! is_email( $client_email ) ) {
                echo '<div class="error"><p>Invalid email address.</p></div>';
                return;
            }
    
            // Encrypt the account key before storing it
            $encrypted_account_key = self::encrypt_account_key();
    
            if ( !$encrypted_account_key ) {
                echo '<div class="error"><p>There was an error encrypting the account key. Please try again.</p></div>';
                return;
            }
    
            // Insert the client into the database with the encrypted account key
            $client_id = CMP_Database::insert_client( $client_name, $client_email, $encrypted_account_key );
    
            if ( $client_id ) {
                echo '<div class="updated"><p>Client added successfully!</p></div>';
            } else {
                echo '<div class="error"><p>There was an error adding the client. Please try again later.</p></div>';
            }
        }
    
        // Display the form for adding a client
        self::render_add_client_form();

         // Display the table of all clients
         self::render_clients_table();
    }
    
    /**
     * Encrypt the account key using AES-256 encryption.
     *
     * @param string $account_key The account key to encrypt.
     * @return string|false The encrypted account key, or false on failure.
     */
    public static function encrypt_account_key() {
        // Define your secret key and IV (Initialization Vector)
        // In a real-world scenario, these should be stored securely
        
        $random_string = self::generate_random_string();

        if ( defined('SECRET_KEY') ) {
            $secret_key = SECRET_KEY;  // Retrieve from wp-config.php
        } elseif ( getenv('SECRET_KEY') ) {
            $secret_key = getenv('SECRET_KEY');  // Retrieve from environment variable
        } else {
            // Fallback to a hardcoded default (not recommended for production)
            $secret_key = 'Commotio-Cerebri';  // Ideally, this should be avoided in production
        }
        $iv = '1234567890123456'; // 16 bytes for AES-256-CBC
    
        // Encrypt the account key using openssl_encrypt
        $encrypted_key = openssl_encrypt( $random_string, 'aes-256-cbc', $secret_key, 0, $iv );
    
        return $encrypted_key;
    }
    
    public static function generate_random_string() {
        // Get current timestamp in microseconds (to get more uniqueness)
        $timestamp = microtime(true);
    
        // Convert timestamp to a string and remove the decimal point
        $timestamp_str = str_replace('.', '', (string)$timestamp);
    
        // Append a random number to further increase randomness
        $random_number = rand(1000, 9999);  // Generate a random 4-digit number
    
        // Concatenate timestamp with random number and take the first 16 characters
        $random_string = substr($timestamp_str . $random_number, 0, 16);
    
        return $random_string;
    }

    /**
     * Render the form to add a new client
     */
    public static function render_add_client_form() {
        ?>
        <div class="wrap">
            <h1>Add New Client</h1>
            <form method="POST" action="">
                <table class="form-table">
                    <tr>
                        <th><label for="cmp_client_name">Client Name</label></th>
                        <td><input type="text" name="cmp_client_name" id="cmp_client_name" required /></td>
                    </tr>
                    <tr>
                        <th><label for="cmp_client_email">Client Email</label></th>
                        <td><input type="email" name="cmp_client_email" id="cmp_client_email" required /></td>
                    </tr>
                    <tr>
                        <th><label for="cmp_account_key">Account Key</label></th>
                        <td><input type="text" name="cmp_account_key" id="cmp_account_key" disabled /></td>
                    </tr>
                </table>
                <?php wp_nonce_field( 'cmp_add_client_action', 'cmp_add_client_nonce' ); ?>
                <input type="submit" name="cmp_add_client" value="Add Client" class="button-primary" />
            </form>
        </div>
        <?php
    }

    /**
     * Render the table of all clients
     */
    public static function render_clients_table() {
        // Fetch all clients from the database
        global $wpdb;
        $clients_table = $wpdb->prefix . 'cmp_clients';
        $results = $wpdb->get_results( "SELECT * FROM $clients_table" );

        if ( !empty($results) ) {
            echo '<table class="table-responsive">';
            echo '<thead><tr><th>Client Name</th><th>Email</th><th>Actions</th></tr></thead>';
            echo '<tbody>';

            foreach ( $results as $client ) {
                echo '<tr>';
                echo '<td>' . esc_html( $client->name ) . '</td>';
                echo '<td>' . esc_html( $client->email ) . '</td>';
                echo '<td><a href="' . admin_url( 'admin.php?page=cmp_update_client&client_id=' . $client->id ) . '">Update</a></td>';
                echo '</tr>';
            }

            echo '</tbody>';
            echo '</table>';
        } else {
            echo '<p>No clients found.</p>';
        }
    }

    /**
     * Handle the form submission for adding a person under a client
     */
    public static function handle_manage_people_form() {
        // Check if the form is submitted
        if ( isset( $_POST['cmp_add_person'] ) && check_admin_referer( 'cmp_add_person_action', 'cmp_add_person_nonce' ) ) {
            // Sanitize and validate form fields
            $person_name = sanitize_text_field( $_POST['cmp_person_name'] );
            $person_email = sanitize_email( $_POST['cmp_person_email'] );
            $client_id = intval( $_POST['cmp_client'] );

            // Check if the fields are not empty
            if ( empty( $person_name ) || empty( $person_email ) || empty( $client_id ) ) {
                echo '<div class="error"><p>Please fill in all the fields.</p></div>';
                return;
            }

            // Check if the email is valid
            if ( ! is_email( $person_email ) ) {
                echo '<div class="error"><p>Invalid email address.</p></div>';
                return;
            }

            // Insert the person into the database
            $person_id = CMP_Database::insert_person( $person_name, $person_email, $client_id );

            if ( $person_id ) {
                echo '<div class="updated"><p>Person added successfully!</p></div>';
            } else {
                echo '<div class="error"><p>There was an error adding the person. Please try again later.</p></div>';
            }
        }

        // Display the form for managing people
        self::render_manage_people_form();
    }

    /**
     * Render the form to manage people under a client
     */
    public static function render_manage_people_form() {
        global $wpdb;

        // Get all clients to display in the dropdown
        $clients = CMP_Database::get_all_clients();

        ?>
        <div class="wrap">
            <h1>Manage People</h1>
            <form method="POST" action="">
                <table class="form-table">
                    <tr>
                        <th><label for="cmp_client">Select Client</label></th>
                        <td>
                            <select name="cmp_client" id="cmp_client" required>
                                <option value="">Select Client</option>
                                <?php
                                foreach ( $clients as $client ) {
                                    echo '<option value="' . $client->id . '">' . esc_html( $client->name ) . '</option>';
                                }
                                ?>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <th><label for="cmp_person_name">Person's Name</label></th>
                        <td><input type="text" name="cmp_person_name" id="cmp_person_name" required /></td>
                    </tr>
                    <tr>
                        <th><label for="cmp_person_email">Person's Email</label></th>
                        <td><input type="email" name="cmp_person_email" id="cmp_person_email" required /></td>
                    </tr>
                </table>
                <?php wp_nonce_field( 'cmp_add_person_action', 'cmp_add_person_nonce' ); ?>
                <input type="submit" name="cmp_add_person" value="Add Person" class="button-primary" />
            </form>
        </div>
        <?php
    }
}