<?php

/**
 * Define the internationalization functionality
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @link       https://forestcoin.earth/
 * @since      1.0.0
 *
 * @package    Wc_Fc_Payment_Gateway
 * @subpackage Wc_Fc_Payment_Gateway/includes
 */

/**
 * Define the internationalization functionality.
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @since      1.0.0
 * @package    Wc_Fc_Payment_Gateway
 * @subpackage Wc_Fc_Payment_Gateway/includes
 * @author     ForestCoin <sivakumar@tendersoftware.in>
 */
class Wc_Fc_Payment_Gateway_i18n {


	/**
	 * Load the plugin text domain for translation.
	 *
	 * @since    1.0.0
	 */
	public function load_plugin_textdomain() {

		load_plugin_textdomain(
			'wc-fc-payment-gateway',
			false,
			dirname( dirname( plugin_basename( __FILE__ ) ) ) . '/languages/'
		);

	}



}
