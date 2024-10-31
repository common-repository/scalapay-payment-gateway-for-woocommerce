<?php
/**
 * Plugin Name: Scalapay Easy Installment Gateway
 * Plugin URI: scalapay.com
 * Description: With Scalapay you offer your customers the possibility to pay for purchases in convenient installments (3 or 4) or later (after 14 days from purchase date), without interest. Increase your orders and retain your customers with Scalapay.
 * Author: Scalapay
 * Author URI: scalapay.com/contattaci
 * Version: 1.0.0
 * Text Domain: wc-gateway-scalapay
 * Domain Path: /languages
 *
 * Copyright: (c) 2018-2020 (scalapay.com)
 *
 * License: GNU General Public License v3.0
 * License URI: http://www.gnu.org/licenses/gpl-3.0.html
 *
 * @package   WC-Gateway-Scalapay
 * @author    Scalapay
 * @category  Admin
 * @copyright Copyright (c) 2018-2019, (scalapay.com)
 * @license   http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License v3.0
 *
 * This plugin provides a Scalapay payment method to immediately buy what you want and pay it in three convenient installments of the same amount, without interest.
 */

defined('ABSPATH') or exit;
define( 'WC_SCALAPAY_VERSION', '1.1.39');
define( 'WC_SCALAPAY_WC_VERSION', '5.7');
define( "WC_SCALAPAY_GATEWAY_NOTICE", "mc_notice_");
define( 'WC_SCALAPAY_GATEWAY_PLUGIN_FILE',    __FILE__ );
define( 'WC_SCALAPAY_GATEWAY_DIR_PATH', plugin_dir_path(__FILE__ ));
define( 'WC_SCALAPAY_GATEWAY_DIR_URL', plugin_dir_url( __FILE__ ));
define( 'WC_SCALAPAY_GATEWAY_BASE_NAME', plugin_basename(WC_SCALAPAY_GATEWAY_PLUGIN_FILE));
define( "WC_SCALAPAY_GATEWAY_ASSETS", WC_SCALAPAY_GATEWAY_DIR_URL.'assets/');

require WC_SCALAPAY_GATEWAY_DIR_PATH . 'includes/constant.php';
require WC_SCALAPAY_GATEWAY_DIR_PATH . 'classes/wc-scalapay-gateway.php';
require WC_SCALAPAY_GATEWAY_DIR_PATH . 'includes/functions.php';
register_activation_hook(__FILE__, 'ScalapayActivationHook');
function run_woo_scalapay_gateway() {
    $plugin = new WC_Scalapay_Gateway();
    $plugin->run();
}
run_woo_scalapay_gateway();