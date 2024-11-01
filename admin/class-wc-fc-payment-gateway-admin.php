<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://forestcoin.earth/
 * @since      1.0.0
 *
 * @package    Wc_Fc_Payment_Gateway
 * @subpackage Wc_Fc_Payment_Gateway/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Wc_Fc_Payment_Gateway
 * @subpackage Wc_Fc_Payment_Gateway/admin
 * @author     ForestCoin <sivakumar@tendersoftware.in>
 */
class Wc_Fc_Payment_Gateway_Admin {

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
	 * @param      string    $plugin_name       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;

	}

	/**
	 * Register the stylesheets for the admin area.
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

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/wc-fc-payment-gateway-admin.css', array(), $this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the admin area.
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
		
		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/wc-fc-payment-gateway.js', array( 'jquery' ), $this->version, false );
	}
	
	/**
	 * Admin menu, You will see WooCommerce -> Forestcoin Settings 
	 */
	public function fc_admin_menu() {
		
		add_submenu_page(
			'woocommerce',
			__('Forestcoin', 'wc-fc-payment-gateway'),
			__('Forestcoin', 'wc-fc-payment-gateway'),
			'manage_woocommerce',
			$this->fc_get_admin_setting_link(),
			null
		);
	}
	
	/**
	 * Add relevant links to plugins page.
	 *
	 * @since 1.2.0
	 *
	 * @param array $links Plugin action links
	 *
	 * @return array Plugin action links
	 */
	public function fc_plugin_action_links( $links ) {
		$plugin_links = array();
		
		if ( in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {
			$setting_url    = $this->fc_get_admin_setting_link();
			$plugin_links[] = '<a href="' . esc_url( $setting_url ) . '">' . esc_html__( 'Settings', 'woocommerce-gateway-paypal-express-checkout' ) . '</a>';
		}
		
		return array_merge( $plugin_links, $links );
	}
	
	/**
	 * Link to settings screen.
	 */
	public function fc_get_admin_setting_link() {
		if ( version_compare( WC()->version, '2.6', '>=' ) ) {
			$section_slug = WC_FC_SETTINGS_KEY;
		} else {
			$section_slug = strtolower( 'ForestCoin_Payment_Gateway' );
		}
		return admin_url( 'admin.php?page=wc-settings&tab=checkout&section=' . $section_slug );
	}
	
	
	/**
	 * Include the class for fc payment gateway
	 */
	public function init_fc_payment_gateway() {
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'woocommerce/class-wc-fc-wc-payment-gateway.php';
	}
	
	/**
	 * Register the ForestCoin payment gateway with WooCommerce.
	 *
	 * @hooked woocommerce_payment_gateways
	 * @see ForestCoin_Payment_Gateway::init()
	 *
	 * @param string[] $methods
	 *
	 * @return string[]
	 */
	public function add_fc_payment_gateway( $methods ) {
		$methods[] = 'ForestCoin_Payment_Gateway';
		return $methods;
	}

}
