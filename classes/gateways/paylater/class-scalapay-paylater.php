<?php
class WC_Scalapay_Gateway_Later extends WC_Payment_Gateway {
    public $api_request_handler;
    public $api_callback;
    public $specific_currencies;
    public $log_enabled;

    public function __construct() {
        try {
            $this->id = 'wc-scalapay-paylater';
            $this->method_title = __('Scalapay', 'wc-scalapay-gateway');
            $this->has_fields = false;
            $this->supports = array('products', 'refunds' );
            $payment_tabs = __('Allow Scalapay Paylater Payments', 'wc-gateway-scalapay');
            if (isset($_REQUEST['section']) && $_REQUEST['section'] == esc_attr('wc-scalapay-paylater')) {
                $payment_tabs   = '<p class="scalapaytabs">';
                $payment_tabs   .= '<a class="scalapay-widget-link" href="'.admin_url('admin.php?page=wc-settings&tab=checkout&section=wc-scalapay-payin3&act=general-settings').'">GENERAL SETTINGS</a>';
                $payment_tabs   .= '<a class="scalapay-widget-link" href="'.admin_url('admin.php?page=wc-settings&tab=checkout&section=wc-scalapay-payin3').'">SCALAPAY PAY IN 3</a>';
                $payment_tabs   .= '<a class="scalapay-widget-link" href="'.admin_url('admin.php?page=wc-settings&tab=checkout&section=wc-scalapay-payin4').'">SCALAPAY PAY IN 4</a>';
                $payment_tabs   .= '<a class="scalapay-widget-link active" href="'.admin_url('admin.php?page=wc-settings&tab=checkout&section=wc-scalapay-paylater').'">SCALAPAY PAYLATER</a>';
                $payment_tabs   .= '</p>';
            }
            $this->method_description = __($payment_tabs, 'wc-gateway-scalapay');
            $this->specific_currencies = $this->get_option('specific_currencies');
            $this->log_enabled         = $this->get_option('log_enabled','no');
            $this->init_form_fields();
            $this->init_settings();
            $this->title = __('Scalapay - Pay later', 'wc-gateway-scalapay');
            $this->description = __('Receive your order right away and pay it after 14 days. 0 interest. No explanation needed.', 'wc-gateway-scalapay');
            $this->enabled     = $this->get_option('enabled');
            $this->livemode    = $this->get_option('live_mode') === "yes" ? true : false;
            $this->api_callback = home_url('/wc-api/') . strtolower(get_class($this));
            add_action('woocommerce_update_options_payment_gateways_' . $this->id, array($this, 'process_admin_options'));
            add_action('woocommerce_api_' . strtolower(get_class($this)), array($this, 'wc_scalapay_check_response'));
        } catch (Exception $ex) {

        }
    }

    public function init_form_fields() {
        try {
            $gateway_name   = 'Pay later';
            $this->form_fields = include(WC_SCALAPAY_GATEWAY_DIR_PATH.'classes/gateways/paylater/settings-paylater-fields.php');
        } catch (Exception $ex) {
            wc_add_notice($ex->getMessage(),'error');
        }
    }

    public function is_available() {
        if (!is_admin()) {
            $is_available = 1;
            if ($this->enabled === "yes") {
                if ( WC()->cart ) {
                    $cart_total = WC()->cart->get_total( 'raw' );
                    $specific_categories   = $this->get_option('specific_categories');
                    $specific_currencies   = $this->get_option('specific_currencies');
                    $specific_countries    = $this->get_option('specific_countries');
                    $specific_languages    = $this->get_option('specific_languages');

                    $minimum_amount = $this->get_option('minimum_amount');
                    $maximum_amount = $this->get_option('maximum_amount');

                    if (WC()->customer->get_billing_country())  $current_billing_country  = WC()->customer->get_billing_country();
                    if (WC()->customer->get_shipping_country()) $current_shipping_country = WC()->customer->get_shipping_country();

                    if ($cart_total<$minimum_amount || $cart_total>$maximum_amount) {
                        $is_available = 0;
                    }

                    //checking which countries are allowed
                    if (is_array($specific_countries)) {
                        if (isset($current_billing_country) && $current_billing_country != "") {
                            if (!in_array($current_billing_country, $specific_countries) && !in_array($current_shipping_country, $specific_countries)) {
                                $is_available = 0;
                            }
                        }
                    }
                    //end checking which countries are allowed

                    //checking which currencies are allowed
                    if (is_array($specific_currencies)) {
                        $currency_code = get_woocommerce_currency();
                        if (!in_array($currency_code, $specific_currencies)) {
                            $is_available = 0;
                        }
                    }
                    //end checking which currencies are allowed

                    //checking which languages are allowed
                    if (is_array($specific_languages)) {
                        $language_code = get_locale();
                        if (!in_array($language_code, $specific_languages)) {
                            $is_available = 0;
                        }
                        if (defined('ICL_LANGUAGE_CODE')) {
                            $current_lang = ICL_LANGUAGE_CODE;
                            if ($current_lang=='fr') {
                                $current_lang='fr_FR';
                            }
                            if ($current_lang=='it') {
                                $current_lang='it_IT';
                            }
                            if ($current_lang=='de') {
                                $current_lang='de_DE';
                            }
                            if ($current_lang=='es') {
                                $current_lang='es_ES';
                            }

                            if ($current_lang=='en') {
                                $current_lang='en-US';
                            }
                            if (in_array($current_lang, $specific_languages)) {
                                $is_available = 1;
                            }
                            if ($is_available == 0) {
                                if ($language_code == 'en_US') {
                                    $language_code = 'en-US';
                                    if (in_array($language_code, $specific_languages)) {
                                        $is_available = 1;
                                    }
                                }
                            }
                        }
                    }
                    //end checking language


                    //checking which categories are allowed
                    foreach (WC()->cart->get_cart() as $cart_item) {
                        $product_id  =  $cart_item['product_id'];
                        $product     = wc_get_product( $product_id );
                        $term_ids            = $product->get_category_ids();
                        if (is_array($term_ids) && is_array($specific_categories) ) {
                            foreach ($term_ids as $term_id) {
                                if (in_array($term_id, $specific_categories)) {
                                    $is_available = 0;
                                }
                            }
                        }
                    }
                    //end checking which categories are allowed
                }
            } else {
                $is_available = 0;
            }
            if ($is_available == 1) {
                return true;
            } else {
                return false;
            }
        }
    }

    public function process_payment($order_id) {
        try {
            $this->init_request_api();
            $result = $this->api_request_handler->request_return_handler($order_id);

            /**
             * tracking data if log is enabled
             */
            if ($this->log_enabled == esc_attr('yes')) {
                wc_scalapay_log($this->id, 'process_payment -> response_request_return_handler', json_encode($result));
            }

            $result = json_decode($result, true);
            if (isset($result['token']) &&  !empty($result['token'])) {
                $order = new WC_Order($order_id);
                $approvalUrl = trim($result['checkoutUrl']);
                update_metadata('post', $order_id, '_payment_method_title', $this->title);
                $order->set_payment_method_title($this->title);
                return array(
                    'result' => 'success',
                    'redirect' => $approvalUrl
                );
            } else {
                wc_add_notice($result['message'], 'error');
            }
        } catch (Exception $exception) {
            wc_add_notice($exception->getMessage(), 'error');
        }
    }

    public function init_request_api() {
        try {
            $api_callback = $this->api_callback;
            include_once( WC_SCALAPAY_GATEWAY_DIR_PATH . '/classes/gateways/paylater/class-scalapay-paylater-api-handler.php' );
            $this->api_request_handler = new WC_Scalapay_Gateway_Pay_Later_API_Handler($this,$api_callback);
        } catch (Exception $ex) {
            self::log($ex->getMessage());
        }
    }

    public function process_refund($order_id, $amount = null, $reason = '') {
        try {
            $this->init_request_api();
            return $this->api_request_handler->request_process_refund($order_id, $amount, $reason);
        } catch (Exception $ex) {
            wc_add_notice($ex->getMessage(), 'error');
        }
    }

    public function wc_scalapay_check_response() {
        try {
            $this->init_request_api();
            $this->api_request_handler->request_check_response();
        } catch (Exception $ex) {
            wc_add_notice($ex->getMessage(), 'error');
        }
    }
}