<?php

/**
 * Fired during plugin activation
 *
 * @link       https://forestcoin.earth/
 * @since      1.0.0
 *
 * @package    Wc_Fc_Payment_Gateway
 * @subpackage Wc_Fc_Payment_Gateway/includes
 */

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      1.0.0
 * @package    Wc_Fc_Payment_Gateway
 * @subpackage Wc_Fc_Payment_Gateway/includes
 * @author     ForestCoin <sivakumar@tendersoftware.in>
 */
class Wc_Fc_Payment_Gateway_Activator {
	
	/**
	 * @param $network_wide
	 * @since 1.0.0
	 */
	public static function activate($network_wide) {
		self::wc_fc_payment_gateway_activate($network_wide);
	}
	
	/**
	 * Activate the plugin
	 * @param $network_wide
	 * @since 1.0
	 */
	private static function wc_fc_payment_gateway_activate($network_wide) {
		if(is_multisite() && $network_wide) {
			global $wpdb;
			
			$current_blog = $wpdb->blogid;
			$blog_ids = $wpdb->get_col("SELECT `blog_id` FROM $wpdb->blogs;");
			
			foreach($blog_ids as $blog_id) {
				switch_to_blog($blog_id);
				self::wc_fc_payment_gateway_activate_setup();
			}
			
			switch_to_blog($current_blog);
			return;
		}
		self::wc_fc_payment_gateway_activate_setup();
	}
	
	/**
	 * Set up table and default settings
	 * @since 1.0
	 */
	private static function wc_fc_payment_gateway_activate_setup() {
		
			if(!current_user_can('activate_plugins')) {
				deactivate_plugins(WC_FC_PLUGIN_BASE_FILE);
				wp_die('You do not have appropriate access to activate this plugin! Contact your administrator!<br /><a href="'. get_option('siteurl').'/wp-admin/plugins.php">Back to dashboard</a>.');
				return;
			} else {
				// Set defaults for internal versions
				add_option('wc_fc_payment_gateway_version', array('current' => WC_FC_PAYMENT_GATEWAY_VERSION, 'previous' => ''));
				
				// Install new database
				self::wc_fc_payment_gateway_database_install();
				wc_fc_update_plugin_status_flag(true);
			}
	}
	
	/**
	 * Create database table
	 * @since 1.0
	 */
	private static function wc_fc_payment_gateway_database_install() {
		global $wpdb;
		
		require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
		
		// Initial data
		$charset_collate = '';
		$engine = '';
		
		if(!empty($wpdb->charset)) {
			$charset_collate .= " DEFAULT CHARACTER SET {$wpdb->charset}";
		}
		if($wpdb->has_cap('collation') AND !empty($wpdb->collate)) {
			$charset_collate .= " COLLATE {$wpdb->collate}";
		}
		
		$found_engine = $wpdb->get_var("SELECT ENGINE FROM `information_schema`.`TABLES` WHERE `TABLE_SCHEMA` = '".DB_NAME."' AND `TABLE_NAME` = '{$wpdb->prefix}posts';");
		if(strtolower($found_engine) == 'innodb') {
			$engine = ' ENGINE=InnoDB';
		}
		
		dbDelta("CREATE TABLE IF NOT EXISTS `" . $wpdb->prefix . "wc_fc_payment_transaction_log` (
			`id` bigint(20) NOT NULL AUTO_INCREMENT,
			`order_id` int(11) DEFAULT NULL,
			`transaction_request_id` varchar(36) DEFAULT NULL,
			`signal_r_id` varchar(36) DEFAULT NULL,
			`status` enum('Pending','Failed','Success') NOT NULL DEFAULT 'Pending',
			`qr_status` tinyint(1) NOT NULL,
			`data` text,
			PRIMARY KEY  (`id`)
		) ".$charset_collate.$engine.";");
		
	}

}
