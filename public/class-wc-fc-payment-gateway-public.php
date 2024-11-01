<?php

/**
 * The public-facing functionality of the plugin.
 *
 * @link       https://forestcoin.earth/
 * @since      1.0.0
 *
 * @package    Wc_Fc_Payment_Gateway
 * @subpackage Wc_Fc_Payment_Gateway/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the public-facing stylesheet and JavaScript.
 *
 * @package    Wc_Fc_Payment_Gateway
 * @subpackage Wc_Fc_Payment_Gateway/public
 * @author     ForestCoin <sivakumar@tendersoftware.in>
 */
class Wc_Fc_Payment_Gateway_Public {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of the plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;

	}

	/**
	 * Register the stylesheets for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Wc_Fc_Payment_Gateway_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Wc_Fc_Payment_Gateway_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */
		
		if (in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) && is_checkout()) {
			wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/wc-fc-payment-gateway-public.css', array(), $this->version, 'all' );
		}
		
	}

	/**
	 * Register the JavaScript for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Wc_Fc_Payment_Gateway_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Wc_Fc_Payment_Gateway_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */
		
		if (in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) && is_checkout()) {
			wp_enqueue_script( $this->plugin_name . '_qrcode', plugin_dir_url( __FILE__ ) . 'js/qrcode.min.js', array( ), $this->version, true);
			wp_enqueue_script( $this->plugin_name . '_signalr', plugin_dir_url( __FILE__ ) . 'js/signalr.min.js', array( ), $this->version, true);
			wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/wc-fc-payment.js', array( 'jquery','jquery-blockui'), $this->version, true);
			wp_localize_script($this->plugin_name, 'wooFc', array(
				'signalRUrl' => wc_fc_payment_gateway()->get_fc_settings(WC_FC_SIGNAL_R_URL) . '?fc=' . $this->version
			));
		}
	}

	public function remove_postcode_validation( $fields ) {
		$fields['postcode']['required'] = false;
		return $fields;
	}
}
