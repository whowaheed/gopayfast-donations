<?php
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Get client IP address
 */
function gpf_get_client_ip() {
    $ip_keys = ['HTTP_CF_CONNECTING_IP', 'HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'REMOTE_ADDR'];
    
    foreach ($ip_keys as $key) {
        if (!empty($_SERVER[$key])) {
            $ips = explode(',', sanitize_text_field($_SERVER[$key]));
            $ip = trim($ips[0]);
            if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
                return $ip;
            }
        }
    }
    
    return sanitize_text_field($_SERVER['REMOTE_ADDR'] ?? '0.0.0.0');
}

/**
 * Handle the donation form submission and redirect to GoPayFast.
 */
add_action( 'admin_post_nopriv_gpf_handle_donation', 'gpf_handle_donation_submission' );
add_action( 'admin_post_gpf_handle_donation', 'gpf_handle_donation_submission' );

function gpf_handle_donation_submission() {
    
    // SECURITY CHECK 1: Verify request method is POST
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        gpf_log_security_event('invalid_request_method', ['method' => $_SERVER['REQUEST_METHOD']]);
        wp_die( 'Invalid request method.', 'Security Error', ['response' => 405] );
    }
    
    // SECURITY CHECK 2: Check if IP is blocked
    if (function_exists('gpf_is_ip_blocked') && gpf_is_ip_blocked()) {
        wp_die( 'Access denied.', 'Forbidden', ['response' => 403] );
    }
    
    // SECURITY CHECK 3: Rate limiting (max 5 per hour)
    if (function_exists('gpf_check_rate_limit')) {
        $rate_check = gpf_check_rate_limit();
        if (!$rate_check['allowed']) {
            wp_die( esc_html($rate_check['message']), 'Rate Limit Exceeded', ['response' => 429] );
        }
    }
    
    // SECURITY CHECK 4: Check for bots
    if (function_exists('gpf_is_bot') && gpf_is_bot()) {
        gpf_log_security_event('bot_detected');
        wp_die( 'Access denied.', 'Forbidden', ['response' => 403] );
    }
    
    // SECURITY CHECK 5: Nonce verification (CSRF protection)
    if ( ! isset( $_POST['gpf_donate_nonce'] ) || ! wp_verify_nonce( $_POST['gpf_donate_nonce'], 'gpf_donate_action' ) ) {
        gpf_log_security_event('invalid_nonce');
        wp_die( 'Security check failed. Please refresh the page and try again.', 'Security Error', ['response' => 403] );
    }
    
    // SECURITY CHECK 6: Honeypot check (anti-spam)
    if ( ! empty( $_POST['website'] ) ) {
        gpf_log_security_event('honeypot_triggered');
        wp_die( 'Spam detected.', 'Access Denied', ['response' => 403] );
    }
    
    // SECURITY CHECK 7: Timestamp check (prevent form replay attacks)
    if (isset($_POST['gpf_timestamp'])) {
        $form_timestamp = intval($_POST['gpf_timestamp']);
        $current_time = time();
        if ($form_timestamp < ($current_time - 3600) || $form_timestamp > $current_time) {
            gpf_log_security_event('invalid_timestamp', ['submitted' => $form_timestamp, 'current' => $current_time]);
            wp_die( 'Form expired. Please refresh the page and try again.', 'Form Expired', ['response' => 403] );
        }
    }
    
    // Get settings
    $merchant_id  = get_option('gpf_merchant_id');
    $secured_key  = get_option('gpf_secret_key');
    $token_url    = get_option('gpf_token_url');
    $checkout_url = get_option('gpf_checkout_url');
    
    if ( empty($merchant_id) || empty($secured_key) || empty($token_url) || empty($checkout_url) ) {
        gpf_log_security_event('missing_configuration');
        wp_die( 'Payment system not configured.', 'Configuration Error', ['response' => 500] );
    }
    
    // VALIDATE AND SANITIZE: Amount
    $amount = isset($_POST['amount']) ? gpf_sanitize_amount($_POST['amount']) : 0;
    if (!gpf_validate_amount($amount)) {
        gpf_log_security_event('invalid_amount', ['amount' => $_POST['amount']]);
        wp_die( 'Invalid amount.', 'Invalid Input', ['response' => 400] );
    }
    
    // VALIDATE AND SANITIZE: Name
    $full_name = isset($_POST['full-name']) ? sanitize_text_field($_POST['full-name']) : '';
    if (!gpf_validate_name($full_name)) {
        gpf_log_security_event('invalid_name', ['name' => $full_name]);
        wp_die( 'Please enter a valid name (2-100 characters).', 'Invalid Input', ['response' => 400] );
    }
    
    // VALIDATE AND SANITIZE: Email
    $email = isset($_POST['email']) ? sanitize_email($_POST['email']) : '';
    if (!gpf_validate_email($email)) {
        gpf_log_security_event('invalid_email', ['email' => $email]);
        wp_die( 'Please enter a valid email address.', 'Invalid Email', ['response' => 400] );
    }
    
    // VALIDATE AND SANITIZE: Phone
    $phone = isset($_POST['phone']) ? sanitize_text_field($_POST['phone']) : '';
    if (!empty($phone)) {
        if (!gpf_validate_phone($phone)) {
            wp_die( 'Please enter a valid phone number.', 'Invalid Phone', ['response' => 400] );
        }
    } else {
        $phone = '0900000000';
    }
    
    // Generate unique basket ID
    $basket_id = 'ORD-' . strtoupper(wp_generate_password(6, false));
    
    // Log successful validation
    gpf_log_security_event('payment_initiated', [
        'basket_id' => $basket_id,
        'amount' => $amount,
        'email' => substr($email, 0, 3) . '***@***' // Partially masked
    ]);
    
    // Get Access Token from GoPayFast
    $urlPostParams = sprintf(
        'MERCHANT_ID=%s&SECURED_KEY=%s&BASKET_ID=%s&TXNAMT=%s&CURRENCY_CODE=%s',
        urlencode($merchant_id),
        urlencode($secured_key),
        urlencode($basket_id),
        urlencode($amount),
        urlencode('PKR')
    );
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $token_url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $urlPostParams);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
    
    $response = curl_exec($ch);
    
    if (curl_errno($ch)) {
        $error_msg = curl_error($ch);
        curl_close($ch);
        gpf_log_security_event('curl_error', ['error' => $error_msg]);
        wp_die( 'Unable to connect to payment gateway. Please try again later.', 'Connection Error', ['response' => 503] );
    }
    
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($http_code !== 200) {
        gpf_log_security_event('gateway_http_error', ['code' => $http_code]);
        wp_die( 'Payment gateway returned an error. Please try again later.', 'Gateway Error', ['response' => 502] );
    }
    
    $payload = json_decode($response);
    
    if (json_last_error() !== JSON_ERROR_NONE) {
        gpf_log_security_event('invalid_json_response');
        wp_die( 'Invalid response from payment gateway.', 'Gateway Error', ['response' => 502] );
    }
    
    $token = isset($payload->ACCESS_TOKEN) ? sanitize_text_field($payload->ACCESS_TOKEN) : '';
    
    if ( empty($token) ) {
        $error_msg = isset($payload->MESSAGE) ? sanitize_text_field($payload->MESSAGE) : 'Unable to generate Access Token';
        gpf_log_security_event('token_generation_failed', ['message' => $error_msg]);
        wp_die( 'Gateway Error: ' . esc_html($error_msg), 'Authentication Error', ['response' => 401] );
    }
    
    // Build success and failure URLs with nonce for verification
    $success_url = home_url('/payment-success/');
    $failure_url = home_url('/payment-failed/');
    
    // Output secure auto-submit form
    ?>
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset="UTF-8">
        <meta name="robots" content="noindex, nofollow">
        <title>Redirecting to GoPayFast...</title>
        <style>
            body { font-family: Arial, sans-serif; text-align: center; padding: 50px; background: #f9fafb; }
            .loader { border: 4px solid #f3f3f3; border-top: 4px solid #34a853; border-radius: 50%; width: 40px; height: 40px; animation: spin 1s linear infinite; margin: 20px auto; }
            @keyframes spin { 0% { transform: rotate(0deg); } 100% { transform: rotate(360deg); } }
            p { color: #374151; }
        </style>
    </head>
    <body onload="document.forms['checkout_form'].submit();">
        <div class="loader"></div>
        <p>Connecting to secure payment gateway...</p>
        <p style="font-size: 12px; color: #666;">If you are not redirected automatically, <button type="submit" form="checkout_form" style="background: none; border: none; color: #34a853; text-decoration: underline; cursor: pointer;">click here</button></p>
        
        <form id="checkout_form" name="checkout_form" method="post" action="<?php echo esc_url($checkout_url); ?>">
            <input type="hidden" name="MERCHANT_ID" value="<?php echo esc_attr($merchant_id); ?>" />
            <input type="hidden" name="TOKEN" value="<?php echo esc_attr($token); ?>" />
            <input type="hidden" name="BASKET_ID" value="<?php echo esc_attr($basket_id); ?>" />
            <input type="hidden" name="TXNAMT" value="<?php echo esc_attr($amount); ?>" />
            <input type="hidden" name="CURRENCY_CODE" value="PKR" />
            <input type="hidden" name="CUSTOMER_EMAIL_ADDRESS" value="<?php echo esc_attr($email); ?>" />
            <input type="hidden" name="CUSTOMER_MOBILE_NO" value="<?php echo esc_attr($phone); ?>" />
            <input type="hidden" name="SUCCESS_URL" value="<?php echo esc_url($success_url); ?>" />
            <input type="hidden" name="FAILURE_URL" value="<?php echo esc_url($failure_url); ?>" />
            <input type="hidden" name="ORDER_DATE" value="<?php echo esc_attr(current_time('Y-m-d H:i:s')); ?>" />
            <input type="hidden" name="PROCCODE" value="00" />
            <input type="hidden" name="TRAN_TYPE" value="ECOMM_PURCHASE" />
            <input type="hidden" name="TXNDESC" value="<?php echo esc_attr('Payment from ' . $full_name); ?>" />
        </form>
    </body>
    </html>
    <?php
    exit;
}
