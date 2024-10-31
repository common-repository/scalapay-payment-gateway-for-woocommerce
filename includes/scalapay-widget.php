<?php
$hide_widget_text = 0;
if (is_product()) {
    $widget_type = esc_attr('product');
} elseif (is_cart()) {
    $widget_type = esc_attr('cart');
}

//GENERAL SETTINGS
$minimum_amount = $gatewayObj->get_option('minimum_amount');
$maximum_amount = $gatewayObj->get_option('maximum_amount');

//widget settings
$price_selectors   = $gatewayObj->get_option($widget_type . '_price_selectors');
$frequency    = esc_attr(30);
$widget_theme = esc_attr('primary');
if ($gatewayObj->id == esc_attr('wc-scalapay-paylater')) {
    $frequency    = $gatewayObj->get_option('frequency');
    $widget_theme = $gatewayObj->get_option('widget_theme');
}
$hide_widget       = ($gatewayObj->get_option($widget_type . '_hide_widget') == "no") ? "false" : "true";
$hide_amount       = ($gatewayObj->get_option($widget_type . '_hide_amount') == "no") ? "false" : "true";
$currency_position = $gatewayObj->get_option($widget_type . '_currency_position');
$currency_display  = $gatewayObj->get_option($widget_type . '_currency_display');
$logo_size         = $gatewayObj->get_option($widget_type . '_logo_size');

// RESTRICTION SETTINGS
$specific_categories = $gatewayObj->get_option('specific_categories');
$specific_countries  = $gatewayObj->get_option('specific_countries');
$specific_currencies = $gatewayObj->get_option('specific_currencies');
$specific_languages  = $gatewayObj->get_option('specific_languages');
$AmountGet = 0;


if ($widget_type == esc_attr('product')) {
    global $product;
    $product_id = $product->get_id();
    if ($hide_widget == 'false') {
        $hide_widget = get_scalapay_restriction_product($product_id, $specific_categories, $specific_currencies, $specific_languages, $hide_widget);
    }
    $product = wc_get_product( $product_id );
    $product_price = $product->get_price();
    if(($minimum_amount <= $product_price) && ($product_price <= $maximum_amount)) {
        $hide_widget_text = 1;
    }
    $AmountGet = $product_price;
}

if ($widget_type == esc_attr('cart')) {
    if ($hide_widget == 'false') {
        $hide_widget = get_scalapay_restriction_cart($specific_categories, $specific_currencies, $specific_languages, $hide_widget);
    }
    if (WC()->cart){
        $cart_total = WC()->cart->get_total( 'raw' );
        if(($minimum_amount <= $cart_total) && ($cart_total <= $maximum_amount)) {
            $hide_widget_text = 1;
        }
        $AmountGet = $cart_total;
    }
    
}
?>
<div class="scalapay-widget-area-<?php echo $widget_type ?>" id="scalapay-widget-area-<?php echo $gatewayObj->id; ?>">
    <scalapay-widget
            frequency-number="<?php echo $frequency; ?>"
            amount="<?php echo $AmountGet; ?>"
            number-of-installments="<?php echo esc_attr($numberOfInstallments); ?>"
            hide="<?php echo $hide_widget; ?>"
            hide-price="<?php echo $hide_amount; ?>"
            min="<?php echo $minimum_amount; ?>"
            max="<?php echo $maximum_amount; ?>"
            amount-selectors='["<?php echo esc_attr($price_selectors); ?>"]'
            currency-position="<?php echo $currency_position; ?>"
            currency-display="<?php echo $currency_display; ?>"
            logo-size="<?php echo $logo_size; ?>"
            theme="<?php echo $widget_theme; ?>"
            locale="<?php echo get_current_language() ?>">
    </scalapay-widget>
    <?php
        $hide_widget_min_price = $gatewayObj->get_option('hide_widget_min_price');
        $display_widget_text   = $gatewayObj->get_option('display_widget_text');
        if (isset($hide_widget_min_price) && $hide_widget_min_price == esc_attr('no')) {
            if (isset($display_widget_text) && $display_widget_text == esc_attr('yes')) {
                $below_widget_text = $gatewayObj->get_option('below_widget_text');
                echo '<p>'.$below_widget_text.'</p>';
            }
        } else if (isset($hide_widget_min_price) && $hide_widget_min_price == esc_attr('yes')) {
            if (isset($display_widget_text) && $display_widget_text == esc_attr('yes')) {
                if($hide_widget_text == 1) {
                    $below_widget_text = $gatewayObj->get_option('below_widget_text');
                    echo '<p>'.$below_widget_text.'</p>';
                }
            }
        }
    ?>
</div>
<?php do_action('scalapay_widget_text_postions', $gatewayObj->id); ?>
