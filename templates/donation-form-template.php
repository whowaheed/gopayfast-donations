<?php
/**
 * Donation Form Template
 * 
 * Variables available:
 * - $security_fields: Security fields (honeypot, timestamp)
 * - $min_amount: Minimum donation amount
 * - $max_amount: Maximum donation amount
 */
if (!defined('ABSPATH')) exit;
?>

<div class="card-container">
  <div class="donation-card">

    <form action="<?php echo esc_url( admin_url('admin-post.php') ); ?>" method="POST" autocomplete="off">

      <input type="hidden" name="action" value="gpf_handle_donation">
      
      <?php echo $security_fields; ?>

      <div class="card-body">
        <div class="form-space-y">

          <div class="form-group-floating">
            <input
              type="text"
              id="full-name"
              name="full-name"
              required
              maxlength="100"
              pattern="[A-Za-z\s\-']+"
              class="peer floating-input"
              placeholder="Full Name"
              autocomplete="name"
            />
            <label for="full-name" class="floating-label">Full Name</label>
          </div>

          <div class="form-grid-cols-2">
            <div class="form-group-floating">
              <input
                type="email"
                id="email"
                name="email"
                required
                maxlength="100"
                class="peer floating-input"
                placeholder="Email Address"
                autocomplete="email"
              />
              <label for="email" class="floating-label">Email Address</label>
            </div>
            <div class="form-group-floating">
              <input
                type="tel"
                id="phone"
                name="phone"
                maxlength="20"
                pattern="[0-9\+\-\s\(\)]+"
                class="peer floating-input"
                placeholder="Phone Number"
                autocomplete="tel"
              />
              <label for="phone" class="floating-label">Phone Number (Optional)</label>
            </div>
          </div>

          <div>
            <label class="form-label">Donation Amount (PKR)</label>
            <div id="amount-options" class="amount-options-grid">
              <button type="button" data-amount="500" class="amount-btn">500</button>
              <button type="button" data-amount="1000" class="amount-btn">1000</button>
              <button type="button" data-amount="5000" class="amount-btn">5000</button>
              <input
                type="number"
                id="custom-amount"
                name="amount"
                min="<?php echo esc_attr($min_amount); ?>"
                max="<?php echo esc_attr($max_amount); ?>"
                step="1"
                class="custom-amount-input"
                placeholder="Custom"
                required
                autocomplete="off"
              />
            </div>
          </div>

          <div>
            <label class="form-label">Payment Method</label>
            <div class="payment-options-stack">
              <input type="radio" name="payment-method" value="card" id="pay-card" class="sr-only" checked />
              <label for="pay-card" class="payment-option-label">Pay with Card</label>

              <input type="radio" name="payment-method" value="jazzcash" id="pay-jazzcash" class="sr-only" />
              <label for="pay-jazzcash" class="payment-option-label">JazzCash</label>

              <input type="radio" name="payment-method" value="easypaisa" id="pay-easypaisa" class="sr-only" />
              <label for="pay-easypaisa" class="payment-option-label">EasyPaisa</label>

              <input type="radio" name="payment-method" value="bank" id="pay-bank" class="sr-only" />
              <label for="pay-bank" class="payment-option-label">Bank Transfer</label>
            </div>
          </div>

        </div>
      </div>

      <div class="card-footer">
        <button type="submit" class="submit-button">Donate Now</button>

        <p class="footer-text-small">
          Secure payment powered by GoPayFast
        </p>
        <div class="footer-badge-container">
          <div class="footer-badge">
            <svg class="badge-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
            </svg>
            100% Secure Payment
          </div>
          <div class="footer-badge">
            <svg class="badge-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12 12 0 0012 21.697z"></path>
            </svg>
            Instant Confirmation
          </div>
        </div>
      </div>

    </form>
  </div>
</div>
