<?php
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Handle Payment Success and Failure/Cancel Return URLs
 */

/**
 * Handle payment success
 */
function gpf_handle_payment_success() {
    // Only handle if this is the payment-success page
    if (!is_page('payment-success')) {
        return;
    }
    
    // Get and sanitize parameters
    $basket_id = isset($_GET['basket_id']) ? sanitize_text_field($_GET['basket_id']) : '';
    $transaction_id = isset($_GET['transaction_id']) ? sanitize_text_field($_GET['transaction_id']) : '';
    $amount = isset($_GET['transaction_amount']) ? floatval($_GET['transaction_amount']) : 0;
    
    // Log success (optional)
    gpf_log_security_event('payment_success', [
        'basket_id' => $basket_id,
        'transaction_id' => $transaction_id,
        'amount' => $amount
    ]);
    
    // Display success message
    add_filter('the_content', 'gpf_payment_success_content');
}
add_action('wp', 'gpf_handle_payment_success');

/**
 * Success page content
 */
function gpf_payment_success_content($content) {
    // Only modify on payment-success page
    if (!is_page('payment-success')) {
        return $content;
    }
    
    $basket_id = isset($_GET['basket_id']) ? sanitize_text_field($_GET['basket_id']) : '';
    $transaction_id = isset($_GET['transaction_id']) ? sanitize_text_field($_GET['transaction_id']) : '';
    $amount = isset($_GET['transaction_amount']) ? floatval($_GET['transaction_amount']) : 0;
    
    $success_html = '
    <div class="gpf-result-container gpf-success">
        <div class="gpf-result-icon">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
        </div>
        <h2>Payment Successful!</h2>
        <p class="gpf-result-message">Thank you for your donation. Your payment has been processed successfully.</p>
        
        <div class="gpf-result-details">
            <div class="gpf-detail-row">
                <span>Order ID:</span>
                <strong>' . esc_html($basket_id) . '</strong>
            </div>
            ' . ($transaction_id ? '
            <div class="gpf-detail-row">
                <span>Transaction ID:</span>
                <strong>' . esc_html($transaction_id) . '</strong>
            </div>
            ' : '') . '
            ' . ($amount ? '
            <div class="gpf-detail-row">
                <span>Amount:</span>
                <strong>PKR ' . number_format($amount, 2) . '</strong>
            </div>
            ' : '') . '
        </div>
        
        <div class="gpf-result-actions">
            <a href="' . esc_url(home_url()) . '" class="gpf-btn gpf-btn-primary">Return to Homepage</a>
        </div>
    </div>';
    
    return $success_html;
}

/**
 * Handle payment failure/cancel
 */
function gpf_handle_payment_failed() {
    // Only handle if this is the payment-failed page
    if (!is_page('payment-failed')) {
        return;
    }
    
    // Get and sanitize parameters
    $basket_id = isset($_GET['basket_id']) ? sanitize_text_field($_GET['basket_id']) : '';
    $err_code = isset($_GET['err_code']) ? sanitize_text_field($_GET['err_code']) : '';
    $err_msg = isset($_GET['err_msg']) ? sanitize_text_field(urldecode($_GET['err_msg'])) : '';
    $transaction_id = isset($_GET['transaction_id']) ? sanitize_text_field($_GET['transaction_id']) : '';
    $amount = isset($_GET['transaction_amount']) ? floatval($_GET['transaction_amount']) : 0;
    
    // Log the failure (optional - for monitoring)
    gpf_log_security_event('payment_failed', [
        'basket_id' => $basket_id,
        'err_code' => $err_code,
        'err_msg' => $err_msg
    ]);
    
    // Display failure message
    add_filter('the_content', 'gpf_payment_failed_content');
}
add_action('wp', 'gpf_handle_payment_failed');

/**
 * Failure/Cancel page content
 */
function gpf_payment_failed_content($content) {
    // Only modify on payment-failed page
    if (!is_page('payment-failed')) {
        return $content;
    }
    
    $basket_id = isset($_GET['basket_id']) ? sanitize_text_field($_GET['basket_id']) : '';
    $err_code = isset($_GET['err_code']) ? sanitize_text_field($_GET['err_code']) : '';
    $err_msg = isset($_GET['err_msg']) ? sanitize_text_field(urldecode($_GET['err_msg'])) : '';
    $amount = isset($_GET['transaction_amount']) ? floatval($_GET['transaction_amount']) : 0;
    
    // Determine if it was a cancellation or failure
    $is_cancelled = ($err_code === '901' || stripos($err_msg, 'cancel') !== false);
    
    $title = $is_cancelled ? 'Payment Cancelled' : 'Payment Failed';
    $message = $is_cancelled 
        ? 'You have cancelled the payment. No amount has been charged.' 
        : 'We couldn\'t process your payment. Please try again.';
    
    $failed_html = '
    <div class="gpf-result-container gpf-failed">
        <div class="gpf-result-icon">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
        </div>
        <h2>' . $title . '</h2>
        <p class="gpf-result-message">' . $message . '</p>
        
        ' . ($err_msg ? '<p class="gpf-error-reason">Reason: ' . esc_html($err_msg) . '</p>' : '') . '
        
        <div class="gpf-result-details">
            ' . ($basket_id ? '
            <div class="gpf-detail-row">
                <span>Order ID:</span>
                <strong>' . esc_html($basket_id) . '</strong>
            </div>
            ' : '') . '
            ' . ($amount ? '
            <div class="gpf-detail-row">
                <span>Attempted Amount:</span>
                <strong>PKR ' . number_format($amount, 2) . '</strong>
            </div>
            ' : '') . '
        </div>
        
        <div class="gpf-result-actions">
            <a href="' . esc_url(home_url('/donate/')) . '" class="gpf-btn gpf-btn-primary">Try Again</a>
            <a href="' . esc_url(home_url()) . '" class="gpf-btn gpf-btn-secondary">Return to Homepage</a>
        </div>
    </div>';
    
    return $failed_html;
}

/**
 * Add styles for result pages
 */
function gpf_result_page_styles() {
    if (is_page('payment-success') || is_page('payment-failed')) {
        ?>
        <style>
            .gpf-result-container {
                max-width: 600px;
                margin: 2rem auto;
                padding: 3rem 2rem;
                background: #fff;
                border-radius: 12px;
                box-shadow: 0 4px 20px rgba(0,0,0,0.1);
                text-align: center;
            }
            
            .gpf-result-icon {
                width: 80px;
                height: 80px;
                margin: 0 auto 1.5rem;
                border-radius: 50%;
                display: flex;
                align-items: center;
                justify-content: center;
            }
            
            .gpf-success .gpf-result-icon {
                background: #d1fae5;
                color: #047857;
            }
            
            .gpf-failed .gpf-result-icon {
                background: #fee2e2;
                color: #dc2626;
            }
            
            .gpf-result-icon svg {
                width: 40px;
                height: 40px;
            }
            
            .gpf-result-container h2 {
                font-size: 1.75rem;
                font-weight: 700;
                margin-bottom: 1rem;
                color: #1f2937;
            }
            
            .gpf-result-message {
                font-size: 1.1rem;
                color: #6b7280;
                margin-bottom: 1.5rem;
            }
            
            .gpf-error-reason {
                background: #fef2f2;
                color: #991b1b;
                padding: 0.75rem 1rem;
                border-radius: 8px;
                margin-bottom: 1.5rem;
                font-size: 0.95rem;
            }
            
            .gpf-result-details {
                background: #f9fafb;
                border-radius: 8px;
                padding: 1.5rem;
                margin-bottom: 2rem;
            }
            
            .gpf-detail-row {
                display: flex;
                justify-content: space-between;
                padding: 0.5rem 0;
                border-bottom: 1px solid #e5e7eb;
            }
            
            .gpf-detail-row:last-child {
                border-bottom: none;
            }
            
            .gpf-detail-row span {
                color: #6b7280;
            }
            
            .gpf-detail-row strong {
                color: #374151;
                font-weight: 600;
            }
            
            .gpf-result-actions {
                display: flex;
                gap: 1rem;
                justify-content: center;
                flex-wrap: wrap;
            }
            
            .gpf-btn {
                display: inline-block;
                padding: 0.875rem 1.5rem;
                border-radius: 8px;
                font-weight: 600;
                text-decoration: none;
                transition: all 0.2s;
            }
            
            .gpf-btn-primary {
                background: #047857;
                color: #fff;
            }
            
            .gpf-btn-primary:hover {
                background: #065f46;
            }
            
            .gpf-btn-secondary {
                background: #e5e7eb;
                color: #374151;
            }
            
            .gpf-btn-secondary:hover {
                background: #d1d5db;
            }
        </style>
        <?php
    }
}
add_action('wp_head', 'gpf_result_page_styles');
