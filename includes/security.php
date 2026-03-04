<?php
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Security Functions for GoPayFast Plugin
 * 
 * All security events are logged to server error log only (not dashboard)
 */

/**
 * Check donation submission rate limit
 */
function gpf_check_rate_limit() {
    $ip = gpf_get_client_ip();
    $transient_key = 'gpf_rate_' . md5($ip);
    $attempts = get_transient($transient_key);
    
    if ($attempts === false) {
        set_transient($transient_key, 1, HOUR_IN_SECONDS);
        return ['allowed' => true, 'remaining' => 4];
    }
    
    if ($attempts >= 5) {
        gpf_log_security_event('rate_limit_exceeded', ['ip' => $ip]);
        return [
            'allowed' => false, 
            'message' => 'Too many requests. Please try again later.',
            'remaining' => 0
        ];
    }
    
    set_transient($transient_key, $attempts + 1, HOUR_IN_SECONDS);
    return ['allowed' => true, 'remaining' => 5 - ($attempts + 1)];
}

/**
 * Check if IP is blocked
 */
function gpf_is_ip_blocked($ip = null) {
    if (!$ip) {
        $ip = gpf_get_client_ip();
    }
    
    $blocked_ips = get_option('gpf_blocked_ips', []);
    return in_array($ip, $blocked_ips);
}

/**
 * Log security events to server error log only
 */
function gpf_log_security_event($event_type, $data = []) {
    $log_entry = [
        'timestamp'  => current_time('mysql'),
        'ip_address' => gpf_get_client_ip(),
        'event_type' => $event_type,
        'data'       => $data,
        'user_agent' => isset($_SERVER['HTTP_USER_AGENT']) ? sanitize_text_field($_SERVER['HTTP_USER_AGENT']) : '',
    ];
    
    // Log to server error log only (not database/dashboard)
    error_log('GoPayFast Security: ' . json_encode($log_entry));
}

/**
 * Validate name - prevent injection attempts
 */
function gpf_validate_name($name) {
    if (strlen($name) < 2 || strlen($name) > 100) {
        return false;
    }
    
    $suspicious_patterns = [
        '/<script/i',
        '/javascript:/i',
        '/on\w+\s*=/i',
        '/<iframe/i',
        '/<object/i',
        '/<embed/i',
        '/eval\s*\(/i',
        '/expression\s*\(/i',
    ];
    
    foreach ($suspicious_patterns as $pattern) {
        if (preg_match($pattern, $name)) {
            gpf_log_security_event('suspicious_name', ['name' => $name]);
            return false;
        }
    }
    
    return true;
}

/**
 * Validate amount
 */
function gpf_validate_amount($amount) {
    if (!is_numeric($amount)) {
        return false;
    }
    
    $amount = floatval($amount);
    $min = apply_filters('gpf_min_amount', 10);
    $max = apply_filters('gpf_max_amount', 500000);
    
    if ($amount < $min || $amount > $max) {
        return false;
    }
    
    // Check for SQL injection patterns
    $suspicious = ['/;/', '/--/', '/\*\*/', '/union/i', '/select/i', '/insert/i', '/delete/i'];
    foreach ($suspicious as $pattern) {
        if (preg_match($pattern, (string)$amount)) {
            gpf_log_security_event('suspicious_amount', ['amount' => $amount]);
            return false;
        }
    }
    
    return true;
}

/**
 * Validate email with stricter checks
 */
function gpf_validate_email($email) {
    if (!is_email($email)) {
        return false;
    }
    
    if (strlen($email) > 100) {
        return false;
    }
    
    // Check for disposable email domains
    $blocked_domains = apply_filters('gpf_blocked_email_domains', [
        'tempmail.com',
        'throwaway.com',
        'mailinator.com',
        'guerrillamail.com',
    ]);
    
    $domain = substr(strrchr($email, "@"), 1);
    if (in_array(strtolower($domain), $blocked_domains)) {
        gpf_log_security_event('blocked_email_domain', ['email' => $email]);
        return false;
    }
    
    return true;
}

/**
 * Validate phone number
 */
function gpf_validate_phone($phone) {
    $phone = preg_replace('/[^0-9]/', '', $phone);
    
    if (strlen($phone) < 10 || strlen($phone) > 15) {
        return false;
    }
    
    return true;
}

/**
 * Sanitize amount input
 */
function gpf_sanitize_amount($amount) {
    $amount = preg_replace('/[^0-9.]/', '', $amount);
    return floatval($amount);
}

/**
 * Add security headers
 */
function gpf_security_headers() {
    if (!is_admin()) {
        header('X-Frame-Options: SAMEORIGIN');
        header('X-XSS-Protection: 1; mode=block');
        header('X-Content-Type-Options: nosniff');
        header('Referrer-Policy: strict-origin-when-cross-origin');
    }
}
add_action('wp', 'gpf_security_headers');

/**
 * Check for bots using basic checks
 */
function gpf_is_bot() {
    $user_agent = isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '';
    
    if (empty($user_agent)) {
        return true;
    }
    
    $bot_keywords = ['bot', 'crawl', 'spider', 'scrape', 'curl', 'wget', 'python', 'java'];
    $user_agent_lower = strtolower($user_agent);
    
    foreach ($bot_keywords as $keyword) {
        if (strpos($user_agent_lower, $keyword) !== false) {
            return true;
        }
    }
    
    return false;
}
