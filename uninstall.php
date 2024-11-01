<?php

/**
 * Fired when the plugin is uninstalled.
 *
 * When populating this file, consider the following flow
 * of control:
 *
 * - This method should be static
 * - Check if the $_REQUEST content actually is the plugin name
 * - Run an admin referrer check to make sure it goes through authentication
 * - Verify the output of $_GET makes sense
 * - Repeat with other user roles. Best directly by using the links/query string parameters.
 * - Repeat things for multisite. Once for a single site in the network, once sitewide.
 *
 * This file may be updated more in future version of the Boilerplate; however, this is the
 * general skeleton and outline for how the file should work.
 *
 * For more information, see the following discussion:
 * https://github.com/tommcfarlin/WordPress-Plugin-Boilerplate/pull/123#issuecomment-28541913
 *
 * @link       https://forestcoin.earth/
 * @since      1.0.0
 *
 * @package    Wc_Fc_Payment_Gateway
 */

// If uninstall not called from WordPress, then exit.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}
if(!current_user_can('activate_plugins')) return;

/**
 * Loop through all instances and init wc_fc_payment_gateway_uninstall_setup
 * @since 1.0.0
 */
function wc_fc_payment_gateway_initiate_uninstall() {
	global $network_wide, $wpdb;
	
	if(is_multisite()) {
		$blog_ids = $wpdb->get_col("SELECT blog_id FROM $wpdb->blogs");
		
		if($blog_ids) {
			foreach($blog_ids as $blog_id) {
				switch_to_blog($blog_id);
				if(!is_plugin_active('woocommerce-fc-payment-gateway/wc-fc-payment-gateway.php')) wc_fc_payment_gateway_uninstall_setup();
				restore_current_blog();
			}
		}
	} else {
		wc_fc_payment_gateway_uninstall_setup();
	}
}

/**
 * Delete the entire SB AdRotate database and remove the options on uninstall
 * @since 1.0.0
 */

function wc_fc_payment_gateway_uninstall_setup()
{
	global $wpdb;
	

	// Drop MySQL Tables
	$wpdb->query("DROP TABLE IF EXISTS `{$wpdb->prefix}wc_fc_payment_transaction_log`");
	
	
	// define a vairbale and store an option name as the value.
	$plugin_options = 'woocommerce_fc_payment_gateway_settings'; // Format: $wc_plugin_id + $plugin_own_id + option key
	$option_name    = $plugin_options;

	// call delete option and use the variable inside the quotations.
	update_option( $option_name, null );
	delete_option( $option_name );

	
	$upload_dir = wp_upload_dir();
	$fc_log_dir = $upload_dir['basedir'] . '/wc-fc-payment-gateway-logs';
	
	$files = glob($fc_log_dir . '/*');
	if(!empty($files)) {
		foreach ($files as $file) { // iterate files
			if (is_file($file)) {
				unlink($file); // delete file
			}
		}
	}
	
	if(is_dir($fc_log_dir)) {
		rmdir($fc_log_dir);
	}
}

// Run the uninstaller
wc_fc_payment_gateway_initiate_uninstall();