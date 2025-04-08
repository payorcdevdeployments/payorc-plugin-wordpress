<?php
/**
 * Main PayOrc Gateway Class
 *
 * @package PayOrc
 */

if (!defined('ABSPATH')) {
    exit;
}

class PayOrc_Gateway {
    /**
     * The single instance of the class
     */
    protected static $_instance = null;

    /**
     * Countries array for phone codes
     */
    protected $countries = ["AW"=>"297","AF"=>"93","AO"=>"244","AI"=>"1-264","AX"=>"358-18","AL"=>"355","AD"=>"376","AR"=>"54","AM"=>"374","AS"=>"1-684","AQ"=>"672","TF"=>"262","AG"=>"1-268","AU"=>"61","AT"=>"43","AZ"=>"994","BI"=>"257","BE"=>"32","BJ"=>"229","BQ"=>"599","BF"=>"226","BD"=>"880","BG"=>"359","BH"=>"973","BS"=>"1-242","BA"=>"387","BL"=>"590","BY"=>"375","BZ"=>"501","BM"=>"1-441","BO"=>"591","BR"=>"55","BB"=>"1-246","BN"=>"673","BT"=>"975","BV"=>"47","BW"=>"267","CF"=>"236","CA"=>"1","CC"=>"61","CH"=>"41","CL"=>"56","CN"=>"86","CI"=>"225","CM"=>"237","CD"=>"243","CG"=>"242","CK"=>"682","CO"=>"57","KM"=>"269","CV"=>"238","CR"=>"506","CU"=>"53","CW"=>"599","CX"=>"61","KY"=>"1-345","CY"=>"357","CZ"=>"420","DE"=>"49","DJ"=>"253","DM"=>"1-767","DK"=>"45","DO"=>"1-809","DZ"=>"213","EC"=>"593","EG"=>"20","ER"=>"291","EH"=>"212","ES"=>"34","EE"=>"372","ET"=>"251","FI"=>"358","FJ"=>"679","FK"=>"500","FR"=>"33","FO"=>"298","FM"=>"691","GA"=>"241","GB"=>"44","GE"=>"995","GG"=>"44","GH"=>"233","GI"=>"350","GN"=>"224","GP"=>"590","GM"=>"220","GW"=>"245","GQ"=>"240","GR"=>"30","GD"=>"1-473","GL"=>"299","GT"=>"502","GF"=>"594","GU"=>"1-671","GY"=>"592","HK"=>"852","HM"=>"61","HN"=>"504","HR"=>"385","HT"=>"509","HU"=>"36","ID"=>"62","IM"=>"44","IN"=>"91","IO"=>"246","IE"=>"353","IR"=>"98","IQ"=>"964","IS"=>"354","IL"=>"972","IT"=>"39","JM"=>"1-876","JE"=>"44","JO"=>"962","JP"=>"81","KZ"=>"7","KE"=>"254","KG"=>"996","KH"=>"855","KI"=>"686","KN"=>"1-869","KR"=>"82","KW"=>"965","LA"=>"856","LB"=>"961","LR"=>"231","LY"=>"218","LC"=>"1-758","LI"=>"423","LK"=>"94","LS"=>"266","LT"=>"370","LU"=>"352","LV"=>"371","MO"=>"853","MF"=>"590","MA"=>"212","MC"=>"377","MD"=>"373","MG"=>"261","MV"=>"960","MX"=>"52","MH"=>"692","MK"=>"389","ML"=>"223","MT"=>"356","MM"=>"95","ME"=>"382","MN"=>"976","MP"=>"1-670","MZ"=>"258","MR"=>"222","MS"=>"1-664","MQ"=>"596","MU"=>"230","MW"=>"265","MY"=>"60","YT"=>"262","NA"=>"264","NC"=>"687","NE"=>"227","NF"=>"672","NG"=>"234","NI"=>"505","NU"=>"683","NL"=>"31","NO"=>"47","NP"=>"977","NR"=>"674","NZ"=>"64","OM"=>"968","PK"=>"92","PA"=>"507","PN"=>"64","PE"=>"51","PH"=>"63","PW"=>"680","PG"=>"675","PL"=>"48","PR"=>"1-787","KP"=>"850","PT"=>"351","PY"=>"595","PS"=>"970","PF"=>"689","QA"=>"974","RE"=>"262","RO"=>"40","RU"=>"7","RW"=>"250","SA"=>"966","SD"=>"249","SN"=>"221","SG"=>"65","GS"=>"500","SH"=>"290","SJ"=>"47","SB"=>"677","SL"=>"232","SV"=>"503","SM"=>"378","SO"=>"252","PM"=>"508","RS"=>"381","SS"=>"211","ST"=>"239","SR"=>"597","SK"=>"421","SI"=>"386","SE"=>"46","SZ"=>"268","SX"=>"1-721","SC"=>"248","SY"=>"963","TC"=>"1-649","TD"=>"235","TG"=>"228","TH"=>"66","TJ"=>"992","TK"=>"690","TM"=>"993","TL"=>"670","TO"=>"676","TT"=>"1-868","TN"=>"216","TR"=>"90","TV"=>"688","TW"=>"886","TZ"=>"255","UG"=>"256","UA"=>"380","UM"=>"1","UY"=>"598","US"=>"1","UZ"=>"998","VA"=>"379","VC"=>"1-784","VE"=>"58","VG"=>"1-284","VI"=>"1-340","VN"=>"84","VU"=>"678","WF"=>"681","WS"=>"685","YE"=>"967","ZA"=>"27","ZM"=>"260","ZW"=>"263"];

    /**
     * Main PayOrc Gateway Instance
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
        $this->init_hooks();
        $this->register_shortcodes();
        $this->create_tables();
    }

    /**
     * Initialize hooks
     */
    private function init_hooks() {
        add_action('wp_enqueue_scripts', array($this, 'enqueue_scripts'));
        add_action('wp_ajax_payorc_process_payment', array($this, 'process_payment'));
        add_action('wp_ajax_nopriv_payorc_process_payment', array($this, 'process_payment'));
        add_action('wp_ajax_payorc_save_transaction', array($this, 'save_transaction'));
        add_action('wp_ajax_nopriv_payorc_save_transaction', array($this, 'save_transaction'));
        add_action('init', array($this, 'handle_hosted_response'));
    }

    /**
     * Create necessary tables
     */
    private function create_tables() {
        global $wpdb;
        $charset_collate = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE IF NOT EXISTS {$wpdb->prefix}payorc_transaction (
            id_payorc int(11) NOT NULL AUTO_INCREMENT,
            type enum('payment','refund') NOT NULL DEFAULT 'payment',
            source_type varchar(16) NOT NULL DEFAULT 'card',
            p_request_id varchar(100) DEFAULT NULL,
            m_payment_token varchar(120) DEFAULT NULL,
            p_order_id varchar(100) DEFAULT NULL,
            id_customer int(10) DEFAULT NULL,
            id_cart int(10) DEFAULT NULL,
            id_order int(10) DEFAULT NULL,
            transaction_id varchar(32) DEFAULT NULL,
            amount float(20,6) DEFAULT NULL,
            status varchar(32) NOT NULL DEFAULT 'pending',
            response text DEFAULT NULL,
            currency varchar(3) DEFAULT NULL,
            cc_schema varchar(16) DEFAULT NULL,
            cc_type varchar(16) DEFAULT NULL,
            cc_mask varchar(30) DEFAULT NULL,
            mode enum('live','test') DEFAULT NULL,
            date_add datetime DEFAULT NULL,
            PRIMARY KEY (id_payorc)
        ) $charset_collate;";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }

    /**
     * Save transaction to database
     */
    public function save_transaction() {
        check_ajax_referer('payorc-nonce', 'nonce');

        global $wpdb;
        $transaction = isset($_POST['transaction']) ? $_POST['transaction'] : null;

        if (!$transaction) {
            wp_send_json_error(array('message' => 'No transaction data provided'));
            return;
        }

        // For iframe responses, data comes as JSON
        if (is_string($transaction)) {
            $transaction = json_decode($transaction, true);
        }

        $data = array(
            'type' => 'payment',
            'source_type' => isset($transaction['payment_method_data']['scheme']) ? 'card' : 'other',
            'p_request_id' => $transaction['p_request_id'],
            'm_payment_token' => $transaction['m_payment_token'],
            'p_order_id' => $transaction['p_order_id'],
            'id_order' => $transaction['m_order_id'],
            'transaction_id' => $transaction['transaction_id'],
            'amount' => $transaction['amount'],
            'status' => $transaction['status'],
            'response' => json_encode($transaction),
            'currency' => $transaction['currency'],
            'cc_schema' => isset($transaction['payment_method_data']['scheme']) ? $transaction['payment_method_data']['scheme'] : null,
            'cc_type' => isset($transaction['payment_method_data']['card_type']) ? $transaction['payment_method_data']['card_type'] : null,
            'cc_mask' => isset($transaction['payment_method_data']['mask_card_number']) ? $transaction['payment_method_data']['mask_card_number'] : null,
            'mode' => get_option('payorc_test_mode') === 'yes' ? 'test' : 'live',
            'date_add' => current_time('mysql')
        );

        $result = $wpdb->insert(
            $wpdb->prefix . 'payorc_transaction',
            $data,
            array(
                '%s', '%s', '%s', '%s', '%s', '%s', '%d', '%d', '%s', '%s',
                '%f', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s'
            )
        );

        if ($result === false) {
            wp_send_json_error(array('message' => 'Failed to save transaction'));
            return;
        }

        wp_send_json_success(array('message' => 'Transaction saved successfully'));
    }

    /**
     * Handle hosted checkout response
     */
    public function handle_hosted_response() {
        
        if (!empty($_POST) && isset($_POST['status'])) {
            global $wpdb;
            $transaction  = $_POST;
    
            $data = array(
                'type' => 'payment',
                'source_type' => isset($transaction['payment_method_data']['scheme']) ? 'card' : 'other',
                'p_request_id' => $transaction['p_request_id'],
                'm_payment_token' => $transaction['m_payment_token'],
                'p_order_id' => $transaction['p_order_id'],
                'id_order' => $transaction['m_order_id'],
                'transaction_id' => $transaction['transaction_id'],
                'amount' => $transaction['amount'],
                'status' => $transaction['status'],
                'response' => json_encode($transaction),
                'currency' => $transaction['currency'],
                'cc_schema' => isset($transaction['payment_method_data']['scheme']) ? $transaction['payment_method_data']['scheme'] : null,
                'cc_type' => isset($transaction['payment_method_data']['card_type']) ? $transaction['payment_method_data']['card_type'] : null,
                'cc_mask' => isset($transaction['payment_method_data']['mask_card_number']) ? $transaction['payment_method_data']['mask_card_number'] : null,
                'mode' => get_option('payorc_test_mode') === 'yes' ? 'test' : 'live',
                'date_add' => current_time('mysql')
            );
    
            $result = $wpdb->insert(
                $wpdb->prefix . 'payorc_transaction',
                $data,
                array(
                    '%s', '%s', '%s', '%s', '%s', '%s', '%d', '%d', '%s', '%s',
                    '%f', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s'
                )
            );
    
            if ($result === false) {
                error_log('Failed to save transaction', print_r($_POST, 1));
                return;
            }    

            $return_url = isset($_POST['return_url']) ? $_POST['return_url'] : home_url();
            wp_redirect($return_url);
            exit;
        }
    }

    /**
     * Register shortcodes
     */
    private function register_shortcodes() {
        add_shortcode('payorc_payment', array($this, 'payment_button_shortcode'));
    }

    /**
     * Get browser information
     */
    private function get_browser_info() {
        $u_agent = $_SERVER['HTTP_USER_AGENT'];
        $bname = 'Unknown';
        $platform = 'Unknown';
        $version = "";

        // Platform
        if (preg_match('/linux/i', $u_agent)) {
            $platform = 'Linux';
        } elseif (preg_match('/macintosh|mac os x/i', $u_agent)) {
            $platform = 'Mac';
        } elseif (preg_match('/windows|win32/i', $u_agent)) {
            $platform = 'Windows';
        }

        // Browser
        if(preg_match('/MSIE/i',$u_agent) && !preg_match('/Opera/i',$u_agent)) {
            $bname = 'Internet Explorer';
            $ub = "MSIE";
        } elseif(preg_match('/Firefox/i',$u_agent)) {
            $bname = 'Firefox';
            $ub = "Firefox";
        } elseif(preg_match('/Chrome/i',$u_agent)) {
            $bname = 'Chrome';
            $ub = "Chrome";
        } elseif(preg_match('/Safari/i',$u_agent)) {
            $bname = 'Safari';
            $ub = "Safari";
        } elseif(preg_match('/Opera/i',$u_agent)) {
            $bname = 'Opera';
            $ub = "Opera";
        }

        // Version
        $known = array('Version', $ub, 'other');
        $pattern = '#(?<browser>' . join('|', $known) . ')[/ ]+(?<version>[0-9.|a-zA-Z.]*)#';
        if (!preg_match_all($pattern, $u_agent, $matches)) {
            $version = "";
        } else {
            $version = $matches['version'][0];
        }

        return array(
            'platform' => $platform,
            'browser' => $bname,
            'version' => $version
        );
    }

    /**
     * Payment button shortcode
     */
    public function payment_button_shortcode($atts) {
        $atts = shortcode_atts(array(
            'amount' => '0',
            'currency' => 'AED',
            'button_text' => __('Pay Now', 'payorc-payments'),
            'button_class' => 'payorc-payment-button button',
            'success_url' => get_option('payorc_return_url', home_url()),
            'cancel_url' => get_option('payorc_return_url', home_url()),
            'failure_url' => get_option('payorc_return_url', home_url()),
            'customer_email' => '',
            'customer_name' => '',
            'customer_phone' => '',
            'customer_address_line1' => '',
            'customer_address_line2' => '',
            'customer_city' => '',
            'customer_province' => '',
            'customer_pin' => '',
            'customer_country' => '',
            'order_id' => wp_rand(1000, 9999),
            'description' => '',
            'payment_method' => ''
        ), $atts, 'payorc_payment');

        // Ensure amount is valid
        $amount = floatval($atts['amount']);
        if ($amount <= 0) {
            return '<p class="payorc-error">' . __('Invalid amount specified', 'payorc-payments') . '</p>';
        }

        // Get country code for phone
        $country_code = '';
        if (!empty($atts['customer_country'])) {
            $country_code = isset($this->countries[$atts['customer_country']]) ? $this->countries[$atts['customer_country']] : '';
        }

        // Generate unique button ID
        $button_id = 'payorc-btn-' .date("YmdHi"). '-' . wp_rand();

        // Build button HTML with all necessary data attributes
        $button = sprintf(
            '<button id="%s" class="%s" 
                data-amount="%s" 
                data-currency="%s" 
                data-order-id="%s"
                data-description="%s"
                data-success-url="%s" 
                data-cancel-url="%s" 
                data-failure-url="%s" 
                data-customer-email="%s" 
                data-customer-name="%s" 
                data-customer-phone="%s" 
                data-country-code="%s"
                data-address-line1="%s"
                data-address-line2="%s"
                data-city="%s"
                data-province="%s"
                data-pin="%s"
                data-country="%s" 
                data-payment-method="%s">%s</button>',
            esc_attr($button_id),
            esc_attr($atts['button_class']),
            esc_attr($amount),
            esc_attr($atts['currency']),
            esc_attr($atts['order_id']),
            esc_attr($atts['description']),
            esc_url($atts['success_url']),
            esc_url($atts['cancel_url']),
            esc_url($atts['failure_url']),
            esc_attr($atts['customer_email']),
            esc_attr($atts['customer_name']),
            esc_attr($atts['customer_phone']),
            esc_attr($country_code),
            esc_attr($atts['customer_address_line1']),
            esc_attr($atts['customer_address_line2']),
            esc_attr($atts['customer_city']),
            esc_attr($atts['customer_province']),
            esc_attr($atts['customer_pin']),
            esc_attr($atts['customer_country']),
            esc_attr($atts['payment_method']),
            esc_html($atts['button_text'])
        );

        return $button;
    }

    /**
     * Process payment
     */
    public function process_payment() {
        check_ajax_referer('payorc-nonce', 'nonce');

        $amount = isset($_POST['amount']) ? floatval($_POST['amount']) : 0;
        $currency = isset($_POST['currency']) ? sanitize_text_field($_POST['currency']) : 'USD';
        
        if ($amount <= 0) {
            wp_send_json_error(array('message' => __('Invalid amount', 'payorc-payments')));
            return;
        }

        // Get browser info
        $browser_info = $this->get_browser_info();

        // Get current API URL based on test mode
        $api_url = payorc_get_api_url();

        $test_mode = get_option('payorc_test_mode') === 'yes';
        $merchant_key = $test_mode ? get_option('payorc_test_merchant_key') : get_option('payorc_live_merchant_key');
        $merchant_secret = $test_mode ? get_option('payorc_test_merchant_secret') : get_option('payorc_live_merchant_secret');

        // Prepare the request data according to the specified format
        $request_data = array(
            'data' => array(
                'class' => 'ECOM',
                'action' => get_option('payorc_action_type', 'AUTH'),
                'capture_method' => get_option('payorc_capture_method', 'MANUAL'),
                'payment_token' => '',
                'order_details' => array(
                    'm_order_id' => sanitize_text_field($_POST['order_id']),
                    'amount' => number_format($amount, 2, '.', ''),
                    'convenience_fee' => '0',
                    'currency' => $currency,
                    'description' => sanitize_text_field($_POST['description']),
                    'quantity' => '1'
                ),
                'customer_details' => array(
                    'name' => sanitize_text_field($_POST['customer_name']),
                    'm_customer_id' => '1',
                    'email' => sanitize_email($_POST['customer_email']),
                    'mobile' => sanitize_text_field($_POST['customer_phone']),
                    'code' => sanitize_text_field($_POST['country_code'])
                ),
                'billing_details' => array(
                    'address_line1' => sanitize_text_field($_POST['address_line1']),
                    'address_line2' => sanitize_text_field($_POST['address_line2']),
                    'city' => sanitize_text_field($_POST['city']),
                    'province' => sanitize_text_field($_POST['province']),
                    'pin' => sanitize_text_field($_POST['pin']),
                    'country' => sanitize_text_field($_POST['country'])
                ),
                'shipping_details' => array(
                    'shipping_name' => sanitize_text_field($_POST['customer_name']),
                    'shipping_email' => sanitize_email($_POST['customer_email']),
                    'shipping_code' => sanitize_text_field($_POST['country_code']),
                    'shipping_mobile' => sanitize_text_field($_POST['customer_phone']),
                    'address_line1' => sanitize_text_field($_POST['address_line1']),
                    'address_line2' => sanitize_text_field($_POST['address_line2']),
                    'city' => sanitize_text_field($_POST['city']),
                    'province' => sanitize_text_field($_POST['province']),
                    'pin' => sanitize_text_field($_POST['pin']),
                    'country' => sanitize_text_field($_POST['country']),
                    'location_pin' => '',
                    'shipping_currency' => $currency,
                    'shipping_amount' => '0'
                ),
                'urls' => array(
                    'success' => esc_url($_POST['success_url']),
                    'cancel' => esc_url($_POST['cancel_url']),
                    'failure' => esc_url($_POST['failure_url'])
                ),
                'parameters' => array(
                    array('alpha' => ''),
                    array('beta' => ''),
                    array('gamma' => ''),
                    array('delta' => ''),
                    array('epsilon' => '')
                ),
                'custom_data' => array(
                    array('alpha' => ''),
                    array('beta' => ''),
                    array('gamma' => ''),
                    array('delta' => ''),
                    array('epsilon' => '')
                )
            )
        );

        $response = wp_remote_post($api_url . '/sdk/orders/create', array(
            'headers' => array(
                'Content-Type' => 'application/json',
                'merchant-key' => $merchant_key,
                'merchant-secret' => $merchant_secret,
                'platform' => 'WordPress',
                'browser' => $browser_info['browser'],
                'browser-version' => $browser_info['version']
            ),
            'body' => json_encode($request_data)
        ));

        if (is_wp_error($response)) {
            wp_send_json_error(array('message' => $response->get_error_message()));
            return;
        }

        $body = json_decode(wp_remote_retrieve_body($response), true);

        if ($body['status'] !== 'SUCCESS' || $body['status_code'] !== '00') {
            wp_send_json_error(array(
                'message' => isset($body['message']) ? $body['message'] : __('Unknown error', 'payorc-payments')
            ));
            return;
        }

        wp_send_json_success(array(
            'payment_id' => $body['payment_id'],
            'checkout_mode' => get_option('payorc_checkout_mode', 'iframe'),
            'payment_link' => $body['payment_link'],
            'iframe_link' => $body['iframe_link']
        ));
    }

    /**
     * Enqueue scripts
     */
    public function enqueue_scripts() {
        wp_enqueue_script(
            'payorc-js',
            PAYORC_PLUGIN_URL . 'assets/js/payorc.js',
            array('jquery'),
            PAYORC_VERSION,
            true
        );

        wp_localize_script(
            'payorc-js',
            'payorc_params',
            array(
                'ajax_url' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('payorc-nonce'),
                'checkout_mode' => get_option('payorc_checkout_mode', 'iframe'),
                'return_url' => get_option('payorc_return_url', home_url())
            )
        );
    }
}