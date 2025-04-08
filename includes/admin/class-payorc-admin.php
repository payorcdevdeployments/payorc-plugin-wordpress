<?php
/**
 * PayOrc Admin Class
 *
 * @package PayOrc
 */

if (!defined('ABSPATH')) {
    exit;
}

class PayOrc_Admin {
    /**
     * The single instance of the class
     */
    protected static $_instance = null;

    /**
     * Main PayOrc Admin Instance
     */
    public static function get_instance() {
        if (is_null(self::$_instance)) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    /**
     * Constructor
     */
    public function __construct() {
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('admin_init', array($this, 'register_settings'));
        add_action('admin_notices', array($this, 'show_admin_notices'));
        add_action('update_option_payorc_test_mode', array($this, 'handle_test_mode_change'), 10, 2);
    }

    /**
     * Add admin menu
     */
    public function add_admin_menu() {
        add_menu_page(
            __('PayOrc Settings', 'payorc-payments'),
            __('PayOrc', 'payorc-payments'),
            'manage_options',
            'payorc-settings',
            array($this, 'settings_page'),
            'dashicons-money',
            55
        );
    }

    /**
     * Register settings
     */
    public function register_settings() {
        register_setting('payorc_settings', 'payorc_test_mode', array(
            'type' => 'string',
            'sanitize_callback' => array($this, 'sanitize_test_mode')
        ));
        register_setting('payorc_settings', 'payorc_test_merchant_key');
        register_setting('payorc_settings', 'payorc_test_merchant_secret');
        register_setting('payorc_settings', 'payorc_live_merchant_key');
        register_setting('payorc_settings', 'payorc_live_merchant_secret');
        /* register_setting('payorc_settings', 'payorc_checkout_mode'); */
        register_setting('payorc_settings', 'payorc_action_type');
        register_setting('payorc_settings', 'payorc_capture_method');
        register_setting('payorc_settings', 'payorc_return_url');
    }

    /**
     * Sanitize test mode value
     */
    public function sanitize_test_mode($value) {
        return $value === 'yes' ? 'yes' : 'no';
    }

    /**
     * Handle test mode change
     */
    public function handle_test_mode_change($old_value, $new_value) {
        // Force refresh of API URL when test mode changes
        payorc_get_api_url(true);
    }

    /**
     * Show admin notices
     */
    public function show_admin_notices() {
        if (isset($_GET['settings-updated']) && $_GET['settings-updated']) {
            if ($this->verify_credentials()) {
                add_settings_error(
                    'payorc_messages',
                    'payorc_message',
                    __('Settings saved and credentials verified successfully.', 'payorc-payments'),
                    'success'
                );
            } else {
                add_settings_error(
                    'payorc_messages',
                    'payorc_message',
                    __('Settings saved but credential verification failed. Please check your Merchant Key and Secret.', 'payorc-payments'),
                    'error'
                );
            }
            settings_errors('payorc_messages');
        }
    }

    /**
     * Settings page
     */
    public function settings_page() {
        // Get current mode for displaying appropriate fields
        $test_mode = get_option('payorc_test_mode') === 'yes';
        $api_url = payorc_get_api_url();
        ?>  
        <div class="wrap">
            <h1><?php echo esc_html__('PayOrc Settings', 'payorc-payments'); ?></h1>
            
            <form method="post" action="options.php">
                <?php
                settings_fields('payorc_settings');
                do_settings_sections('payorc_settings');
                ?>
                <table class="form-table">
                    <tr>
                        <th scope="row"><?php echo esc_html__('Test Mode', 'payorc-payments'); ?></th>
                        <td>
                            <label>
                                <input type="checkbox" name="payorc_test_mode" value="yes" <?php checked($test_mode, true); ?> />
                                <?php echo esc_html__('Enable Test Mode', 'payorc-payments'); ?>
                            </label>
                            <p class="description">
                                <?php echo esc_html__('Check this box to use test credentials instead of live credentials.', 'payorc-payments'); ?><br>
                            </p>
                        </td>
                    </tr>

                    <tr>
                        <th scope="row"><?php echo esc_html__('Test Merchant Key', 'payorc-payments'); ?></th>
                        <td>
                            <input type="text" name="payorc_test_merchant_key" value="<?php echo esc_attr(get_option('payorc_test_merchant_key')); ?>" class="regular-text" <?php echo !$test_mode ? 'disabled' : ''; ?> />
                            <p class="description"><?php echo esc_html__('Enter your Test Merchant Key from PayOrc.', 'payorc-payments'); ?></p>
                        </td>
                    </tr>

                    <tr>
                        <th scope="row"><?php echo esc_html__('Test Merchant Secret', 'payorc-payments'); ?></th>
                        <td>
                            <input type="password" name="payorc_test_merchant_secret" value="<?php echo esc_attr(get_option('payorc_test_merchant_secret')); ?>" class="regular-text" <?php echo !$test_mode ? 'disabled' : ''; ?> />
                            <p class="description"><?php echo esc_html__('Enter your Test Merchant Secret from PayOrc.', 'payorc-payments'); ?></p>
                        </td>
                    </tr>

                    <tr>
                        <th scope="row"><?php echo esc_html__('Live Merchant Key', 'payorc-payments'); ?></th>
                        <td>
                            <input type="text" name="payorc_live_merchant_key" value="<?php echo esc_attr(get_option('payorc_live_merchant_key')); ?>" class="regular-text" <?php echo $test_mode ? 'disabled' : ''; ?> />
                            <p class="description"><?php echo esc_html__('Enter your Live Merchant Key from PayOrc.', 'payorc-payments'); ?></p>
                        </td>
                    </tr>

                    <tr>
                        <th scope="row"><?php echo esc_html__('Live Merchant Secret', 'payorc-payments'); ?></th>
                        <td>
                            <input type="password" name="payorc_live_merchant_secret" value="<?php echo esc_attr(get_option('payorc_live_merchant_secret')); ?>" class="regular-text" <?php echo $test_mode ? 'disabled' : ''; ?> />
                            <p class="description"><?php echo esc_html__('Enter your Live Merchant Secret from PayOrc.', 'payorc-payments'); ?></p>
                        </td>
                    </tr>

                    <!-- <tr>
                        <th scope="row"><?php echo esc_html__('Checkout Mode', 'payorc-payments'); ?></th>
                        <td>
                            <select name="payorc_checkout_mode">
                                <option value="iframe" <?php selected(get_option('payorc_checkout_mode'), 'iframe'); ?>><?php echo esc_html__('iFrame', 'payorc-payments'); ?></option>
                                <option value="hosted" <?php selected(get_option('payorc_checkout_mode'), 'hosted'); ?>><?php echo esc_html__('Hosted Checkout', 'payorc-payments'); ?></option>
                            </select>
                            <p class="description"><?php echo esc_html__('Choose how the payment form should be displayed.', 'payorc-payments'); ?></p>
                        </td>
                    </tr> -->

                    <tr>
                        <th scope="row"><?php echo esc_html__('Action Type', 'payorc-payments'); ?></th>
                        <td>
                            <select name="payorc_action_type">
                                <option value="SALE" <?php selected(get_option('payorc_action_type'), 'SALE'); ?>><?php echo esc_html__('SALE', 'payorc-payments'); ?></option>
                                <option value="AUTH" <?php selected(get_option('payorc_action_type'), 'AUTH'); ?>><?php echo esc_html__('AUTH', 'payorc-payments'); ?></option>
                            </select>
                            <p class="description"><?php echo esc_html__('Choose the payment action type.', 'payorc-payments'); ?></p>
                        </td>
                    </tr>

                    <tr>
                        <th scope="row"><?php echo esc_html__('Capture Method', 'payorc-payments'); ?></th>
                        <td>
                            <select name="payorc_capture_method">
                                <option value="AUTOMATIC" <?php selected(get_option('payorc_capture_method'), 'AUTOMATIC'); ?>><?php echo esc_html__('AUTOMATIC', 'payorc-payments'); ?></option>
                                <option value="MANUAL" <?php selected(get_option('payorc_capture_method'), 'MANUAL'); ?>><?php echo esc_html__('MANUAL', 'payorc-payments'); ?></option>
                            </select>
                            <p class="description"><?php echo esc_html__('Choose the capture method for payments.', 'payorc-payments'); ?></p>
                        </td>
                    </tr>

                    <tr>
                        <th scope="row"><?php echo esc_html__('Default Return URL', 'payorc-payments'); ?></th>
                        <td>
                            <input type="url" name="payorc_return_url" value="<?php echo esc_attr(get_option('payorc_return_url', home_url())); ?>" class="regular-text" />
                            <p class="description"><?php echo esc_html__('Default URL to return to after payment (can be overridden in shortcode).', 'payorc-payments'); ?></p>
                        </td>
                    </tr>
                </table>

                <?php submit_button(); ?>
            </form>

            <div class="payorc-shortcode-info">
                <h2><?php echo esc_html__('Shortcode Usage', 'payorc-payments'); ?></h2>
                <p><?php echo esc_html__('Use the following shortcode to add a payment button:', 'payorc-payments'); ?></p>
                <code>[payorc_payment amount="100" currency="AED" button_text="Pay Now" success_url="" cancel_url="" failure_url="" customer_email="" customer_name="" customer_phone="" customer_address_line1="" customer_address_line2="" customer_city="" customer_province="" customer_pin="" customer_country="" parameter_alpha parameter_beta="" parameter_gamma="" parameter_delta="" parameter_epsilon="" custom_alpha="" custom_beta="" custom_gamma="" custom_delta="" custom_epsilon=""]</code>
                
                <h3><?php echo esc_html__('Shortcode Parameters', 'payorc-payments'); ?></h3>
                <ul>
                    <li><code>amount</code> - <?php echo esc_html__('Payment amount (required)', 'payorc-payments'); ?></li>
                    <li><code>currency</code> - <?php echo esc_html__('Payment currency (default: USD)', 'payorc-payments'); ?></li>
                    <li><code>button_text</code> - <?php echo esc_html__('Custom button text', 'payorc-payments'); ?></li>
                    <li><code>success_url</code> - <?php echo esc_html__('URL to redirect after successful payment', 'payorc-payments'); ?></li>
                    <li><code>cancel_url</code> - <?php echo esc_html__('URL to redirect after cancelled payment', 'payorc-payments'); ?></li>
                    <li><code>failure_url</code> - <?php echo esc_html__('URL to redirect after failed payment', 'payorc-payments'); ?></li>
                    <li><code>customer_email</code> - <?php echo esc_html__('Customer email address', 'payorc-payments'); ?></li>
                    <li><code>customer_name</code> - <?php echo esc_html__('Customer name', 'payorc-payments'); ?></li>
                    <li><code>customer_phone</code> - <?php echo esc_html__('Customer phone number', 'payorc-payments'); ?></li>
                    <li><code>customer_address_line1</code> - <?php echo esc_html__('Customer address line 1', 'payorc-payments'); ?></li>
                    <li><code>customer_address_line2</code> - <?php echo esc_html__('Customer address line 2', 'payorc-payments'); ?></li>
                    <li><code>customer_city</code> - <?php echo esc_html__('Customer city', 'payorc-payments'); ?></li>
                    <li><code>customer_province</code> - <?php echo esc_html__('Customer province/state', 'payorc-payments'); ?></li>
                    <li><code>customer_pin</code> - <?php echo esc_html__('Customer PIN/ZIP code', 'payorc-payments'); ?></li>
                    <li><code>customer_country</code> - <?php echo esc_html__('Customer country code (e.g., US, GB)', 'payorc-payments'); ?></li>
                </ul>
            </div>
        </div>
        <?php
    }

    /**
     * Verify credentials
     */
    private function verify_credentials() {
        $test_mode = get_option('payorc_test_mode') === 'yes';
        $merchant_key = $test_mode ? get_option('payorc_test_merchant_key') : get_option('payorc_live_merchant_key');
        $merchant_secret = $test_mode ? get_option('payorc_test_merchant_secret') : get_option('payorc_live_merchant_secret');
        $api_url = payorc_get_api_url();

        if (empty($merchant_key) || empty($merchant_secret)) {
            return false;
        }

        $validation_data = array(
            'merchant_key' => $merchant_key,
            'merchant_secret' => $merchant_secret,
            'env' => $test_mode ? 'test' : 'live'
        );

        $response = wp_remote_post($api_url . '/check/keys-secret', array(
            'headers' => array(
                'Content-Type' => 'application/json'
            ),
            'body' => json_encode($validation_data)
        ));

        if (is_wp_error($response)) {
            return false;
        }

        $body = json_decode(wp_remote_retrieve_body($response), true);
        return isset($body['status']) && $body['status'] === 'success' && $body['code'] === '00';
    }
}