<?php
/**
 * Plugin Name: Antigravity MCP and Skill Test Plugin (Corrected)
 * Description: A test plugin with applied security fixes and optimized hook timings.
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
 * SKILL TEST CASE: Insecure Database Query & Missing Nonce (CORRECTED)
 * =========================================================================
 */
function my_plugin_delete_log() {
    global $wpdb;

    if ( isset( $_GET['log_id'] ) ) {
        
        // 1. Verify CSRF Nonce
        if ( ! isset( $_GET['my_plugin_nonce'] ) || ! wp_verify_nonce( sanitize_key( $_GET['my_plugin_nonce'] ), 'my_plugin_delete_log_action' ) ) {
            wp_die( esc_html__( 'Security check failed.', 'my-test-plugin' ) );
        }

        // 2. Verify User Permissions
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_die( esc_html__( 'You do not have permission to perform this action.', 'my-test-plugin' ) );
        }

        // 3. Sanitize Input
        $log_id = absint( $_GET['log_id'] );
        
        // 4. Secure Database Query
        $wpdb->query(
            $wpdb->prepare(
                "DELETE FROM {$wpdb->prefix}plugin_logs WHERE id = %d",
                $log_id
            )
        );
        
        // 5. Escape Output
        echo esc_html( sprintf( __( 'Deleted log item: %d', 'my-test-plugin' ), $log_id ) );
    }
}

/**
 * =========================================================================
 * SKILL TEST CASE: Hook Timing / Headers Already Sent (CORRECTED)
 * =========================================================================
 */
add_action( 'template_redirect', 'my_plugin_handle_login_redirect' );
function my_plugin_handle_login_redirect() {
    if ( is_user_logged_in() && isset( $_GET['action'] ) && 'go_dashboard' === $_GET['action'] ) {
        // Safe to redirect here because no body output has been generated yet.
        wp_redirect( admin_url() );
        exit;
    }
}
