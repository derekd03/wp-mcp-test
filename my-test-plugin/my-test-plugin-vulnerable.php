<?php
/**
 * Plugin Name: Antigravity MCP and Skill Test Plugin (Vulnerable)
 * Description: A test plugin containing intentional security and architecture errors to verify skills and MCP tools.
 * Version: 1.0.0
 * Author: Antigravity Developer
 * Text Domain: my-test-plugin
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * =========================================================================
 * SKILL TEST CASE: Insecure Database Query & Missing Nonce
 * =========================================================================
 */
function my_plugin_delete_log() {
    global $wpdb;

    if ( isset( $_GET['log_id'] ) ) {
        $log_id = $_GET['log_id'];
        
        // VULNERABILITY 1: Direct SQL concatenation (SQL Injection)
        $wpdb->query( "DELETE FROM {$wpdb->prefix}plugin_logs WHERE id = " . $log_id );
        
        // VULNERABILITY 2: Outputting input data directly without escaping (Reflected XSS)
        echo "Deleted log item: " . $_GET['log_id'];
    }
}

/**
 * =========================================================================
 * SKILL TEST CASE: Hook Timing / Headers Already Sent
 * =========================================================================
 */
add_action( 'wp_footer', 'my_plugin_handle_login_redirect' );
function my_plugin_handle_login_redirect() {
    if ( is_user_logged_in() && isset( $_GET['action'] ) && 'go_dashboard' === $_GET['action'] ) {
        // ERROR: Redirecting in wp_footer will trigger "headers already sent" warning.
        wp_redirect( admin_url() );
        exit;
    }
}
