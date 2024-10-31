<?php

if (!defined('ABSPATH')) exit;

class WC_Scalapay_Gateway_Pay_Later_API_Handler {

    public $ScalapayLaterPassword;
    public $ScalapayLaterUrl;
    public $api_callback;
    public $scalapay_failed_message;
    public $order_status;
    public $frequency;
    public $log_enabled;

    public function __construct($gateway,$callback) {
        $this->gateway      = $gateway;
        $this->api_callback = $callback;
        $this->livemode     = $this->gateway->get_option('live_mode') === "yes" ? true : false;
        $this->log_enabled  = $this->gateway->get_option('log_enabled','no');
        $this->order_status = $this->gateway->get_option('order_status');
        $this->frequency    = $this->gateway->get_option('frequency');
        $this->scalapay_failed_message =  __('We are really sorry, but this order cannot be approved by Scalapay. Payment failed. Choose another payment method.', 'wc-gateway-scalapay');
        $gateway_settings   = get_option('woocommerce_wc-scalapay-payin3_settings');
        if ($this->livemode) {
            $this->ScalapayLaterPassword = $gateway_settings['production_key']; //$this->gateway->get_option('production_key');
            $this->ScalapayLaterUrl      = $gateway_settings['production_url']; //$this->gateway->get_option('production_url');
        } else {
            $this->ScalapayLaterPassword = $gateway_settings['sandbox_key'];
            $this->ScalapayLaterUrl      = $gateway_settings['sandbox_url'];
        }
    }

    public function request_return_handler($order_id) {
        try {
            $ScalapayUrl = esc_url($this->ScalapayLaterUrl.'/v2/orders');
            $ScalapayPassword = esc_attr($this->ScalapayLaterPassword);
            $order_data = $this->get_order_details($order_id);

            /**
             * tracking data if log is enabled
             */
            if ($this->log_enabled == esc_attr('yes')) {
                wc_scalapay_log('wc-scalapay-paylater', 'request_return_handler -> get_order_details', $order_data);
            }

            $args = array(
                'method' => 'POST',
                'timeout'     => 45,
                'httpversion' => '1.0',
                'headers' => array(
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json',
                    'Authorization' => 'Bearer '.$ScalapayPassword,
                ),
                'body' => $order_data
            );
            $result = wp_remote_post($ScalapayUrl, $args);
            $response = wp_remote_retrieve_body($result);

            /**
             * tracking data if log is enabled
             */
            if ($this->log_enabled == esc_attr('yes')) {
                wc_scalapay_log('wc-scalapay-paylater', 'response_request_return_handler', json_encode($response));
            }

            return $response;
        } catch (Exception $exception) {
            wc_add_notice($exception->getMessage(), 'error');
        }
    }

    public function get_order_details($order_id) {
        global $woocommerce;
        $json_array = array();
        $currency   = get_woocommerce_currency();
        $order      = wc_get_order($order_id);
        $grandTotal = $order->get_total();
        $phoneNumber = $order->get_billing_phone();
        $email = $order->get_billing_email();
        $postcode = $order->get_billing_postcode();
        $city = $order->get_billing_city();
        $address_1 = $order->get_billing_address_1();
        $address = $order->get_billing_address_1();
        $address_2 = $order->get_billing_address_2();
        $state = $order->get_billing_state();
        $country = $order->get_billing_country();

        //getting shipping address
        $shipping_postcode = $order->get_shipping_postcode();
        $shipping_city = $order->get_shipping_city();
        $shipping_address_1 = $order->get_shipping_address_1();
        $shipping_address = $order->get_shipping_address_1();
        $shipping_address_2 = $order->get_shipping_address_2();
        $shipping_state = $order->get_shipping_state();
        $shipping_country = $order->get_shipping_country();
        $shipping_phone = $order->get_billing_phone();
        $shipping_first_name = $order->get_shipping_first_name();
        $shipping_last_name = $order->get_shipping_last_name();
        $shipping_company = $order->get_shipping_company();

        //getting customer details
        $first_name = $order->get_billing_first_name();
        $last_name = $order->get_billing_last_name();
        $email = $order->get_billing_email();
        $full_name = $first_name . " " . $last_name;
        $full_name_shipping = $shipping_first_name . " " . $shipping_last_name;

        $DiscountTotal = 0;
        $json_array["totalAmount"]["amount"] = "$grandTotal";
        $json_array["totalAmount"]["currency"] = "$currency";
        if (isset($phoneNumber) && $phoneNumber != "") {
            $json_array["consumer"]["phoneNumber"] = $phoneNumber;
        }

        $json_array["consumer"]["givenNames"] = $first_name;
        $json_array["consumer"]["surname"] = $last_name;
        $json_array["consumer"]["email"] = $email;

        //billing info
        $json_array["billing"]["name"] = $full_name;
        $json_array["billing"]["line1"] = $address_1;
        $json_array["billing"]["suburb"] = $city;
        if (isset($state) && $state != "") {
            $json_array["billing"]["state"] = $state;
        }
        $json_array["billing"]["postcode"] = $postcode;
        $json_array["billing"]["countryCode"] = $country;
        if (isset($phone) && $phone != "") {
            $json_array["billing"]["phoneNumber"] = "$phone";
        }

        //shipping info
        if (empty($full_name_shipping)) {
            $json_array["shipping"]["name"] = $full_name;
        } else {
            $json_array["shipping"]["name"] = $full_name_shipping;
        }

        if (empty($shipping_address_1)) {
            $json_array["shipping"]["line1"] = $address_1;
        } else {
            $json_array["shipping"]["line1"] = $shipping_address_1;
        }

        if (empty($shipping_city)) {
            $json_array["shipping"]["suburb"] = $city;
        } else {
            $json_array["shipping"]["suburb"] = $shipping_city;
        }

        if (isset($shipping_state) && $shipping_state != "") {
            $json_array["shipping"]["state"] = $shipping_state;
        }

        if (empty($shipping_postcode)) {
            $json_array["shipping"]["postcode"] = $postcode;
        } else {
            $json_array["shipping"]["postcode"] = $shipping_postcode;
        }

        if (empty($shipping_country)) {
            $json_array["shipping"]["countryCode"] = $country;
        } else {
            $json_array["shipping"]["countryCode"] = $shipping_country;
        }

        if (isset($shipping_phone) && $shipping_phone != "") {
            $json_array["shipping"]["phoneNumber"] = "$shipping_phone";
        }

        $items = $order->get_items();
        foreach ($items as $item) {
            $scalapay_item = array();
            $product = $item->get_product();
            $product_name = $item->get_name();
            $product_id = $item->get_product_id();
            $product_variation_id = $item->get_variation_id();
            $product_sku = $product->get_sku();
            $item_price = $product->get_price();
            if ($product_sku == "") {
                $product_sku = strtolower(str_replace(" ", "-", $product_name));
            }
            $qty = $item->get_quantity();
            $scalapay_item["name"] = $product_name;
            $scalapay_item["sku"] = $product_sku;
            $scalapay_item["quantity"] = $qty;

            // set product categories
            if (function_exists('get_categories')) {
                $catlist = wc_get_product_category_list($product_id);
                if (!empty(trim($catlist))) {
                    $CatArr =  explode(',', strip_tags($catlist));
                    if (@is_array($CatArr)) {
                        $scalapay_item["category"] = $CatArr[0];
                        $scalapay_item["subcategory"] = $CatArr;
                    }
                }
            }
            if (empty($item_price) || $item_price == '' || $item_price== ' ') {
                $item_price = 0;
            }
            $scalapay_item["price"]["amount"] = "$item_price";
            $scalapay_item["price"]["currency"] = "$currency";
            $json_array["items"][] = $scalapay_item;
        }

        //get discounts
        $discounts = $order->get_total_discount();
        if (isset($discounts) && $discounts > 0) {
            $all_discounts = $woocommerce->cart->get_applied_coupons();
            foreach ($all_discounts as $coupon_id) {
                $coupons_obj = new WC_Coupon($coupon_id);
                $coupons_amount = $coupons_obj->get_amount();
                $discounts_item = array();
                $discounts_item["displayName"] = $coupon_id;
                $discounts_item["amount"]["amount"] = "$coupons_amount";
                $discounts_item["amount"]["currency"] = "$currency";
                $json_array["discounts"][] = $discounts_item;
            }
        }

        $taxAmount = $order->get_total_tax();
        if ($taxAmount == "") {
            $taxAmount = 0;
        }
        $json_array["taxAmount"]["amount"] = "$taxAmount";
        $json_array["taxAmount"]["currency"] = "$currency";
        $cart_session_id = $order->get_id();

        //pass cart session id and order total in url for future refernce and validation
        $confirm_url = $this->api_callback . '/?scalapay-paylater=true&order_id=' . $cart_session_id . '&total=' . $grandTotal;
        $cancel_url = $this->api_callback . '/?scalapay-paylater=cancel';
        $json_array["merchant"]["redirectConfirmUrl"] = "$confirm_url";
        $json_array["merchant"]["redirectCancelUrl"] = "$cancel_url";
       // $json_array["merchantReference"] = ".$cart_session_id.";
        $json_array["type"] = "online";
        $json_array["product"] = "later";
        $json_array["frequency"]["number"] = "14";
        $json_array["frequency"]["frequencyType"] = "daily";
        $shippingAmount = $order->get_shipping_total();
        if ($shippingAmount == "") $shippingAmount = 0;
        $json_array["shippingAmount"]["amount"] = "$shippingAmount";
        $json_array["shippingAmount"]["currency"] = "$currency";
        $json_array["orderExpiryMilliseconds"]='600000';
        $json_array["pluginDetails"]["platform"]='WordPress';
        $json_array["pluginDetails"]["customized"]='0';
        $json_array["pluginDetails"]["platformVersion"] = get_bloginfo('version');
        $json_array["pluginDetails"]["checkout"] = 'woocommerce';
        $json_array["pluginDetails"]["checkoutVersion"] = WC_SCALAPAY_WC_VERSION;
        $json_array["pluginDetails"]["pluginVersion"] = WC_SCALAPAY_VERSION;
        $json_array_encoded = json_encode($json_array);
        return $json_array_encoded;
    }

    public function request_check_response() {
        try {
            if (isset($_REQUEST['scalapay-paylater'])) {
                if ($_REQUEST['scalapay-paylater'] == esc_attr('cancel') && $_REQUEST['status'] == esc_attr('FAILED'))
                {
                    wc_add_notice(__($this->scalapay_failed_message, 'wc-gateway-scalapay'), 'error');
                    wp_safe_redirect(wc_get_page_permalink('cart'));
                }
                if (!empty($_REQUEST['orderToken']) && $_REQUEST['status'] == esc_attr('SUCCESS'))
                {
                    $orderToken      = $_REQUEST['orderToken'];
                    $order_id        = $_REQUEST['order_id'];
                    $order           = new WC_Order($order_id);
                    $captureResponse = $this->capture_scalapay_order($order_id, $orderToken);

                    /**
                     * tracking data if log is enabled
                     */
                    if ($this->log_enabled == esc_attr('yes')) {
                        wc_scalapay_log('wc-scalapay-paylater', 'request_check_response -> capture_scalapay_order', json_encode($captureResponse));
                    }

                    if (isset($captureResponse["status"]) && $captureResponse["status"] == "APPROVED") {
                        $token = $orderToken;
                        $order->payment_complete($token);
                        $order->add_order_note(sprintf(__('%s payment approved! Transaction ID: %s', 'woocommerce'), "Scalapay", $token));
                        $order->update_status($this->order_status);

                        //sending order id to scalapay
                        $response = $this->scalapay_send_orderID($order_id, $orderToken);

                        /**
                         * tracking data if log is enabled
                         */
                        if ($this->log_enabled == esc_attr('yes')) {
                            wc_scalapay_log('wc-scalapay-paylater', 'request_check_response -> scalapay_send_orderID', $response);
                        }


                        WC()->cart->empty_cart();
                        $url = $this->gateway->get_return_url($order);
                        wp_redirect($url); exit;
                    }
                }
            }
        } catch (Exception $ex) {
            wc_add_notice($ex->getMessage(), 'error');
        }
    }

    public function scalapay_send_orderID($order_id, $orderToken) {
        $scalapay_gateway_url = esc_url($this->ScalapayLaterUrl.'/v2/orders/'.$orderToken);
        $scalapay_password    = esc_attr($this->ScalapayLaterPassword);
        $send_data["merchantReference"] = $order_id;
        $send_data_encoded = json_encode($send_data);
        global $woocommerce;
        $order = new WC_Order($order_id);
        $order_id = $order->get_order_number();
        $orderToken = $order_id;
        $response = wp_remote_post($scalapay_gateway_url, array(
            'method' => 'POST',
            'timeout'     => 45,
            'httpversion' => '1.0',
            'headers' => array('Content-Type' => 'application/json','Accept' => 'application/json',
            'Authorization' => 'Bearer ' . $scalapay_password),
            'body' => "{  \n    \"merchantReference\": \"$orderToken\"\n}",
            'data_format' => 'body'));

        $res['code']    =  $response['response']['code'];
        $res['message'] =  $response['response']['message'];
        return json_encode($res);
    }

    public function capture_scalapay_order($order_id, $orderToken) {
        $scalapay_gateway_url = esc_url($this->ScalapayLaterUrl.'/v2/payments/capture');
        $scalapay_password    = esc_attr($this->ScalapayLaterPassword);
        $send_data["token"] = $orderToken;
        global $woocommerce;
        $order = new WC_Order($order_id);
        $order_id = $order->get_order_number();
        $send_data["merchantReference"] = $order_id;
        $send_data_encoded = json_encode($send_data);
        $response = wp_remote_post($scalapay_gateway_url, array(
            'method' => 'POST',
            'timeout'     => 45,
            'httpversion' => '1.0',
            'headers' => array(
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
                'Authorization' => 'Bearer ' . $scalapay_password
            ),
            'body' => $send_data_encoded,)
        );
        $result   =  wp_remote_retrieve_body($response);
        $response =  json_decode($result, true);
        return $response;
    }

    public function request_process_refund($order_id, $amount, $reason) {
        $order = wc_get_order($order_id);
        if (!can_refund_order($order)) return false;
        $success = $this->create_refund($order, $amount, $order_id);
        if ($success) {
            $order->add_order_note(esc_html__("Refund of amount " . $amount . " sent to Scalapay. Reason: " . $reason, 'wc-gateway-scalapay'));
            return true;
        }
        $order->add_order_note(esc_html__("Failed to send refund of amount " . $amount . " to Scalapay.", 'wc-gateway-scalapay'));
        return false;
    }

    public function create_refund($order, $amount, $order_id) {
        $order_token = $order->get_transaction_id();
        $currency   = $order->get_currency();
        $order_data = [
            "refundAmount" => [
                "amount"   => "$amount",
                "currency" => "$currency"
            ],
            "merchantReference" => "$order_id"
        ];
        $json_encoded_data = json_encode($order_data);
        $ScalapayUrl       = esc_url($this->ScalapayLaterUrl.'/v2/payments/'.$order_token.'/refund');
        $ScalapayPassword  = esc_attr($this->ScalapayLaterPassword);
        $response = wp_remote_post($ScalapayUrl, array(
                'method' => 'POST',
                'httpversion' => '1.0',
                'headers' => array('Content-Type' => 'application/json', 'Accept' => 'application/json',
                    'Authorization' => 'Bearer ' . $ScalapayPassword),
                'body'        => $json_encoded_data,
            )
        );
        $res  = $response['response'];
        /**
         * tracking data if log is enabled
         */
        if ($this->log_enabled == esc_attr('yes')) {
            wc_scalapay_log('wc-scalapay-paylater', 'request_process_refund -> create_refund',json_encode($res));
        }
        if ($res["message"] == "OK" && $res["code"] == '200') { return true; } else {  return false;  }
    }
}