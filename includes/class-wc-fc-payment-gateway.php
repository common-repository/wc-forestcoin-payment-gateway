<?php

/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       https://forestcoin.earth/
 * @since      1.0.0
 *
 * @package    Wc_Fc_Payment_Gateway
 * @subpackage Wc_Fc_Payment_Gateway/includes
 */

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      1.0.0
 * @package    Wc_Fc_Payment_Gateway
 * @subpackage Wc_Fc_Payment_Gateway/includes
 * @author     ForestCoin <sivakumar@tendersoftware.in>
 */
class Wc_Fc_Payment_Gateway {

	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      Wc_Fc_Payment_Gateway_Loader    $loader    Maintains and registers all hooks for the plugin.
	 */
	protected $loader;

	/**
	 * The unique identifier of this plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $plugin_name    The string used to uniquely identify this plugin.
	 */
	protected $plugin_name;

	/**
	 * The current version of the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $version    The current version of the plugin.
	 */
	protected $version;

	/**
	 * Define the core functionality of the plugin.
	 *
	 * Set the plugin name and the plugin version that can be used throughout the plugin.
	 * Load the dependencies, define the locale, and set the hooks for the admin area and
	 * the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function __construct() {
		if ( defined( 'WC_FC_PAYMENT_GATEWAY_VERSION' ) ) {
			$this->version = WC_FC_PAYMENT_GATEWAY_VERSION;
		} else {
			$this->version = '1.0.0';
		}
		$this->plugin_name = 'wc-fc-payment-gateway';

		$this->load_dependencies();
		$this->set_locale();
		$this->define_admin_hooks();
		$this->define_public_hooks();

	}

	/**
	 * Load the required dependencies for this plugin.
	 *
	 * Include the following files that make up the plugin:
	 *
	 * - Wc_Fc_Payment_Gateway_Loader. Orchestrates the hooks of the plugin.
	 * - Wc_Fc_Payment_Gateway_i18n. Defines internationalization functionality.
	 * - Wc_Fc_Payment_Gateway_Admin. Defines all hooks for the admin area.
	 * - Wc_Fc_Payment_Gateway_Public. Defines all hooks for the public side of the site.
	 *
	 * Create an instance of the loader which will be used to register the hooks
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function load_dependencies() {

		/**
		 * The class responsible for orchestrating the actions and filters of the
		 * core plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-wc-fc-payment-gateway-loader.php';

		/**
		 * The class responsible for defining internationalization functionality
		 * of the plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-wc-fc-payment-gateway-i18n.php';

		/**
		 * The class responsible for defining all actions that occur in the admin area.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-wc-fc-payment-gateway-admin.php';

		/**
		 * The class responsible for defining all actions that occur in the public-facing
		 * side of the site.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/class-wc-fc-payment-gateway-public.php';
		
		$this->loader = new Wc_Fc_Payment_Gateway_Loader();

	}

	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * Uses the Wc_Fc_Payment_Gateway_i18n class in order to set the domain and to register the hook
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function set_locale() {

		$plugin_i18n = new Wc_Fc_Payment_Gateway_i18n();

		$this->loader->add_action( 'plugins_loaded', $plugin_i18n, 'load_plugin_textdomain' );

	}

	/**
	 * Register all of the hooks related to the admin area functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_admin_hooks() {

		$plugin_admin = new Wc_Fc_Payment_Gateway_Admin( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_styles' );
		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts' );
		$this->loader->add_action( 'admin_menu', $plugin_admin, 'fc_admin_menu' );
		$this->loader->add_filter( 'plugin_action_links_' . plugin_basename( WC_FC_FILE ), $plugin_admin, 'fc_plugin_action_links');
		$this->loader->add_action( 'plugins_loaded', $plugin_admin, 'init_fc_payment_gateway', 11 );
		$this->loader->add_filter( 'woocommerce_payment_gateways', $plugin_admin, 'add_fc_payment_gateway' );
		$this->loader->add_action( 'wc_ajax_wc_fc_start_checkout', ForestCoin_Payment_Gateway::class, 'wc_fc_start_checkout' );
		$this->loader->add_action( 'wc_ajax_wc_fc_get_merchant_code', ForestCoin_Payment_Gateway::class, 'wc_fc_get_merchant_code' );
	}
	
	

	/**
	 * Register all of the hooks related to the public-facing functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_public_hooks() {

		$plugin_public = new Wc_Fc_Payment_Gateway_Public( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_styles' );
		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_scripts' );
		//remove checkout page postcode woocommer validation.
		$this->loader->add_filter( 'woocommerce_default_address_fields' , $plugin_public, 'remove_postcode_validation', 99 );

	}

	/**
	 * Run the loader to execute all of the hooks with WordPress.
	 *
	 * @since    1.0.0
	 */
	public function run() {
		$this->loader->run();
	}

	/**
	 * The name of the plugin used to uniquely identify it within the context of
	 * WordPress and to define internationalization functionality.
	 *
	 * @since     1.0.0
	 * @return    string    The name of the plugin.
	 */
	public function get_plugin_name() {
		return $this->plugin_name;
	}

	/**
	 * The reference to the class that orchestrates the hooks with the plugin.
	 *
	 * @since     1.0.0
	 * @return    Wc_Fc_Payment_Gateway_Loader    Orchestrates the hooks of the plugin.
	 */
	public function get_loader() {
		return $this->loader;
	}

	/**
	 * Retrieve the version number of the plugin.
	 *
	 * @since     1.0.0
	 * @return    string    The version number of the plugin.
	 */
	public function get_version() {
		return $this->version;
	}
	
	public function login($username, $password) {
		$data = array(
			'email' =>  $username,
			'password' => $password,
			'userTypeID' => 1,
			'location' => 'Unknown',
			'browser' => 'Unknown',
			'os' => 'Unknown'
		);
		
		$method = 'POST';
		
		$api = 'Login';
		/*$merchantId = self::get_fc_settings(WC_FC_MID);
		if(!(intval($merchantId) > 0 )) {
			return $merchant;
		}
		if ( WC()->session !== null ) {
			wc_fc_write_log('Getting merchant details from session');
			return WC()->session->get( 'merchant_' .$merchantId);
		} else {
			$response = $this->_call($api, $method, $data);
		}*/
		$response = $this->_call($api, $method, $data);
		wc_fc_write_log('Login Request');
		$data['password'] = str_repeat('x', strlen($password));
		wc_fc_write_log($data);
		wc_fc_write_log('Login Response');
		wc_fc_write_log($response);
		
		$user = isset($response['user']) ? $response['user'] : [];
		$data = isset($response['data']) ? $response['data'] : [];
		
		$organisation = isset($user['organisation']) ? $user['organisation'] : [];
		$appSettingsList = isset($response['appSettingsList']) ? $response['appSettingsList'] : [];
		
		$web_url = '';
		$signal_r_url = '';
		foreach ($appSettingsList as $item) {
			if(isset($item['name'])) {
				switch ($item['name']) {
					case 'SignalrURL' :
						$signal_r_url = $item['value'];
						break;
					case 'webURL' :
						$web_url = trim($item['value'], '/') . '/';
						$web_url .= isset($organisation['webPayUniqueKey']) ? $organisation['webPayUniqueKey'] : '';
						break;
				}
			}
		}
		
		return array(
			"organisation" => array(
				"name" => isset($organisation['orgName']) ? $organisation['orgName'] : null,
				"website" => isset($organisation['orgWebsite']) ? $organisation['orgWebsite'] : null,
				"address" => isset($organisation['orgAddress']) ? $organisation['orgAddress'] : null,
				"web_pay_key" =>  isset($organisation['webPayUniqueKey']) ? $organisation['webPayUniqueKey'] : null,
				"accept_payment" => isset($organisation['acceptPaymeny']) ? $organisation['acceptPaymeny'] : null,
				"allow_referral_page" => isset($organisation['bReferralPage']) ? $organisation['bReferralPage'] : null,
				"m_currency_id" => isset($organisation['merchantCurrencyId']) ? $organisation['merchantCurrencyId'] : null,
				"m_currency" => isset($organisation['merchantCurrency']) ? $organisation['merchantCurrency'] : null,
				"fund_type" =>  isset($organisation['receivePaymentType']) ? $organisation['receivePaymentType'] : null,
			),
			"email" => isset($user['email']) ? $user['email'] : '',
			"mid" => isset($user['userId']) ? $user['userId'] : 0,
			"access_token" => isset($data['access_token']) ? $data['access_token'] : '',
			"profile" => isset($user['profilePic']) ? $user['profilePic'] : null,
			"location" => isset($user['location']) ? $user['location'] : null,
			"web_url" => $web_url,
			"signal_r_url" => $signal_r_url,
			"status" => isset($response['statusCode']) ? $response['statusCode'] : 0,
			"message" => isset($response['errorMessage']) ? $response['errorMessage'] : '',
		);
	}
	
	public function get_merchant_data($userId){
		
		$method = 'GET';
		$api = 'GetMerchantdata?userid=' . $userId;
		
		$response = $this->_call($api, $method, array());
		
		wc_fc_write_log('GetMerchantdata Request');
		wc_fc_write_log($api);
		wc_fc_write_log('GetMerchantdata Response');
		wc_fc_write_log($response);
		return $response;
	}
	
	public function update_merchant_data($data){
		if(empty($data)) {
			return false;
		}
		
		$method = 'POST';
		$api = 'UpdateMerchantdata';
		$headers = array("Content-Type: application/json");
		
		$response = $this->_call($api, $method, $data, $headers);
		wc_fc_write_log('UpdateMerchantdata Request');
		wc_fc_write_log($data);
		wc_fc_write_log('UpdateMerchantdata Response');
		wc_fc_write_log($response);
		return isset($response['statusCode']) && $response['statusCode'] > 0;
	}
	
	public function get_open_order($merchantId, $amount, $currency, $fundsType) {
		
		$data = array(
			"fundsType" => $fundsType,
			"currencyName" => $currency,
			"amount" => $amount,
			"userId" => $merchantId
		);
		
		$method = 'POST';
		
		$api = 'GetOpenOrder';
		
		$headers = array(
			"Content-Type: application/json"
		);
		
		$response = $this->_call($api, $method, $data, $headers);
		
		wc_fc_write_log('GetOpenOrder Request');
		wc_fc_write_log($data);
		wc_fc_write_log('GetOpenOrder Response');
		wc_fc_write_log($response);
		
		return array(
			"volume_trade" => isset($response['volumeTrade']) ? $response['volumeTrade'] : null,
			"one_fc_price" => isset($response['oneFCPrice']) ? $response['oneFCPrice'] : null,
			"one_fc_equal_fund_type" => isset($response['oneFCEqualFundType']) ? $response['oneFCEqualFundType'] : null,
			"refresh_second" => isset($response['refreshSecond']) ? $response['refreshSecond'] : -1,
			"merchant_payout" => !empty($response['merchantPayoutAmountText']) ? "(" . $response['merchantPayoutAmountText'] . ")" : null,
			"other" => isset($response['data']) ? $response['data'] : null,
			"barcode_text" => isset($response['barcodeText']) ? $response['barcodeText'] : null,
			"fiat_currency" => isset($response['fiatCurrencyText']) ? $response['fiatCurrencyText'] : null,
			"status" => isset($response['statusCode']) ? $response['statusCode'] : 0,
			"message" => $response['statusCode'] == 0 && empty($response['errorMessage']) ? $response['status'] : $response['errorMessage'],
		);
	}
	
	public function get_transaction_request($merchantId, $amount, $currency, $fundtype, $volumeTrade, $signalrConnectionID, $barcodeText) {
		
		$data = array(
			"fundsType" =>  $fundtype,
			"selectedCurrency" => $currency,
			"amount" => $amount,
			"merchantUserId" => $merchantId,
			"volumeTrade" => $volumeTrade,
			"signalrConnectionID" => $signalrConnectionID,
			"transactionTypeID" => 2,
			"barcodeText" => $barcodeText
		);
		
		$method = 'POST';
		
		$api = 'GetTransactionRequestId';
		
		$headers = array(
			"Content-Type: application/json"
		);
		
		$response = $this->_call($api, $method, $data, $headers);
		
		wc_fc_write_log('GetTransactionRequestId Request');
		wc_fc_write_log($data);
		wc_fc_write_log('GetTransactionRequestId Response');
		wc_fc_write_log($response);
		
		
		$transaction = isset($response['transactionRequest']) ? $response['transactionRequest'] : [];
		
		return array(
			"transaction_pay_id" => isset($transaction['transactionPayID']) ? $transaction['transactionPayID'] : null,
			"transaction_request_id" => isset($transaction['transactionRequestID']) ? $transaction['transactionRequestID'] : null,
			"merchant_user_id" => isset($transaction['merchantUserId']) ? $transaction['merchantUserId'] : null,
			"customer_user_id" => isset($transaction['customerUserId']) ? $transaction['customerUserId'] : null,
			"merchant_push_token" => isset($transaction['merchantPushToken']) ? $transaction['merchantPushToken'] : null,
			"customer_push_token" => isset($transaction['customerPushToken']) ? $transaction['customerPushToken'] : null,
			"amount" => isset($transaction['amount']) ? $transaction['amount'] : null,
			"volume_trade" => isset($transaction['volumeTrade']) ? $transaction['volumeTrade'] : null,
			"converted_price" => isset($transaction['convertedPrice']) ? $transaction['convertedPrice'] : null,
			"funds_type" => isset($transaction['fundsType']) ? $transaction['fundsType'] : null,
			"txt_status" => isset($transaction['status']) ? $transaction['status'] : null,
			"order_id" => isset($transaction['orderID']) ? $transaction['orderID'] : null,
			"created_on" => isset($transaction['createdOn']) ? $transaction['createdOn'] : null,
			"selected_currency" => isset($transaction['selectedCurrency']) ? $transaction['selectedCurrency'] : null,
			"transaction_type_id" => isset($transaction['transactionTypeID']) ? $transaction['transactionTypeID'] : null,
			"signalr_connection_id" => isset($transaction['signalrConnectionID']) ? $transaction['signalrConnectionID'] : null,
			"ef_update_deposit" => isset($transaction['eFUpdateDeposit']) ? $transaction['eFUpdateDeposit'] : null,
			"status" => isset($response['statusCode']) ? $response['statusCode'] : 0,
			"message" => isset($response['errorMessage']) ? $response['errorMessage'] : '',
		);
	}
	
	public function get_transaction_details($transactionRequestID) {
		
		$data = array(
			"transactionRequestID" =>  $transactionRequestID
		);
		
		$method = 'POST';
		
		$api = 'GetTransactionDetails';
		
		$headers = array(
			"Content-Type: application/json"
		);
		
		$response = $this->_call($api, $method, $data, $headers);
		
		//wc_fc_write_log('GetTransactionDetails Request');
		//wc_fc_write_log($data);
		//wc_fc_write_log('GetTransactionDetails Response');
		//wc_fc_write_log($response);
		
		
		$transaction = isset($response['transactionRequest']) ? $response['transactionRequest'] : [];
		
		return array(
			"transaction_pay_id" => isset($transaction['transactionPayID']) ? $transaction['transactionPayID'] : null,
			"transaction_request_id" => isset($transaction['transactionRequestID']) ? $transaction['transactionRequestID'] : null,
			"merchant_user_id" => isset($transaction['merchantUserId']) ? $transaction['merchantUserId'] : null,
			"customer_user_id" => isset($transaction['customerUserId']) ? $transaction['customerUserId'] : null,
			"merchant_push_token" => isset($transaction['merchantPushToken']) ? $transaction['merchantPushToken'] : null,
			"customer_push_token" => isset($transaction['customerPushToken']) ? $transaction['customerPushToken'] : null,
			"amount" => isset($transaction['amount']) ? $transaction['amount'] : null,
			"volume_trade" => isset($transaction['volumeTrade']) ? $transaction['volumeTrade'] : null,
			"converted_price" => isset($transaction['convertedPrice']) ? $transaction['convertedPrice'] : null,
			"funds_type" => isset($transaction['fundsType']) ? $transaction['fundsType'] : null,
			"txt_status" => isset($transaction['status']) ? $transaction['status'] : null,
			"order_id" => isset($transaction['orderID']) ? $transaction['orderID'] : null,
			"created_on" => isset($transaction['createdOn']) ? $transaction['createdOn'] : null,
			"selected_currency" => isset($transaction['selectedCurrency']) ? $transaction['selectedCurrency'] : null,
			"transaction_type_id" => isset($transaction['transactionTypeID']) ? $transaction['transactionTypeID'] : null,
			"signalr_connection_id" => isset($transaction['signalrConnectionID']) ? $transaction['signalrConnectionID'] : null,
			"ef_update_deposit" => isset($transaction['eFUpdateDeposit']) ? $transaction['eFUpdateDeposit'] : null,
			"status" => isset($response['statusCode']) ? $response['statusCode'] : 0,
			"message" => isset($response['errorMessage']) ? $response['errorMessage'] : '',
		);
	}
	
	public function get_ef_address($email, $currency = "FC") {
		
		$method = 'GET';

		$headers = array("Email: $email");
		
		$api = 'EFGenerateAddress/' .  $currency;
		
		$response = $this->_call($api, $method, $data = [], $headers);

		//wc_fc_write_log('EFGenerateAddress Request :: currency => ' . $currency  . ', Email =>' . $email);
		//wc_fc_write_log($data);
		//wc_fc_write_log('EFGenerateAddress Response');
		//wc_fc_write_log($response);
		
		return isset($response['data']['address']) ? $response['data']['address'] : null;
	}
	
	public function get_fc_settings($item = '') {
		$settings = get_option('woocommerce_' . WC_FC_SETTINGS_KEY . '_settings');
		if(!empty($item) && isset($settings[$item])) {
			return $settings[$item];
		}
		return $settings;
	}
	
	public function get_currency_and_fund_type($mid) {
		if($mid > 0) {
			$response = $this->get_merchant_data($mid);
			if(isset($response['organisation'])) {
				return array(
					get_woocommerce_currency(),
					$response['organisation']['receivePaymentType']
				);
			}
		}
		return array(null, null);
	}
	
	public function get_currency($mid) {
		if($mid > 0) {
			$response = $this->get_merchant_data($mid);
			if(isset($response['organisation'])) {
				return $response['organisation']['merchantCurrency'];
			}
		}
		return null;
	}

	public function get_currency_list(){
		$currency = array();

		$method = 'GET';
		$api = 'GetCountries';
		$bodyObject = array();
		
		$all_countries = $this->_call($api, $method, $bodyObject);
		
		if(isset($all_countries['countryList']) && !empty($all_countries['countryList'])) {
			$currency = array_column($all_countries['countryList'], 'currencyName');
			$currency = array_unique($currency);
			sort($currency);
			$currency = array_combine($currency, $currency);
			wc_fc_write_log(print_r($currency, true));
		}
		return $currency;
	}
	
	public function get_maker_fee($id = 26) {
		$makerFee = 0;
		
		$method = 'GET';
		$api = 'GetMarket';
		$bodyObject = array();
		
		$markets = $this->_call($api, $method, $bodyObject);
		
		if(isset($markets['market']) && !empty($markets['market'])) {
			foreach ($markets['market'] as $market) {
				if($market['id'] == $id) {
					$makerFee = $market['makerFee'];
					break;
				}
				
			}
		}
		return $makerFee;
	}
	public function get_accept_forestcoin_details(){
		$method = 'GET';
		$api = 'GetAcceptForestcoinDetails';
		$bodyObject = array();
		
		$items = $this->_call($api, $method, $bodyObject);
		if(isset($items['acceptForestcoinList']) && !empty($items['acceptForestcoinList'])) {
			return $items['acceptForestcoinList'];
		}
		
		return null;
	}
	
	public function add_transaction_log($transaction_request_id, $signal_r_id, $status = WC_FC_STATUS_PENDING, $order_id = 0, $qr_status = 0, $other_data = null) {
		global $wpdb;
		$data = array(
			'id' => null,
			'order_id' => intval($order_id) > 0 ? $order_id : null,
			'transaction_request_id' => $transaction_request_id ? $transaction_request_id : null,
			'signal_r_id' => $signal_r_id ? $signal_r_id : null,
			'status' => $status,
			'qr_status' => $qr_status,
			'data' => $other_data ?  $other_data : null,
		);
		//wc_fc_write_log($data);
		//wc_fc_write_log($wpdb->prefix . "wc_fc_payment_transaction_log");
		return $wpdb->insert($wpdb->prefix . "wc_fc_payment_transaction_log", $data);
	}
	
	private function _call($api, $method, $data, $headers = ['Content-Type' => 'application/json', 'charset' => 'utf-8']) {
		$endpoint = $this->get_fc_settings(WC_FC_API_URL);
		if(empty($endpoint)) {
			return false;
		}
	    $api_url = trim($endpoint, '/') . '/' . $api;
		if(!(empty($headers))) {
			foreach($headers as $key => $value) {
				$pos = strpos($value, ':');
				if ($pos === false) {
					// do nothing
				} else {
					list($arKy,$arVal) = explode(':',$value);
					$headers [$arKy] = $arVal;
					unset($headers[$key]);
				}
			}
		}
		/*if($api=='EFGenerateAddress/FC') {
			echo '<pre>';print_r($headers);echo '</pre>';
			die();
		}*/
		$args = array(
			'headers' => $headers,
			'body' => json_encode($data)
		);
		
		if($method=='POST') {
			$response = wp_remote_post($api_url,$args);
		} else if($method=='GET') {
			$response = wp_remote_get($api_url,$args);
		}
		wc_fc_write_log($data);
		wc_fc_write_log($response);
		if ( is_wp_error( $response ) ) {
			$error_message = $response->get_error_message();
			wc_fc_write_log($error_message);
		} else {
			$body = $response['body'];
		}
		$content = json_decode($body, true);
		return $content;
	}
}
