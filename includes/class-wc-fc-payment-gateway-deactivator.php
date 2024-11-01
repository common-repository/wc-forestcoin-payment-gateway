<?php

/**
 * Fired during plugin deactivation
 *
 * @link       https://forestcoin.earth/
 * @since      1.0.0
 *
 * @package    Wc_Fc_Payment_Gateway
 * @subpackage Wc_Fc_Payment_Gateway/includes
 */

/**
 * Fired during plugin deactivation.
 *
 * This class defines all code necessary to run during the plugin's deactivation.
 *
 * @since      1.0.0
 * @package    Wc_Fc_Payment_Gateway
 * @subpackage Wc_Fc_Payment_Gateway/includes
 * @author     ForestCoin <sivakumar@tendersoftware.in>
 */
class Wc_Fc_Payment_Gateway_Deactivator {

	/**
	 * Short Description. (use period)
	 *
	 * Long Description.
	 *
	 * @since    1.0.0
	 */
	public static function deactivate($network_wide) {
		self::wc_fc_payment_gateway_deactivate($network_wide);
	}
	
	/**
	 * De-Activate the plugin
	 * @param $network_wide
	 * @since 1.0
	 */
	private static function wc_fc_payment_gateway_deactivate($network_wide) {
		if(is_multisite() && $network_wide) {
			global $wpdb;
			
			$current_blog = $wpdb->blogid;
			$blog_ids = $wpdb->get_col("SELECT `blog_id` FROM $wpdb->blogs;");
			
			foreach($blog_ids as $blog_id) {
				switch_to_blog($blog_id);
				self::wc_fc_payment_gateway_deactivate_setup();
			}
			
			switch_to_blog($current_blog);
			return;
		}
		self::wc_fc_payment_gateway_deactivate_setup();
	}
	
	/**
	 * Update de-activate flag in the merchant details
	 * @since 1.0
	 */
	private static function wc_fc_payment_gateway_deactivate_setup() {
		wc_fc_update_plugin_status_flag();
	}
}
