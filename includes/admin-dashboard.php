<?php
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Create GoPayFast menu
 */
add_action( 'admin_menu', function() {
    add_menu_page(
        'GoPayFast Settings',
        'GoPayFast',
        'manage_options',
        'gopayfast_settings',
        'gpf_settings_page_html',
        'dashicons-money-alt',
        25
    );
});

/**
 * Register settings
 */
add_action( 'admin_init', function() {
    register_setting( 'gpf_settings_group', 'gpf_merchant_id' );
    register_setting( 'gpf_settings_group', 'gpf_secret_key' );
    register_setting( 'gpf_settings_group', 'gpf_token_url' );
    register_setting( 'gpf_settings_group', 'gpf_checkout_url' );
    register_setting( 'gpf_settings_group', 'gpf_sandbox_mode' );
    register_setting( 'gpf_settings_group', 'gpf_min_amount' );
    register_setting( 'gpf_settings_group', 'gpf_max_amount' );
});

/**
 * Enqueue admin assets
 */
add_action( 'admin_enqueue_scripts', function($hook) {
    if ($hook !== 'toplevel_page_gopayfast_settings') return;
    
    wp_enqueue_style('gpf-admin-css', GPF_PLUGIN_URL . 'assets/css/admin-style.css', [], GPF_VERSION);
});

/**
 * Settings page HTML
 */
function gpf_settings_page_html() {
    $merchant_id = get_option('gpf_merchant_id');
    $secret_key = get_option('gpf_secret_key');
    $token_url = get_option('gpf_token_url', 'https://ipguat.apps.net.pk/Ecommerce/api/Transaction/GetAccessToken');
    $checkout_url = get_option('gpf_checkout_url', 'https://ipguat.apps.net.pk/Ecommerce/api/Transaction/PostTransaction');
    $sandbox_mode = get_option('gpf_sandbox_mode', '1');
    $min_amount = get_option('gpf_min_amount', '100');
    $max_amount = get_option('gpf_max_amount', '500000');
    
    $is_configured = !empty($merchant_id) && !empty($secret_key);
    ?>
    <div class="wrap gpf-wrap">
        
        <!-- Header -->
        <header class="gpf-header">
            <div class="gpf-brand">
                <span class="dashicons dashicons-money-alt"></span>
                <h1>GoPayFast</h1>
            </div>
            <div class="gpf-meta">
                <span class="gpf-version">v<?php echo esc_html(GPF_VERSION); ?></span>
                <span class="gpf-status <?php echo $is_configured ? 'active' : 'inactive'; ?>">
                    <?php echo $is_configured ? 'Active' : 'Setup'; ?>
                </span>
            </div>
        </header>

        <form action="options.php" method="post" class="gpf-form">
            <?php settings_fields('gpf_settings_group'); ?>
            
            <div class="gpf-layout">
                
                <!-- Main Settings -->
                <main class="gpf-main">
                    
                    <!-- API Credentials -->
                    <section class="gpf-panel">
                        <h2>API Credentials</h2>
                        <div class="gpf-field">
                            <label for="gpf_merchant_id">Merchant ID</label>
                            <input type="text" 
                                   id="gpf_merchant_id" 
                                   name="gpf_merchant_id" 
                                   value="<?php echo esc_attr($merchant_id); ?>" 
                                   placeholder="your-merchant-id"
                                   class="regular-text" />
                        </div>
                        <div class="gpf-field">
                            <label for="gpf_secret_key">Secret Key</label>
                            <div class="gpf-input-wrap">
                                <input type="password" 
                                       id="gpf_secret_key" 
                                       name="gpf_secret_key" 
                                       value="<?php echo esc_attr($secret_key); ?>" 
                                       placeholder="••••••••••••••••"
                                       class="regular-text" />
                                <button type="button" class="gpf-toggle" data-toggle="gpf_secret_key">
                                    <span class="dashicons dashicons-visibility"></span>
                                </button>
                            </div>
                        </div>
                    </section>

                    <!-- API Endpoints -->
                    <section class="gpf-panel">
                        <h2>API Endpoints</h2>
                        <div class="gpf-field">
                            <label for="gpf_token_url">Token API URL</label>
                            <input type="url" 
                                   id="gpf_token_url" 
                                   name="gpf_token_url" 
                                   value="<?php echo esc_attr($token_url); ?>" 
                                   class="large-text" />
                        </div>
                        <div class="gpf-field">
                            <label for="gpf_checkout_url">Checkout URL</label>
                            <input type="url" 
                                   id="gpf_checkout_url" 
                                   name="gpf_checkout_url" 
                                   value="<?php echo esc_attr($checkout_url); ?>" 
                                   class="large-text" />
                        </div>
                    </section>

                    <!-- Transaction Settings -->
                    <section class="gpf-panel">
                        <h2>Transaction Settings</h2>
                        <div class="gpf-row">
                            <div class="gpf-field">
                                <label for="gpf_min_amount">Minimum (PKR)</label>
                                <input type="number" 
                                       id="gpf_min_amount" 
                                       name="gpf_min_amount" 
                                       value="<?php echo esc_attr($min_amount); ?>" 
                                       min="1"
                                       class="small-text" />
                            </div>
                            <div class="gpf-field">
                                <label for="gpf_max_amount">Maximum (PKR)</label>
                                <input type="number" 
                                       id="gpf_max_amount" 
                                       name="gpf_max_amount" 
                                       value="<?php echo esc_attr($max_amount); ?>" 
                                       min="1"
                                       class="small-text" />
                            </div>
                        </div>
                        <div class="gpf-field gpf-field-inline">
                            <label for="gpf_sandbox_mode">
                                <input type="checkbox" 
                                       id="gpf_sandbox_mode" 
                                       name="gpf_sandbox_mode" 
                                       value="1" 
                                       <?php checked($sandbox_mode, '1'); ?> />
                                Enable Sandbox Mode
                            </label>
                        </div>
                    </section>

                    <!-- Actions -->
                    <div class="gpf-actions">
                        <?php submit_button('Save Settings', 'primary gpf-btn', 'submit', false); ?>
                    </div>

                </main>

                <!-- Sidebar -->
                <aside class="gpf-aside">
                    
                    <div class="gpf-box">
                        <h3>Shortcodes</h3>
                        <div class="gpf-shortcode">
                            <code>[gpf_donation_form]</code>
                            <button type="button" class="gpf-copy" onclick="copyShortcode(this, '[gpf_donation_form]')">Copy</button>
                        </div>
                        <p>Full donation form</p>
                        
                        <div class="gpf-shortcode">
                            <code>[gpf_donation_bar]</code>
                            <button type="button" class="gpf-copy" onclick="copyShortcode(this, '[gpf_donation_bar]')">Copy</button>
                        </div>
                        <p>Compact donation bar</p>
                    </div>

                    <div class="gpf-box">
                        <h3>Support</h3>
                        <a href="https://whowaheed.com" target="_blank">Documentation</a>
                        <a href="https://whowaheed.com" target="_blank">Contact Support</a>
                    </div>

                </aside>
            </div>
        </form>

        <!-- Footer -->
        <footer class="gpf-footer">
            <p>
                GoPayFast v<?php echo esc_html(GPF_VERSION); ?> — 
                Developed by <a href="https://whowaheed.com" target="_blank">Abdul Waheed</a>
            </p>
        </footer>

    </div>

    <script>
    function copyShortcode(btn, text) {
        if (navigator.clipboard) {
            navigator.clipboard.writeText(text).then(function() {
                showCopied(btn);
            });
        } else {
            // Fallback
            var textarea = document.createElement('textarea');
            textarea.value = text;
            textarea.style.position = 'fixed';
            textarea.style.opacity = '0';
            document.body.appendChild(textarea);
            textarea.select();
            document.execCommand('copy');
            document.body.removeChild(textarea);
            showCopied(btn);
        }
    }
    
    function showCopied(btn) {
        var originalText = btn.textContent;
        btn.textContent = 'Copied!';
        btn.style.background = '#065f46';
        btn.style.color = '#fff';
        btn.style.borderColor = '#065f46';
        
        setTimeout(function() {
            btn.textContent = originalText;
            btn.style.background = '';
            btn.style.color = '';
            btn.style.borderColor = '';
        }, 2000);
    }
    
    // Password toggle
    document.addEventListener('DOMContentLoaded', function() {
        document.querySelectorAll('[data-toggle]').forEach(function(btn) {
            btn.addEventListener('click', function() {
                var target = document.getElementById(this.getAttribute('data-toggle'));
                var icon = this.querySelector('.dashicons');
                if (target.type === 'password') {
                    target.type = 'text';
                    icon.classList.remove('dashicons-visibility');
                    icon.classList.add('dashicons-hidden');
                } else {
                    target.type = 'password';
                    icon.classList.remove('dashicons-hidden');
                    icon.classList.add('dashicons-visibility');
                }
            });
        });
    });
    </script>
    <?php
}
