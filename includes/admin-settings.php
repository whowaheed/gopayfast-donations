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
