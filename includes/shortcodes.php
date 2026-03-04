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
    $output = '<div style="position: absolute; left: -5000px;" aria-hidden="true">';
    $output .= '<input type="text" name="website" tabindex="-1" value="" autocomplete="off">';
    $output .= '</div>';
    $output .= wp_nonce_field( 'gpf_donate_action', 'gpf_donate_nonce', true, false );
    $output .= '<input type="hidden" name="gpf_timestamp" value="' . esc_attr(time()) . '">';
    return $output;
}

/**
 * Render donation form shortcode
 */
function gpf_render_donation_form( $atts ) {
    wp_enqueue_style( 'gpf-style' );
    wp_enqueue_script( 'gpf-form-script' );
    
    $atts = shortcode_atts([
        'min_amount' => 10,
        'max_amount' => 500000,
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
    
    $atts = shortcode_atts([
        'min_amount' => 10,
        'max_amount' => 500000,
    ], $atts);
    
    ob_start();
    
    $security_fields = gpf_get_security_fields();
    $min_amount = intval($atts['min_amount']);
    $max_amount = intval($atts['max_amount']);
    
    include( GPF_PLUGIN_PATH . 'templates/donation-bar-template.php' );
    return ob_get_clean();
}
