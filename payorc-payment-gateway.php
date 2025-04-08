<?php
/**
 * Plugin Name: PayOrc Payment Gateway
 * Plugin URI: https://payorc.com
 * Description: Accept payments through PayOrc payment gateway
 * Version: 1.0.0
 * Author: PayOrc
 * Author URI: https://payorc.com
 * Text Domain: payorc-payments
 * Domain Path: /languages
 * Requires at least: 5.0
 * Requires PHP: 7.2
 *
 * @package PayOrc
 */

if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
define('PAYORC_PLUGIN_FILE', __FILE__);
define('PAYORC_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('PAYORC_PLUGIN_URL', plugin_dir_url(__FILE__));
define('PAYORC_VERSION', '1.0.0');

/**
 * Get the API URL based on test mode
 */
function payorc_get_api_url() {
    $test_mode = get_option('payorc_test_mode') === 'yes';
    return $test_mode 
        ? 'https://nodeserver.payorc.com/api/v1'
        : 'https://nodeserver.payorc.com/api/v1';
}

define('PAYORC_API_URL', payorc_get_api_url());

// Include required files
require_once PAYORC_PLUGIN_DIR . 'includes/class-payorc-gateway.php';
require_once PAYORC_PLUGIN_DIR . 'includes/admin/class-payorc-admin.php';
require_once PAYORC_PLUGIN_DIR . 'includes/admin/class-payorc-transaction-lookup.php';

/**
 * Initialize the plugin
 */
function payorc_init() {
    // Load plugin text domain
    load_plugin_textdomain('payorc-payments', false, dirname(plugin_basename(__FILE__)) . '/languages');
    
    // Initialize the gateway
    PayOrc_Gateway::get_instance();
    
    // Initialize admin if in admin area
    if (is_admin()) {
        PayOrc_Admin::get_instance();
        new PayOrc_Transaction_Lookup();
    }
}
add_action('plugins_loaded', 'payorc_init');

/**
 * Add plugin action links
 */
function payorc_plugin_action_links($links) {
    $plugin_links = array(
        '<a href="' . admin_url('admin.php?page=payorc-settings') . '">' . __('Settings', 'payorc-payments') . '</a>'
    );
    return array_merge($plugin_links, $links);
}
add_filter('plugin_action_links_' . plugin_basename(__FILE__), 'payorc_plugin_action_links');

/**
 * Update API URL when test mode is toggled
 */
function payorc_update_api_url() {
    define('PAYORC_API_URL', payorc_get_api_url());
}
add_action('update_option_payorc_test_mode', 'payorc_update_api_url');