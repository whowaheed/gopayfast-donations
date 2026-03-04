<?php
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Register plugin assets
 */
function gpf_register_assets() {
    $version = GPF_VERSION;
    
    wp_register_style(
        'gpf-style',
        GPF_PLUGIN_URL . 'assets/css/gpf-style.css',
        array(),
        $version
    );

    wp_register_script(
        'gpf-form-script',
        GPF_PLUGIN_URL . 'assets/js/gpf-form.js',
        array( 'jquery' ),
        $version,
        true
    );
    
    wp_register_script(
        'gpf-bar-script',
        GPF_PLUGIN_URL . 'assets/js/gpf-bar.js',
        array( 'jquery' ),
        $version,
        true
    );
}
add_action( 'wp_enqueue_scripts', 'gpf_register_assets' );

/**
 * Add security headers for payment pages
 */
function gpf_add_security_headers() {
    if (!is_admin()) {
        global $post;
        if ($post && (has_shortcode($post->post_content, 'gpf_donation_form') || 
                      has_shortcode($post->post_content, 'gpf_donation_bar'))) {
            // Prevent clickjacking
            header('X-Frame-Options: SAMEORIGIN');
            // Enable XSS protection
            header('X-XSS-Protection: 1; mode=block');
            // Prevent MIME type sniffing
            header('X-Content-Type-Options: nosniff');
            // Referrer policy
            header('Referrer-Policy: strict-origin-when-cross-origin');
            // Strict Transport Security (HTTPS only)
            if (is_ssl()) {
                header('Strict-Transport-Security: max-age=31536000; includeSubDomains');
            }
        }
    }
}
add_action('wp', 'gpf_add_security_headers');
