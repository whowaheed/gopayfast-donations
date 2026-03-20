<?php
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Register shortcodes
 */
function gpf_register_shortcodes() {
    add_shortcode( 'gpf_donation_form', 'gpf_render_donation_form' );
    add_shortcode( 'gpf_donation_bar', 'gpf_render_donation_bar' );
}
add_action( 'init', 'gpf_register_shortcodes' );

/**
 * Get security fields (honeypot + nonce + timestamp)
 */
function gpf_get_security_fields() {
    // Prevent caching of nonce via fragment caching
    $output = '<div style="position: absolute; left: -5000px;" aria-hidden="true">';
    $output .= '<input type="text" name="website" tabindex="-1" value="" autocomplete="off">';
    $output .= '</div>';
    
    // Use action to generate nonce dynamically via AJAX to avoid caching issues
    $output .= '<input type="hidden" name="gpf_donate_nonce" id="gpf_donate_nonce_field" value="">';
    $output .= '<input type="hidden" name="gpf_timestamp" value="' . esc_attr(time()) . '">';
    
    // Add inline script to populate nonce dynamically
    $output .= '<script>document.getElementById("gpf_donate_nonce_field").value = "' . wp_create_nonce('gpf_donate_action') . '";</script>';
    
    return $output;
}

/**
 * Render donation form shortcode
 */
function gpf_render_donation_form( $atts ) {
    wp_enqueue_style( 'gpf-style' );
    wp_enqueue_script( 'gpf-form-script' );
    
    // Get admin defaults
    $admin_min = intval(get_option('gpf_min_amount', 10));
    $admin_max = intval(get_option('gpf_max_amount', 500000));
    
    $atts = shortcode_atts([
        'min_amount' => $admin_min,
        'max_amount' => $admin_max,
    ], $atts);
    
    ob_start();
    
    $security_fields = gpf_get_security_fields();
    $min_amount = intval($atts['min_amount']);
    $max_amount = intval($atts['max_amount']);
    
    include( GPF_PLUGIN_PATH . 'templates/donation-form-template.php' );
    return ob_get_clean();
}

/**
 * Render donation bar shortcode
 */
function gpf_render_donation_bar( $atts ) {
    wp_enqueue_style( 'gpf-style' );
    wp_enqueue_script( 'gpf-bar-script' );
    
    // Get admin defaults
    $admin_min = intval(get_option('gpf_min_amount', 10));
    $admin_max = intval(get_option('gpf_max_amount', 500000));
    
    $atts = shortcode_atts([
        'min_amount' => $admin_min,
        'max_amount' => $admin_max,
    ], $atts);
    
    ob_start();
    
    $security_fields = gpf_get_security_fields();
    $min_amount = intval($atts['min_amount']);
    $max_amount = intval($atts['max_amount']);
    
    include( GPF_PLUGIN_PATH . 'templates/donation-bar-template.php' );
    return ob_get_clean();
}
