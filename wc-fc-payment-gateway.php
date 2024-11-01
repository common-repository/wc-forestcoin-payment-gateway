<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://forestcoin.earth/
 * @since             1.0.0
 * @package           Wc_Fc_Payment_Gateway
 *
 * @wordpress-plugin
 * Plugin Name:       Forestcoin Payment Gateway for WooCommerce
 * Plugin URI:        https://bitbucket.org/fcpayment/wc-fc-payment-gateway/
 * Description:       A payment gateway for the Forestcoin.
 * Version:           1.0.0
 * Author:            Forestcoin
 * Author URI:        https://forestcoin.earth/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       wc-fc-payment-gateway
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

if ( ! in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {
	return;
};

/**
 * Currently plugin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
define( 'WC_FC_PAYMENT_GATEWAY_VERSION', '1.0.3' );
define( 'WC_FC_SETTINGS_KEY', 'fc_payment_gateway' );
define( 'WC_FC_MID', 'wc_fc_mid' );
define( 'WC_FC_MODE', 'wc_fc_mode' );
define( 'WC_FC_API_URL', 'wc_fc_api_url' );
define( 'WC_FC_EMAIL', 'wc_fc_email' );
define( 'WC_FC_WEB_URL', 'wc_fc_web_url' );
define( 'WC_FC_SIGNAL_R_URL', 'wc_fc_signal_r_url' );
define( 'WC_FC_MERCHANT_USERNAME', 'wc_fc_merchant_username' );
define( 'WC_FC_MERCHANT_PASSWORD', 'wc_fc_merchant_password' );
define( 'WC_FC_CURRENCY', 'wc_fc_currency' );
define( 'WC_FC_FUND_TYPE', 'wc_fc_fund_type' );
define( 'WC_FC_ACCESS_TOKEN', 'wc_fc_access_token' );
define( 'WC_FC_DEBUG_LOG_ENABLED', 'wc_fc_debug_log_enabled' );
define( 'WC_FC_DEBUG_LOG_CLEAR_INTERVAL', 'wc_fc_debug_log_clear_interval' );
define( 'WC_FC_STATUS_PENDING', 'Pending' );
define( 'WC_FC_STATUS_FAILED', 'Failed' );
define( 'WC_FC_STATUS_SUCCESS', 'Success' );
define( 'WC_FC_FILE', __FILE__);
define( 'WC_FC_PLUGIN_BASE_FILE', basename(dirname( __FILE__)) .'/' . basename(__FILE__));

//die(basename(__FILE__) . '/'. __FILE__);
/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-wc-fc-payment-gateway-activator.php
 */
function activate_wc_fc_payment_gateway($network_wide) {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-wc-fc-payment-gateway-activator.php';
	Wc_Fc_Payment_Gateway_Activator::activate($network_wide);
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-wc-fc-payment-gateway-deactivator.php
 */
function deactivate_wc_fc_payment_gateway($network_wide) {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-wc-fc-payment-gateway-deactivator.php';
	Wc_Fc_Payment_Gateway_Deactivator::deactivate($network_wide);
}

register_activation_hook( __FILE__, 'activate_wc_fc_payment_gateway' );
register_deactivation_hook( __FILE__, 'deactivate_wc_fc_payment_gateway' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-wc-fc-payment-gateway.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function wc_fc_payment_gateway() {

	return new Wc_Fc_Payment_Gateway();
}


function wc_fc_write_log( $log) {
	
	$uploads  = wp_upload_dir( null, false );
	$logs_dir = $uploads['basedir'] . '/wc-fc-payment-gateway-logs';
	
	if ( ! is_dir( $logs_dir ) ) {
		mkdir( $logs_dir, 0755, true );
	}
	
	$file = $logs_dir . '/' . 'wc-fc-payment-gateway-logs-' . date( 'Y-m-d' ) . '.log';
	
	$logging_enabled = wc_fc_payment_gateway()->get_fc_settings(WC_FC_DEBUG_LOG_ENABLED) === 'yes';
	
	if ( $logging_enabled ) {
		if ( is_array( $log ) || is_object( $log ) ) {
			error_log( print_r( $log, true ), 3, $file );
		} else {
			$datetime = date( 'Y-m-d h:i:sa' );
			error_log( $datetime . ' : ' . $log, 3, $file );
		}
		error_log( PHP_EOL, 3, $file );
	}
}

function wc_fc_clear_log($days = 0) {
	if($days > 0) {
		wc_fc_write_log('Clearing log with ' . $days . ' days');
		$uploads = wp_upload_dir(null, false);
		$logs_dir = $uploads['basedir'] . '/wc-fc-payment-gateway-logs';
		if (is_dir($logs_dir)) {
			$date = strtotime(date("Y-m-d", strtotime("-" . $days . "day")));
			$items = glob($logs_dir . '/*.log', GLOB_NOSORT);
			foreach ($items as $item) {
				if (filemtime($item) < $date) {
					wc_fc_write_log('Cleared file ' . $item);
					unlink($item);
				}
			}
		}
	}
}

function wc_fc_update_plugin_status_flag($status = false) {
	$mid = wc_fc_payment_gateway()->get_fc_settings(WC_FC_MID);
	if($mid > 0) {
		$data = wc_fc_payment_gateway()->get_merchant_data($mid);
		if(isset($data['organisation']['organisationID']) && $data['organisation']['organisationID'] > 0) {
			// Update merchant data
			$mdata["userID"] = $mid;
			$mdata["webPayUniqueKey"] = $data['organisation']['webPayUniqueKey'];
			$mdata["receivePaymentType"] = $data['organisation']['receivePaymentType'];
			$mdata["merchantCurrencyId"] = $data['organisation']['merchantCurrencyId'];
			$mdata["merchantCurrency"] = $data['organisation']['merchantCurrency'];
			$mdata["acceptPaymeny"] = (bool)$data['organisation']['acceptPaymeny'];
			$mdata["bReferralPage"] = (bool)$data['organisation']['bReferralPage'];
			$mdata["isOnlineShopping"] = $status;
			$mdata["shoppingCartURL"] = home_url();
			wc_fc_write_log(print_r($mdata, true));
			wc_fc_payment_gateway()->update_merchant_data($mdata);
		} else {
			wc_fc_write_log('ERROR Empty Merchant data');
		}
	}
}

wc_fc_payment_gateway()->run();

