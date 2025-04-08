<?php
/**
 * PayOrc Transaction Lookup Class
 *
 * @package PayOrc
 */

if (!defined('ABSPATH')) {
    exit;
}

class PayOrc_Transaction_Lookup {
    /**
     * Constructor
     */
    public function __construct() {
        add_action('admin_menu', array($this, 'add_menu_item'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_scripts'));
        add_action('wp_ajax_payorc_lookup_transaction', array($this, 'lookup_transaction'));
    }

    /**
     * Add menu item
     */
    public function add_menu_item() {
        add_submenu_page(
            'payorc-settings',
            __('Transaction Lookup', 'payorc-payments'),
            __('Transaction Lookup', 'payorc-payments'),
            'manage_options',
            'payorc-transaction-lookup',
            array($this, 'render_page')
        );
    }

    /**
     * Enqueue scripts
     */
    public function enqueue_scripts($hook) {
        if ($hook !== 'payorc_page_payorc-transaction-lookup') {
            return;
        }

        wp_enqueue_style('payorc-admin-css', PAYORC_PLUGIN_URL . 'assets/css/admin.css', array(), PAYORC_VERSION);
        wp_enqueue_script('payorc-admin-js', PAYORC_PLUGIN_URL . 'assets/js/admin.js', array('jquery'), PAYORC_VERSION, true);
        
        wp_localize_script('payorc-admin-js', 'payorcAdmin', array(
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('payorc-admin-nonce')
        ));
    }

    /**
     * Render page
     */
    public function render_page() {
        $transactions = $this->get_payorc_transactions();
        ?>
        <div class="wrap">
            <h1><?php echo esc_html__('Transaction Lookup', 'payorc-payments'); ?></h1>
            
            <div class="payorc-lookup-container">
                <div class="table-responsive">
                    <table class="wp-list-table widefat striped">
                        <thead>
                            <tr>
                                <th>Sno</th>
                                <th>Payorc Order ID</th>
                                <th>Customer Email</th>
                                <th>ID Order</th>
                                <th>Transaction ID</th>
                                <th>Paid Amount</th>
                                <th>Status</th>
                                <th>Response</th>
                                <th>Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($transactions)) : ?>
                                <?php foreach ($transactions as $transaction) : ?>
                                    <tr>
                                        <td data-label="Sno"><?php echo esc_html($transaction['id_payorc']); ?></td>
                                        <td data-label="Payorc Order ID"><?php echo esc_html($transaction['p_order_id']); ?></td>
                                        <td data-label="Customer Email"><?php echo esc_html($transaction['customer_email']); ?></td>
                                        <td data-label="ID Order"><?php echo esc_html($transaction['id_payorc']); ?></td>
                                        <td data-label="Transaction ID"><?php echo esc_html($transaction['transaction_id']); ?></td>
                                        <td data-label="Paid Amount"><?php echo esc_html($transaction['paid_amount']); ?></td>
                                        <td data-label="Status"><?php echo esc_html($transaction['status']); ?></td>
                                        <td data-label="Response"><?php echo esc_html($transaction['response']); ?></td>
                                        <td data-label="Date"><?php echo esc_html($transaction['date_add']); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else : ?>
                                <tr>
                                    <td colspan="9" class="text-center"><?php _e('No transactions found', 'payorc-payments'); ?></td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <?php
    }

    public function get_payorc_transactions() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'payorc_transaction';
    
        $query = "SELECT * FROM $table_name GROUP BY p_order_id ORDER BY id_payorc DESC";
        $transactions = $wpdb->get_results($query, ARRAY_A);
    
        $all_transactions = [];
    
        if (!empty($transactions)) {
            foreach ($transactions as $transaction) {
                $customer = get_userdata($transaction['id_customer']);
                $all_transactions[$transaction["id_payorc"]] = array(
                    'id_payorc' => $transaction["id_payorc"],
                    'p_order_id' => $transaction["p_order_id"],
                    'customer_email' => isset($customer->user_email) ? $customer->user_email : 'N/A',
                    'id_order' => $transaction['id_order'],
                    'transaction_id' => $transaction['transaction_id'],
                    'paid_amount' => number_format((float)$transaction['amount'], 2) . ' ' . get_option('woocommerce_currency'), // Format amount with currency
                    'status' => $transaction['status'],
                    'response' => $transaction['response'],
                    'date_add' => $transaction['date_add'],
                );
            }
        }
    
        return $all_transactions;
    }

    /**
     * Lookup transaction
     */
    public function lookup_transaction() {
        check_ajax_referer('payorc-admin-nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => __('Unauthorized access', 'payorc-payments')));
            return;
        }

        $p_order_id = isset($_POST['p_order_id']) ? sanitize_text_field($_POST['p_order_id']) : '';

        if (empty($p_order_id)) {
            wp_send_json_error(array('message' => __('PayOrc Order ID is required', 'payorc-payments')));
            return;
        }

        global $wpdb;
        $transaction = $wpdb->get_row(
            $wpdb->prepare(
                "SELECT * FROM {$wpdb->prefix}payorc_transaction WHERE p_order_id = %s",
                $p_order_id
            )
        );

        if (!$transaction) {
            wp_send_json_error(array('message' => __('Transaction not found', 'payorc-payments')));
            return;
        }

        $response_data = json_decode($transaction->response, true);
        
        ob_start();
        ?>
        <table class="widefat fixed striped">
            <tbody>
                <tr>
                    <th><?php echo esc_html__('Transaction ID', 'payorc-payments'); ?></th>
                    <td><?php echo esc_html($transaction->transaction_id); ?></td>
                </tr>
                <tr>
                    <th><?php echo esc_html__('Amount', 'payorc-payments'); ?></th>
                    <td><?php echo esc_html($transaction->amount . ' ' . $transaction->currency); ?></td>
                </tr>
                <tr>
                    <th><?php echo esc_html__('Status', 'payorc-payments'); ?></th>
                    <td><?php echo esc_html($transaction->status); ?></td>
                </tr>
                <tr>
                    <th><?php echo esc_html__('Payment Method', 'payorc-payments'); ?></th>
                    <td><?php echo esc_html($transaction->source_type); ?></td>
                </tr>
                <?php if ($transaction->cc_schema): ?>
                <tr>
                    <th><?php echo esc_html__('Card Details', 'payorc-payments'); ?></th>
                    <td>
                        <?php 
                        echo esc_html(sprintf(
                            '%s %s (%s)',
                            $transaction->cc_schema,
                            $transaction->cc_type,
                            $transaction->cc_mask
                        )); 
                        ?>
                    </td>
                </tr>
                <?php endif; ?>
                <tr>
                    <th><?php echo esc_html__('Date', 'payorc-payments'); ?></th>
                    <td><?php echo esc_html($transaction->date_add); ?></td>
                </tr>
                <tr>
                    <th><?php echo esc_html__('Mode', 'payorc-payments'); ?></th>
                    <td><?php echo esc_html(ucfirst($transaction->mode)); ?></td>
                </tr>
                <tr>
                    <th><?php echo esc_html__('Full Response', 'payorc-payments'); ?></th>
                    <td>
                        <pre><?php echo esc_html(json_encode($response_data, JSON_PRETTY_PRINT)); ?></pre>
                    </td>
                </tr>
            </tbody>
        </table>
        <?php
        $html = ob_get_clean();

        wp_send_json_success(array('html' => $html));
    }
}