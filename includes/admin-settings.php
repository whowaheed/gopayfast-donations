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
    register_setting( 'gpf_settings_group', 'gpf_min_amount', ['type' => 'integer', 'default' => 10] );
    register_setting( 'gpf_settings_group', 'gpf_max_amount', ['type' => 'integer', 'default' => 500000] );
});

/**
 * Settings page HTML
 */
function gpf_settings_page_html() {
    ?>
    <div class="wrap">
        <h1>GoPayFast Settings</h1>
        <form action="options.php" method="post">
            <?php settings_fields('gpf_settings_group'); ?>
            <table class="form-table">
                <tr>
                    <th>Merchant ID</th>
                    <td><input type="text" name="gpf_merchant_id" value="<?php echo esc_attr(get_option('gpf_merchant_id')); ?>" class="regular-text" /></td>
                </tr>
                <tr>
                    <th>Secret Key</th>
                    <td><input type="password" name="gpf_secret_key" value="<?php echo esc_attr(get_option('gpf_secret_key')); ?>" class="regular-text" /></td>
                </tr>
                <tr>
                    <th>Token API URL</th>
                    <td>
                        <input type="text" name="gpf_token_url" value="<?php echo esc_attr(get_option('gpf_token_url', 'https://ipguat.apps.net.pk/Ecommerce/api/Transaction/GetAccessToken')); ?>" class="large-text" />
                    </td>
                </tr>
                <tr>
                    <th>Checkout URL</th>
                    <td>
                        <input type="text" name="gpf_checkout_url" value="<?php echo esc_attr(get_option('gpf_checkout_url', 'https://ipguat.apps.net.pk/Ecommerce/api/Transaction/PostTransaction')); ?>" class="large-text" />
                    </td>
                </tr>
                <tr>
                    <th>Minimum Amount (PKR)</th>
                    <td>
                        <input type="number" name="gpf_min_amount" value="<?php echo esc_attr(get_option('gpf_min_amount', 10)); ?>" min="1" class="small-text" />
                        <p class="description">Minimum donation amount in PKR. Default: 10</p>
                    </td>
                </tr>
                <tr>
                    <th>Maximum Amount (PKR)</th>
                    <td>
                        <input type="number" name="gpf_max_amount" value="<?php echo esc_attr(get_option('gpf_max_amount', 500000)); ?>" min="1" class="regular-text" />
                        <p class="description">Maximum donation amount in PKR. Default: 500000</p>
                    </td>
                </tr>
            </table>
            <?php submit_button(); ?>
        </form>
        
        <hr style="margin: 30px 0;">
        <h3>Security Information</h3>
        <p>Security events are logged to your server error log only. Contact your hosting provider to review logs if needed.</p>
        <p><strong>Log location:</strong> Typically <code>/var/log/apache2/error.log</code> or <code>/var/log/nginx/error.log</code></p>
    </div>
    <?php
}
