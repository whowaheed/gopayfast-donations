<?php
/**
 * Plugin Name:       GoPayFast
 * Plugin URI:        https://whowaheed.com
 * Description:       Secure GoPayFast payment integration.
 * Version:           1.0.0
 * Author:            Abdul Waheed
 * Author URI:        https://whowaheed.com
 * License:           GPLv2 or later
 * Text Domain:       gopayfast
 */

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Define plugin constants
define( 'GPF_PLUGIN_PATH', plugin_dir_path( __FILE__ ) );
define( 'GPF_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'GPF_VERSION', '1.0.0' );

// Load security functions first
require_once( GPF_PLUGIN_PATH . 'includes/security.php' );

// Load plugin components
require_once( GPF_PLUGIN_PATH . 'includes/enqueue.php' );
require_once( GPF_PLUGIN_PATH . 'includes/shortcodes.php' );
require_once( GPF_PLUGIN_PATH . 'includes/form-handler.php' );
require_once( GPF_PLUGIN_PATH . 'includes/result-handler.php' );

// Admin-only files
if ( is_admin() ) {
    require_once( GPF_PLUGIN_PATH . 'includes/admin-settings.php' );
}

/**
 * Activation hook - create necessary options and pages
 */
register_activation_hook( __FILE__, 'gpf_activate' );
function gpf_activate() {
    // Initialize empty blocked IPs list (server-side only)
    add_option('gpf_blocked_ips', []);
    
    // Create payment result pages
    gpf_create_payment_pages();
}

/**
 * Create payment success and failed pages
 */
function gpf_create_payment_pages() {
    // Check if pages already exist
    $success_page = get_page_by_path('payment-success');
    $failed_page = get_page_by_path('payment-failed');
    
    // Create Success Page
    if (!$success_page) {
        $success_page_id = wp_insert_post([
            'post_title'   => 'Payment Success',
            'post_name'    => 'payment-success',
            'post_content' => '', // Content will be injected by plugin
            'post_status'  => 'publish',
            'post_type'    => 'page',
            'post_author'  => 1,
            'page_template' => 'default',
        ]);
        
        if ($success_page_id && !is_wp_error($success_page_id)) {
            update_post_meta($success_page_id, '_gpf_auto_created', '1');
        }
    }
    
    // Create Failed Page
    if (!$failed_page) {
        $failed_page_id = wp_insert_post([
            'post_title'   => 'Payment Failed',
            'post_name'    => 'payment-failed',
            'post_content' => '', // Content will be injected by plugin
            'post_status'  => 'publish',
            'post_type'    => 'page',
            'post_author'  => 1,
            'page_template' => 'default',
        ]);
        
        if ($failed_page_id && !is_wp_error($failed_page_id)) {
            update_post_meta($failed_page_id, '_gpf_auto_created', '1');
        }
    }
}

/**
 * Deactivation hook - cleanup
 */
register_deactivation_hook( __FILE__, 'gpf_deactivate' );
function gpf_deactivate() {
    // Optionally delete auto-created pages (uncomment if needed)
    // gpf_delete_payment_pages();
}

/**
 * Delete auto-created payment pages (optional cleanup)
 */
function gpf_delete_payment_pages() {
    $pages = [
        'payment-success',
        'payment-failed'
    ];
    
    foreach ($pages as $slug) {
        $page = get_page_by_path($slug);
        if ($page && get_post_meta($page->ID, '_gpf_auto_created', true) === '1') {
            wp_delete_post($page->ID, true); // Force delete
        }
    }
}
