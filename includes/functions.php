<?php
add_action('wp_footer', 'scalapay_footer_scripts');
add_action('scalapay_widget_text_postions', 'scalapay_move_product_text_postions', 10, 1);
add_filter('woocommerce_gateway_title', 'scalapay_gateway_title', 10, 2);

function scalapay_gateway_title($this_title, $this_id) {
    if (is_checkout()){
        if ($this_id == "wc-scalapay-payin3") {
            $cart_total   = WC()->cart->get_total( 'raw' );
            $logoPath = WC_SCALAPAY_GATEWAY_DIR_URL.'assets/images/scalapay-logo-black.png';
            $installment_amount  = round($cart_total / 3, 2);
            $scalapay_gateway_checkout = __('Pay in 3 installments [price] without fees with [logo]', 'wc-gateway-scalapay');
            $installment_amount  = '<span id="scalapay_ins_cart">'.wc_price($installment_amount).'</span>';
            $scalapay_logo  = '<img src="'.$logoPath.'" width="88">';
            $scalapay_gateway_checkout = str_replace("[price]", $installment_amount, $scalapay_gateway_checkout);
            $scalapay_gateway_checkout = str_replace("[logo]", $scalapay_logo, $scalapay_gateway_checkout);
            $this_title       = $scalapay_gateway_checkout;
        } elseif ($this_id == "wc-scalapay-payin4") {
            $cart_total   = WC()->cart->get_total( 'raw' );
            $logoPath = WC_SCALAPAY_GATEWAY_DIR_URL.'assets/images/scalapay-logo-black.png';
            $installment_amount  = round($cart_total / 4, 2);
            $scalapay_gateway_checkout = __('Pay in 4 installments [price] without fees with [logo]', 'wc-gateway-scalapay');
            $installment_amount  = '<span id="scalapay_ins_cart">'.wc_price($installment_amount).'</span>';
            $scalapay_logo  = '<img src="'.$logoPath.'" width="88">';
            $scalapay_gateway_checkout = str_replace("[price]", $installment_amount, $scalapay_gateway_checkout);
            $scalapay_gateway_checkout = str_replace("[logo]", $scalapay_logo, $scalapay_gateway_checkout);
            $this_title       = $scalapay_gateway_checkout;
        } elseif ($this_id == "wc-scalapay-paylater") {
            $logoPath = WC_SCALAPAY_GATEWAY_DIR_URL.'assets/images/scalapay-logo-black.png';
            $scalapay_gateway_checkout = __('Try first, pay later', 'wc-gateway-scalapay');
            $scalapay_gateway_checkout .= '<img src="'.$logoPath.'" width="88">';
            $this_title       = $scalapay_gateway_checkout;
        }
    }
    return $this_title;
}
function scalapay_move_product_text_postions($gateway_id) {
    $gateway_settings   = get_option('woocommerce_'.$gateway_id.'_settings');
    $widget_position    = $gateway_settings['product_widget_position'];
    if (!empty($widget_position)) {
        $widget_area_id          = '#scalapay-widget-area-'.$gateway_id;
        $scalapay_move_text = "jQuery(\"" . $widget_position . "\").append(jQuery(\"" . $widget_area_id . "\"));jQuery(\"" . $widget_area_id . "\").show();";
        wp_enqueue_script('scalapay-product-segment', WC_SCALAPAY_GATEWAY_ASSETS . 'js/scalapay.js', array('jquery'), '1.0', true);
        $Script = "jQuery(document).ready(function() {" . $scalapay_move_text . "});";
        wp_add_inline_script('scalapay-product-segment', $Script, 'after');
    }
}
function scalapay_footer_scripts() {
    $gateway_settings   = get_option('woocommerce_wc-scalapay-payin3_settings');
    $custom_css         = $gateway_settings['custom_css'];
    if (!empty($custom_css)) {
        if (is_product() || is_cart() || is_checkout()) {
            if (!empty($custom_css)) echo '<style>' . $custom_css . '</style>';
        }
    }
}
function ScalapayActivationHook()
{
    $payin3_settings   = get_option('woocommerce_wc-scalapay-payin3_settings');
    if (!isset($payin3_settings['sandbox_url'])) $payin3_settings['sandbox_url'] = WC_SCALAPAY_SANDBOX_URL;
    if (!isset($payin3_settings['sandbox_key'])) $payin3_settings['sandbox_key'] = WC_SCALAPAY_SANDBOX_KEY;
    if (!isset($payin3_settings['custom_css'])) $payin3_settings['custom_css'] = "";
    if (!isset($payin3_settings['product_hook'])) $payin3_settings['product_hook'] = esc_attr('woocommerce_single_product_summary');
    if (!isset($payin3_settings['cart_hook'])) $payin3_settings['cart_hook'] = esc_attr('woocommerce_proceed_to_checkout');
    if (!isset($payin3_settings['production_key'])) $payin3_settings['production_key'] = "";
    if (!isset($payin3_settings['product_widget_position'])) $payin3_settings['product_widget_position'] = "";
    if (!isset($payin3_settings['log_enabled'])) $payin3_settings['log_enabled'] = "no";
    update_option('woocommerce_wc-scalapay-payin3_settings',$payin3_settings);

    $payin4_settings   = get_option('woocommerce_wc-scalapay-payin4_settings');
    if (!isset($payin4_settings['product_hook'])) $payin4_settings['product_hook'] = esc_attr('woocommerce_single_product_summary');
    if (!isset($payin4_settings['cart_hook'])) $payin4_settings['cart_hook'] = esc_attr('woocommerce_proceed_to_checkout');
    if (!isset($payin4_settings['production_key'])) $payin4_settings['production_key'] = "";
    if (!isset($payin4_settings['product_widget_position'])) $payin4_settings['product_widget_position'] = "";
    if (!isset($payin4_settings['log_enabled'])) $payin4_settings['log_enabled'] = "no";
    update_option('woocommerce_wc-scalapay-payin4_settings',$payin4_settings);

    $paylater_settings   = get_option('woocommerce_wc-scalapay-paylater_settings');
    if (!isset($paylater_settings['product_hook'])) $paylater_settings['product_hook'] = esc_attr('woocommerce_single_product_summary');
    if (!isset($paylater_settings['cart_hook'])) $paylater_settings['cart_hook'] = esc_attr('woocommerce_proceed_to_checkout');
    if (!isset($paylater_settings['production_key'])) $paylater_settings['production_key'] = "";
    if (!isset($paylater_settings['product_widget_position'])) $paylater_settings['product_widget_position'] = "";
    if (!isset($paylater_settings['log_enabled'])) $paylater_settings['log_enabled'] = "no";
    update_option('woocommerce_wc-scalapay-paylater_settings',$paylater_settings);

    add_option('wc_scalapay_do_activation_redirect', true);
    flush_rewrite_rules();
}
function scalapay_single_product_hooks() {
    $product_hooks = array(
        "woocommerce_before_single_product" => "Show Before Product",
        'woocommerce_before_single_product_summary' => 'Show Before Product Summary',
        'woocommerce_single_product_summary' => 'Show At Product Summary',
        'woocommerce_before_add_to_cart_form' => 'Show Before Add to Cart Form',
        'woocommerce_before_variations_form' => 'Show Before Product Variations',
        'woocommerce_before_add_to_cart_button' => 'Show Before Add to Cart Button',
        'woocommerce_before_single_variation' => 'Show Before Single Variation',
        'woocommerce_single_variation' => 'Show At Single Variation',
        'woocommerce_after_single_variation' => 'Show After Single Variation',
        'woocommerce_after_add_to_cart_quantity' => 'Show After Add To Cart Quantity',
        'woocommerce_after_add_to_cart_button' => 'Show After Add to Cart Button',
        'woocommerce_after_variations_form' => 'Show After Variation Form',
        'woocommerce_after_add_to_cart_form' => 'Show After Add to Cart Form',
        'woocommerce_product_meta_start' => 'Show At Product Meta Starts',
        'woocommerce_product_meta_end' => 'Show At Product Meta Ends',
        'woocommerce_share' => 'Show At Woocommerce Share Location',
        'woocommerce_after_single_product_summary' => 'Show After Single Product Summary',
        'woocommerce_after_single_product ' => 'Show After Single Product Ends'
    );
    return $product_hooks;
}

function scalapay_cart_page_hooks() {
    $cart_hooks = array(
        "woocommerce_before_cart " => "Show Before Cart Starts",
        'woocommerce_before_cart_table' => 'Show Before Cart Table',
        'woocommerce_before_cart_contents' => 'Show Before Cart Content',
        'woocommerce_cart_contents' => 'Show At Cart Content',
        'woocommerce_after_cart_contents' => 'Show After Cart Ends',
        'woocommerce_after_cart_table' => 'Show After Cart Table Ends',
        'woocommerce_before_cart_totals' => 'Show Before Cart Total',
        'woocommerce_cart_collaterals' => 'Show At Cart Collaterals',
        'woocommerce_cart_totals_before_shipping' => 'Show After Cart Total Before Shipping',
        'woocommerce_before_shipping_calculator' => 'Show Before Shipping Calculator',
        'woocommerce_after_shipping_calculator' => 'Show After Shipping Calculator',
        'woocommerce_cart_totals_after_shipping' => 'Show After Shipping Price',
        'woocommerce_cart_totals_before_order_total' => 'Show After Cart Total',
        'woocommerce_cart_totals_after_order_total' => 'Show After Order Total',
        'woocommerce_proceed_to_checkout' => 'Show Before Checkout Button',
        'woocommerce_after_cart_totals' => 'Show After Cart Total Ends',
        'woocommerce_after_cart' => 'Show After Cart Table Ends'
    );
    return $cart_hooks;
}

function scalapay_countries_list() {
    $countries = array();
    if (class_exists('WC_Countries')) {
        $countries = new WC_Countries();
        $countries = $countries->__get('countries');
    }
    return $countries;
}

function scalapay_languages_list($args = array()) {
    require_once(ABSPATH . 'wp-admin/includes/translation-install.php');
    $translations = wp_get_available_translations();

    // List available translations.
    if ($translations) {
        $structure['en-US'] = esc_attr('English (United State)');
        foreach ($translations as $translation) {
            $structure[$translation['language']] = $translation['native_name'];
        }
    }
    return $structure;
}

function CreateErrorLogs($filename, $log_data){
    if (!is_dir(WC_SCALAPAY_GATEWAY_DIR_PATH.'/logs')) {
        mkdir(WC_SCALAPAY_GATEWAY_DIR_PATH.'/logs', 0777, true);
    }
    $filename = sanitize_file_name($filename);
    scalapay_checkout_log($filename);
    $log_file_name = WC_SCALAPAY_GATEWAY_DIR_PATH . "logs/".$filename.".log";
    if (@file_exists($log_file_name)) {
        if (wp_is_writable($log_file_name)) {
            @file_put_contents($log_file_name, $log_data."\n", FILE_APPEND);
        }
    }
}

function scalapay_checkout_log($request_call)
{
    global $ScalapayLogs;
    $log_file_name = WC_SCALAPAY_GATEWAY_DIR_PATH . "logs/".$request_call.".log";
    $ScalapayLogs   = $log_file_name;

    if (@file_exists($log_file_name)) {
        $check_file_size = @filesize($log_file_name);

        // if size is more than 10 MB delete it
        if ($check_file_size > 1024) {
            @unlink($check_file_size);
        }
    }

    if (!@file_exists($log_file_name))
    {
        // create file
        @fopen($log_file_name, "w");
        if (@file_exists($log_file_name))
        {
            if (wp_is_writable($log_file_name))
            {
                @file_put_contents($log_file_name, '' . "\n", FILE_APPEND);
            }
        }
    }
}

function get_current_language() {
    $language = get_locale();
    $lang = explode('_',$language);
    return $lang[0];
}

function get_scalapay_gateway_categories($category_id) {
    $taxonomy     = 'product_cat';
    $orderby      = 'name';
    $empty        = 1;
    $args = array(
        'taxonomy'     => $taxonomy,
        'orderby'      => $orderby,
        'hide_empty'   => $empty,
        'parent'       => $category_id,
        'child_of'     => 0,
    );
    $categories_arr = array();
    $all_categories = get_categories( $args );
    foreach ($all_categories as $cat) {
        $categories_arr[$cat->term_id] = $cat->name;
        $categories_arr = scalapay_categories_tree($cat->term_id,$categories_arr);
    }
    return $categories_arr;
}

function scalapay_categories_tree($parent_cat_id,$cats_arr) {
    $taxonomy     = 'product_cat';
    $orderby      = 'name';
    $empty        = 1;
    $args = array(
        'taxonomy'     => $taxonomy,
        'orderby'      => $orderby,
        'hide_empty'   => $empty,
        'parent'       => $parent_cat_id,
        'child_of'     => 0,
    );
    $child_categories = get_categories( $args );
    foreach ($child_categories as $cat) {
        if (!in_array($cat->term_id, $cats_arr)) {
            $cats_arr[$cat->term_id] = ' -'.$cat->name;
            $cats_arr = scalapay_categories_tree($cat->term_id,$cats_arr);
        }
    }
    return $cats_arr;
}

function get_scalapay_restriction_product($product_id, $categories, $currencies, $languages, $hide_widget) {
    $product             = wc_get_product( $product_id );
    $term_ids            = $product->get_category_ids();
    if (is_array($term_ids) && is_array($categories) ) {
        foreach ($term_ids as $term_id) {
            if (in_array($term_id, $categories)) {
                $hide_widget = 'true';
            }
        }
    }

    //currency check
    if (is_array($currencies)) {
        $currency_code = get_woocommerce_currency();
        if (!in_array($currency_code, $currencies)) {
            $hide_widget = 'true';
        }
    }

    //language check
    if (is_array($languages)) {
        $language_code = get_locale();
        if (!in_array($language_code, $languages)) {
            $hide_widget = 'true';
        }
        if (defined('ICL_LANGUAGE_CODE')) {
            $current_lang = ICL_LANGUAGE_CODE;
            if($current_lang=='fr') {
                $current_lang='fr_FR';
            }
            if($current_lang=='it') {
                $current_lang='it_IT';
            }
            if($current_lang=='de') {
                $current_lang='de_DE';
            }
            if($current_lang=='es') {
                $current_lang='es_ES';
            }

            if($current_lang=='en') {
                $current_lang='en-US';
            }

            if (in_array($current_lang, $languages)) {
                $hide_widget = 'false';
            }
            if ($hide_widget == 'true') {
                if ($language_code == 'en_US') {
                    $language_code = 'en-US';
                    if (in_array($language_code, $languages)) {
                        $hide_widget = 'false';
                    }
                }
            }
        }
    }
    return $hide_widget;
}

function get_scalapay_restriction_cart($categories, $currencies, $languages, $hide_widget) {
    foreach (WC()->cart->get_cart() as $cart_item_key => $cart_item) {
        $product_id  =  $cart_item['product_id'];
        $product     =  wc_get_product( $product_id );
        $term_ids    =  $product->get_category_ids();
        if (is_array($term_ids) && is_array($categories) ) {
            foreach ($term_ids as $term_id) {
                if (in_array($term_id, $categories)) {
                    $hide_widget = 'true';
                }
            }
        }
    }

    //currency check
    if (is_array($currencies)) {
        $currency_code = get_woocommerce_currency();
        if (!in_array($currency_code, $currencies)) {
            $hide_widget = 'true';
        }
    }

    //language check
    if (is_array($languages)) {
        $language_code = get_locale();
        if (!in_array($language_code, $languages)) {
            $hide_widget = 'true';
        }
        if (defined('ICL_LANGUAGE_CODE')) {
            $current_lang = ICL_LANGUAGE_CODE;
            if($current_lang=='fr') {
                $current_lang='fr_FR';
            }
            if($current_lang=='it') {
                $current_lang='it_IT';
            }
            if($current_lang=='de') {
                $current_lang='de_DE';
            }
            if($current_lang=='es') {
                $current_lang='es_ES';
            }

            if($current_lang=='en') {
                $current_lang='en-US';
            }

            if (in_array($current_lang, $languages)) {
                $hide_widget = 'false';
            }
            if ($hide_widget == 'true') {
                if ($language_code == 'en_US') {
                    $language_code = 'en-US';
                    if (in_array($language_code, $languages)) {
                        $hide_widget = 'false';
                    }
                }
            }
        }
    }
    return $hide_widget;
}

function can_refund_order($order) {
    if ($order instanceof WC_Order && method_exists($order, 'get_transaction_id')) {
        return $order && $order->get_transaction_id();
    }
    return false;
}

function wc_scalapay_log($handle, $called_from, $message)
{
    $logger = wc_get_logger();
    $errorLog   = $called_from.' : '.$message;
    $logger->add($handle,$errorLog);
}