<?php
/**
 * Donation Bar Template
 * 
 * Variables available:
 * - $security_fields: Security fields (honeypot, timestamp)
 * - $min_amount: Minimum donation amount
 * - $max_amount: Maximum donation amount
 */
if (!defined('ABSPATH')) exit;
?>

<section class="donation-bar-section">
  <div class="container">
    <div class="donation-card">
      <form action="<?php echo esc_url( admin_url('admin-post.php') ); ?>" method="POST" autocomplete="off">
        
        <input type="hidden" name="action" value="gpf_handle_donation">
        <?php echo $security_fields; ?>

        <div class="form-grid">
          
          <!-- Amount Selection -->
          <div class="form-group-amount">
            <label class="form-label">Select Amount (PKR)</label>
            <div id="amount-options" class="amount-options-grid">
              <button type="button" data-amount="500" class="amount-btn-bar">500</button>
              <button type="button" data-amount="1000" class="amount-btn-bar">1000</button>
              <button type="button" data-amount="5000" class="amount-btn-bar">5000</button>
              <input
                type="number"
                id="custom-amount"
                name="amount"
                min="<?php echo esc_attr($min_amount); ?>"
                max="<?php echo esc_attr($max_amount); ?>"
                step="1"
                class="custom-amount-input"
                placeholder="Other"
                required
                autocomplete="off"
              />
            </div>
          </div>

          <!-- Name -->
          <div class="form-group-name">
            <div class="input-wrapper">
              <input
                type="text"
                id="full-name"
                name="full-name"
                required
                maxlength="100"
                class="peer floating-input input-bar-item"
                placeholder="Full Name"
                autocomplete="name"
              />
              <label for="full-name" class="floating-label">Full Name</label>
            </div>
          </div>

          <!-- Email -->
          <div class="form-group-email">
            <div class="input-wrapper">
              <input
                type="email"
                id="email"
                name="email"
                required
                maxlength="100"
                class="peer floating-input input-bar-item"
                placeholder="Email Address"
                autocomplete="email"
              />
              <label for="email" class="floating-label">Email Address</label>
            </div>
          </div>

          <!-- Submit Button -->
          <div class="form-group-submit">
            <button type="submit" class="submit-button input-bar-item">
              Donate Now
              <svg class="submit-arrow" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"></path>
              </svg>
            </button>
          </div>

        </div>
      </form>
    </div>
  </div>
</section>
