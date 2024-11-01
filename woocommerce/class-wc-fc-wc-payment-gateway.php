<?php
/**
 * The ForestCoin payment Gateway.
 *
 * @link       https://forestcoin.earth/
 * @since      1.0.0
 *
 * @package    Wc_Fc_Payment_Gateway
 * @subpackage Wc_Fc_Payment_Gateway/admin
 * @author     ForestCoin <sivakumar@tendersoftware.in>
 */
class ForestCoin_Payment_Gateway extends WC_Payment_Gateway
{
	
	/** @var int */
	protected $wc_fc_mid;
	/** @var string  */
	protected $wc_fc_mode;
	/** @var string  */
	protected $wc_fc_api_url;
	/** @var string  */
	protected $wc_fc_email;
	/** @var string  */
	protected $wc_fc_web_url;
	/** @var string  */
	protected $wc_fc_signal_r_url;
	/** @var string  */
	protected $wc_fc_merchant_username;
	/** @var string  */
	protected $wc_fc_merchant_password;
	/** @var string  */
	protected $wc_fc_currency;
	/** @var string  */
	protected $wc_fc_fund_type;
	/** @var string  */
	protected $wc_fc_access_token;
	/** @var string  */
	protected $wc_fc_debug_log_enabled;
	/** @var int  */
	protected $wc_fc_debug_log_clear_interval;
	
	public function __construct() {
		
		$this->id           = WC_FC_SETTINGS_KEY;
		$this->title = __( 'Forestcoin', 'wc-fc-payment-gateway' );
		$this->method_title = __( 'Forestcoin Payment Gateway', 'wc-fc-payment-gateway' );
		$this->has_fields   = true;
		$this->supports     = array(
			'products',
		);
		add_filter(
			'admin_body_class', 
			array(
				$this,
				'fc_admin_body_class',
			)
		);
		$this->init_form_fields();
		$this->init_settings();
		
		$this->method_description = __( 'Accept Forestcoin and immediately autosell for Bitcoin or other cryptocurrency which you can withdraw and cash out to your bank.', 'wc-fc-payment-gateway' );
		$this->description = __("We love the planet and accept Forestcoin, the cryptocurrency where the coins are created by planting trees!", 'wc-fc-payment-gateway');
		
		$this->wc_fc_mode = $this->get_option(WC_FC_MODE);
		$this->wc_fc_api_url = $this->get_option(WC_FC_API_URL);
		$this->wc_fc_merchant_username = $this->get_option(WC_FC_MERCHANT_USERNAME);
		$this->wc_fc_merchant_password = $this->get_option(WC_FC_MERCHANT_PASSWORD);
		$this->wc_fc_currency = $this->get_option(WC_FC_CURRENCY);
		$this->wc_fc_fund_type = $this->get_option(WC_FC_FUND_TYPE);
		$this->wc_fc_debug_log_enabled = $this->get_option(WC_FC_DEBUG_LOG_ENABLED);
		$this->wc_fc_debug_log_clear_interval = $this->get_option(WC_FC_DEBUG_LOG_CLEAR_INTERVAL);
		
		if(!isset($_POST['save'])) {
			$mid = $this->get_option(WC_FC_MID);
			if($mid > 0) {
				list($currency, $fundType) = wc_fc_payment_gateway()->get_currency_and_fund_type($mid);
				if ($fundType) {
					$this->update_option(WC_FC_FUND_TYPE, $fundType);
					$this->wc_fc_fund_type = $fundType;
				}
			}
		}
		
		// Save settings page options, defined in standard settings page file.
		if ( is_admin() ) {
			add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'process_admin_options' ) );
		}
		
		/**
		 * Remove the payment button.
		 */
		
		add_filter(
			'woocommerce_order_button_html',
			array(
				$this,
				'fc_get_custom_payment_button',
			)
		);
		add_filter(
			'woocommerce_pay_order_button_html',
			array(
				$this,
				'fc_get_custom_payment_button',
			)
		);
	}
	
	/**
	 * Validate the provided credentials.
	 *
	 * @param array $data
	 * @return bool|array|null
	 */
	protected function fc_validate_credentials() {
		$errors = [];
		
		$fc_settings = wc_fc_payment_gateway()->get_fc_settings();
		
		$has_username   = (bool) $fc_settings[WC_FC_MERCHANT_USERNAME];
		$has_password   = (bool) $fc_settings[WC_FC_MERCHANT_PASSWORD];
		$has_api_url   = (bool) $fc_settings[WC_FC_API_URL];
		
		if ( ! $has_username ) {
			$errors[] = __( 'Error: Merchant username is required.', 'wc-fc-payment-gateway' );
			wc_fc_write_log( 'ERROR! Missing merchant username.');
		}
		
		if ( ! $has_password ) {
			$errors[] = __( 'Error: Merchant password is required.', 'wc-fc-payment-gateway' );
			wc_fc_write_log( 'ERROR! Missing merchant password.');
		}
		
		if ( ! $has_api_url ) {
			$errors[] = __( 'Error: API Url is required.', 'wc-fc-payment-gateway' );
			wc_fc_write_log( 'ERROR! Missing API Url.');
		}

		if(empty($errors)) {
			$data = wc_fc_payment_gateway()->login($fc_settings[WC_FC_MERCHANT_USERNAME], $fc_settings[WC_FC_MERCHANT_PASSWORD]);
			if (isset($data['status']) && intval($data['status']) == 0) {
				$errors[] = __( 'Error:  Wrong Credentials or API Url.', 'wc-fc-payment-gateway' );
				wc_fc_write_log( 'ERROR! Wrong Credentials or API Url.');
			} else {
				wc_fc_write_log( 'SUCCESS Merchant Credentials OK.');
				return $data;
			}
		}
		
		if ( ! empty( $errors ) ) {
			foreach ( $errors as $message ) {
				WC_Admin_Settings::add_error( $message );
			}
			return false;
		}
		
		return null;
	}
	
	
	public function init_form_fields() {
		$form_fields = include plugin_dir_path( dirname( __FILE__ ) ) . 'woocommerce/wc-fc-payment-gateway-settings-page.php';
		if(!(intval($this->get_option(WC_FC_MID)) > 0)) {
			unset($form_fields[WC_FC_FUND_TYPE]);
		}		
		if(isset($form_fields[WC_FC_FUND_TYPE])) {
			$items = wc_fc_payment_gateway()->get_accept_forestcoin_details();
			if(!empty($items)) {
				$server_url = str_replace('/api/', '', $this->get_option(WC_FC_API_URL));
				$options =  array();
				$description = '';
				foreach ($items as $item) {
					$options[$item['name']] = __( $item['tilte'], 'wc-fc-payment-gateway' );
					$description .= '<li id="woo-fc-ft-' . strtolower($item['name']) . '"><span><img src="'.$server_url . $item['imagepath'] .'"></span><span><b>' . __( $item['tilte'], 'wc-fc-payment-gateway' ) . '</b> : ' . $item['description'] . '</span></li>';
				}
				$description = '<span id="woo-fc-currency">' . get_woocommerce_currency() . '</span><ul id="woo-fc-ft">' . $description . '</ul>';
				$form_fields[WC_FC_FUND_TYPE]['options'] = $options;
				$form_fields[WC_FC_FUND_TYPE]['description'] = $description;
				$form_fields[WC_FC_FUND_TYPE]['default'] = $items[0]['name'];
			}
		}
		$this->form_fields = apply_filters( 'wc_fc_form_fields', $form_fields);
	}
	
	public function process_admin_options() {
		
		parent::process_admin_options();
		
		wc_fc_write_log('Saving API settings...');
		$enable = $this->get_option('enabled');
		if($this->enabled != $enable) {
			wc_fc_write_log('FC Payment gateway is ' . ($enable == 'yes' ? 'enabled' : 'disabled'));
		}
		
		$data = $this->fc_validate_credentials();
		
		if(isset($_POST['save'])) {
			if(isset($data['status']) &&  $data['status'] > 0) {
				$this->wc_fc_mid = $data['mid'];
				$this->wc_fc_email = $data['email'];
				$this->wc_fc_user_access = $data['access_token'];
				$this->wc_fc_web_url = $data['web_url'];
				$this->wc_fc_signal_r_url = $data['signal_r_url'];
				$this->update_option(WC_FC_MID, $this->wc_fc_mid);
				wc_fc_write_log('Updating merchant id ' . $data['mid']);
				$this->update_option(WC_FC_EMAIL, $this->wc_fc_email);
				wc_fc_write_log('Updating email id ' . $data['email'] . ' for merchant ' . $data['mid']);
				$this->update_option(WC_FC_ACCESS_TOKEN, $this->wc_fc_access_token);
				$this->update_option(WC_FC_WEB_URL, $this->wc_fc_web_url);
				wc_fc_write_log('Updating web url ' . $data['web_url']);
				$this->update_option(WC_FC_SIGNAL_R_URL, $this->wc_fc_signal_r_url);
				wc_fc_write_log('Updating signal r url ' . $data['signal_r_url']);

				// Update merchant data
				$mdata["userID"] = $data['mid'];
				$mdata["webPayUniqueKey"] = $data['organisation']['web_pay_key'];
				$mdata["receivePaymentType"] = isset($_POST['woocommerce_' . WC_FC_SETTINGS_KEY . '_' . WC_FC_FUND_TYPE]) ? wc_clean($_POST['woocommerce_' . WC_FC_SETTINGS_KEY . '_' . WC_FC_FUND_TYPE]) : $this->wc_fc_fund_type;
				$mdata["merchantCurrencyId"] = $data['organisation']['m_currency_id'];
				$mdata["merchantCurrency"] = $data['organisation']['m_currency'];
				$mdata["acceptPaymeny"] = (bool) $data['organisation']['accept_payment'];
				$mdata["bReferralPage"] = (bool) $data['organisation']['allow_referral_page'];
				$mdata["isOnlineShopping"] = true;
				$mdata["shoppingCartURL"] = home_url();
				$this->update_option(WC_FC_CURRENCY, get_woocommerce_currency());
				
				if(wc_fc_payment_gateway()->update_merchant_data($mdata)) {
					wc_fc_write_log('SUCCESS Updating receive payment type for merchant ' . $data['mid'] . ' from ' . $this->wc_fc_fund_type . ' to ' . $mdata["receivePaymentType"]);
				} else {
					wc_fc_write_log('ERROR! Updating receive payment type for merchant ' . $data['mid'] . ' from ' . $this->wc_fc_fund_type . ' to ' . $mdata["receivePaymentType"]);
				}
				wc_fc_clear_log($this->get_option(WC_FC_DEBUG_LOG_CLEAR_INTERVAL));
			} else {
				$this->update_option(WC_FC_MID, 0);
				wc_fc_write_log('Updating merchant id as empty');
				$this->update_option(WC_FC_EMAIL, '');
				wc_fc_write_log('Updating email id as empty');
				$this->update_option(WC_FC_ACCESS_TOKEN, '');
				$this->update_option(WC_FC_WEB_URL, '');
				wc_fc_write_log('Updating web url as empty');
			}
		}
	}
	
	public function fc_get_custom_payment_button($button_html) {
		global $wp;
		
		$output = '';
		$merchant = $this->_fc_get_merchant_details_from_api();
		//echo '<pre>';print_r($merchant);echo '</pre>';
		if(!(isset($merchant['mid']) && $merchant['mid'] > 0)) {
			wc_fc_write_log('ERROR! Invalid merchant, So skipped FC button');
			return $button_html;
		}
		
		if ( is_checkout_pay_page() ) {
			$data_order_id = get_query_var( 'order-pay' );
			$order         = wc_get_order( $data_order_id );
			
			$data_amount   = esc_attr( ( ( WC()->version < '2.7.0' ) ? $order->order_total : $order->get_total() ) );
			$data_currency = esc_attr( ( ( WC()->version < '2.7.0' ) ? $order->order_currency : $order->get_currency() ) );
			wc_fc_write_log('Amount : ' .$data_currency . $data_amount . ' from the order ' . $data_order_id);
		} else {
			$data_amount   = esc_attr( WC()->cart->total );
			$data_currency = esc_attr( strtoupper( get_woocommerce_currency() ) );
			wc_fc_write_log('Amount : ' .$data_currency . $data_amount . ' from the cart');
		}

		
		$merchant_code_nonce_action = '_wc_fc_get_merchant_code_nonce';
		$merchant_code_url       = WC_AJAX::get_endpoint( 'wc_fc_get_merchant_code' );
		$merchant_code_nonce_url = wp_nonce_url( $merchant_code_url, $merchant_code_nonce_action );
		wc_fc_write_log('Merchant code nonce url : ' . $merchant_code_nonce_url );

		$output .= "<div id='wc-fc-payment-gateway-checkout-wrapper' style='display:none;'>";
		$output .= '<input type="hidden" value="' . $data_currency. '" id="wc-fc-payment-gateway-currency">';
		$output .= '<input type="hidden" value="' . $data_amount. '" id="wc-fc-payment-gateway-amount">';
		$output .= '<input type="hidden" value="" id="woo-fc-pay-id" name="woo-fc-pay-id">';
		$output .= "<div id='wc-fc-payment-gateway-merchant-code-url' style='display:none;' data-value='$merchant_code_nonce_url'></div>";
		$output .= "<button id='wc-fc-payment-gateway-btn' type='button' title='Show payment QR code' style='margin:auto;display:block;' class='button alt wc-fc-payment-gateway-btn'>Pay with Forestcoin</button>";
		$output .= "<div id='wc-fc-payment-gateway-script-wrapper' style='display:none'>";
		$output .= "<div class='woo-fc-pay-qr'>";
		
		//$output .= '<div id="woo-fc-transaction"></div>';
		$output .= '<div id="woo-fc-transaction-section" style="display:none">';
		$output .= '<div id="woo-fc-transaction"><span class="woo-fc-success-icon"><svg aria-hidden="true" focusable="false" data-prefix="far" data-icon="check-circle" role="img" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512" class="svg-inline--fa fa-check-circle fa-w-16 fa-3x"><path fill="currentColor" d="M256 8C119.033 8 8 119.033 8 256s111.033 248 248 248 248-111.033 248-248S392.967 8 256 8zm0 48c110.532 0 200 89.451 200 200 0 110.532-89.451 200-200 200-110.532 0-200-89.451-200-200 0-110.532 89.451-200 200-200m140.204 130.267l-22.536-22.718c-4.667-4.705-12.265-4.736-16.97-.068L215.346 303.697l-59.792-60.277c-4.667-4.705-12.265-4.736-16.97-.069l-22.719 22.536c-4.705 4.667-4.736 12.265-.068 16.971l90.781 91.516c4.667 4.705 12.265 4.736 16.97.068l172.589-171.204c4.704-4.668 4.734-12.266.067-16.971z" class=""></path></svg></span>';
		$output .= '<div id="woo-fc-result-message">Transaction Confirmed</div>';
		$output .= '<div id="woo-fc-result-list"><span class="woo-fc-heading">SENT</span>';
		$output .= '<div id="woo-fc-sentreceived-value"><span class="woo-fc-volume-trade">2.3027</span> <span>FC</span></div>';
		$output .= '<div id="woo-fc-sentreceived-currencyvalue"><span class="currencyvalue">(USD 1)</span></div>';
		//$output .= '<div id="woo-fc-converted" style="display:none"></div>';
		//$output .= '<div id="woo-exchange-text">Funds have been credited to your Exchangeforest.com account</div>';
		$output .= '</div>';
		$output .= '<div id="woo-fc-pay-transaction-details">';
		$output .= '<div class="woo-fc-deposit-addr"><span>Transaction ID</span><span id="woo-fc-transaction-id"></span></div>';
		//$output .= '<div class="woo-fc-deposit-addr" id="order-detail"><span>Order ID</span><span>324234234</span></div>';
		$output .= '<div class="woo-fc-deposit-addr"><span>Date</span><span id="created-on">01-09-2020 18:35</span></div>';
		$output .= '</div>';
		$output .= '</div>';
		$output .= '</div>';
		
		$output .= '<div id="woo-fc-pay-qr-section">';
		$output .= '<div class="woo-pay-to-details"><span>Pay To :</span>&nbsp;<span> ' . $merchant['organisation']['name'] . '  : ' . $merchant['location'] . '</span></div>';
		$output .= '<div id="woo-fc-conversion"></div>';
		$output .= '<div id="woo-fc-qrcode-status"></div>';

		$output .= '<div id="woo-pay-qr-inner" class="woo-pay-qr-section">';

		$output .= '<div id="woo-fc-refresh-text" style="display:none;">Rate will refresh in <span id="woo-fc-refresh-rate">0</span> seconds</div>'; // Added for auto reload 22122021

		$output .= '<div id="woo-fc-qr-img-cntnr">';
		$output .= '<div id="woo-fc-get-transaction-id" data-transactionid="0"></div>'; // Added for auto reload 22122021
		$output .= '<div id="woo-fc-qrcode"></div>';
		$output .= '<div id="app-demo-img-cntnr"><img src="'.plugin_dir_url( __DIR__ ) .'public/img/app-demo.png" /></div>';
		$output .= '</div>';
		$output .= '<div id="woo-fc-qrcode-text" style="display:none;">(Scan the QR code with your Forestcoin smartphone app)</div>';
		$output .= '<div class="deposit-addr"><span>Deposit Address:</span>&nbsp;<span id="deposite-address"></span></div>';
		$output .= "<button id='woo-fc-refresh-btn' type='button' title='Refresh' style='margin:auto;display:none;' class='button fc-refresh-btn alt'>Refresh</button>";
		$output .= '</div>';
		$output .= '</div>';
		
		
		
		$output .= '</div>';
		$output .= '</div>';
		$output .= '</div>';
		$output .= '';
		
		return $button_html . $output;
	}
	
	public function process_payment( $order_id ) {
		wc_fc_write_log('Process Payment for the order id : ' . $order_id );
		$order = wc_get_order( $order_id );
		$result = 'failure';
		$status = 'failed';
		$messages =  __( 'Payment failed', 'wc-fc-payment-gateway' );
		$transaction = [];
		$db_status = WC_FC_STATUS_FAILED;
		if(empty($order)) {
			$messages =  __( 'Empty Order', 'wc-fc-payment-gateway' );
			wc_fc_write_log('ERROR! Process Payment : Empty order');
		} elseif (!isset($_POST['woo-fc-pay-id'])) {
			$messages =  __( 'No payment id', 'wc-fc-payment-gateway' );
			wc_fc_write_log('ERROR! Process Payment : No payment id');
		} else {
			$transaction = wc_fc_payment_gateway()->get_transaction_details(WC()->session->get('tid'));
			if(isset($transaction['transaction_pay_id']) && $transaction['transaction_pay_id'] > 0 && $_POST['woo-fc-pay-id'] == $transaction['transaction_pay_id'] && $transaction['txt_status'] == 'Success') {
				wc_fc_write_log('SUCCESS Done Payment for the order id : ' . $order_id . ' with transaction id ' . $transaction['transaction_request_id']);
				// Remove cart
				WC()->cart->empty_cart();
				$result = 'success';
				$status = 'completed';
				$db_status = WC_FC_STATUS_SUCCESS;
				$messages = __('Done payment', 'wc-fc-payment-gateway');

				//updated on 20122021 add a order notes
				$note = __("Done payment <br> Transaction Id : ".$transaction['transaction_pay_id']);
				$order->add_order_note( $note );

			} else {
				wc_fc_write_log('ERROR! Failed Payment for the order id : ' . $order_id . ' with transaction id ' . $transaction['transaction_request_id']);
			}
		}
		wc_fc_payment_gateway()->add_transaction_log(WC()->session->get('tid'), WC()->session->get('sid'), $db_status, $order_id, 1, null);
		
		// Mark as on-hold (we're awaiting the payment)
		$order->update_status($status, $messages);
		
		// Return thankyou redirect
		return array(
			'result'    => $result,
			'messages'  => $messages,
			'redirect'  => $result != 'failure' ? $this->get_return_url( $order ) : ''
		);
	}
	
	public function get_icon() {
		$icon_html = '';
		
		$image_url = plugin_dir_url( __DIR__ ) . 'public/img/fc-logo.png';
		$about_url = wc_fc_payment_gateway()->get_fc_settings(WC_FC_WEB_URL);
		wc_fc_write_log('FC icon image url :' . $image_url);
		wc_fc_write_log('FC sign up url :' . $about_url);
		$icon_html .= '<img src="' . esc_attr( $image_url ) . '" class="woo-fc-logo" alt="' . esc_attr__( 'Forestcoin acceptance mark', 'wc-fc-payment-gateway' ) . '" />';
		
		
		$icon_html .= '<a href="' . $about_url . '" class="woo-fc-about" target="_blank">' . esc_attr__( 'Signup for Forestcoin here', 'wc-fc-payment-gateway' ) . '</a>';
		
		return apply_filters( 'woocommerce_gateway_icon', $icon_html, $this->id );
	}
	/**
	 * Handle AJAX request to start checkout flow, first triggering form
	 * validation if necessary.
	 *
	 * @since 1.0.0
	 */
	public static function wc_fc_get_merchant_code() {
		$status = 1;
		$message = '';
		$data = '';
		$tid = '';
		$qr_code = '';
		
		if ( ! wp_verify_nonce( $_GET['_wpnonce'], '_wc_fc_get_merchant_code_nonce' ) ) {
			$status = 0;
			$message = __( 'Bad attempt', 'wc-fc-payment-gateway' );
			wc_fc_write_log('ERROR! Get merchant qr code data : Bad attempt  - ' . $_GET['_wpnonce'] );
		}
		
		$amount = isset($_POST['amount']) ? wc_clean($_POST['amount']) : '';
		$currency = isset($_POST['currency']) ? wc_clean($_POST['currency']) : '';
		$sid = isset($_POST['sid']) ? wc_clean($_POST['sid']) : '';
		
		 // updated for auto reload 22122021
		$previous_tid = '';
		if(isset($_POST['previous_tid']) && $_POST['previous_tid'] != '0'){
			$previous_tid = wc_clean($_POST['previous_tid']);
		}

		$merchant_code = self::_fc_get_merchant_code($amount, $currency, $sid, $previous_tid); // updated for auto reload 22122021
		//echo "<pre>"; print_r($merchant_code); echo "</pre>";
		$status = $merchant_code['status'];
		if($status == 1) {
			if($previous_tid == ''){
				$tid = $merchant_code['transaction']['transaction_request_id'];
			}else{
				$tid = $previous_tid;
			}
			$qr_code = $merchant_code['qr_code'];
			wc_fc_write_log('SUCCESS Get merchant qr code data : qr code : ' . $qr_code);
			wc_fc_payment_gateway()->add_transaction_log($tid, $sid, WC_FC_STATUS_PENDING, 0, 1, $qr_code);
		} else {
			$message = $merchant_code['error'] ? $merchant_code['error'] : __( 'Unable to generate QR Code', 'wc-fc-payment-gateway' );
			wc_fc_write_log('ERROR! Get merchant qr code data : Unable to generate QR Code - ' . $merchant_code['error']);
			wc_fc_payment_gateway()->add_transaction_log($tid, $sid, WC_FC_STATUS_PENDING, 0, 0, $merchant_code['error']);
		}
		
		$one_fc_price = '';
		
		if($merchant_code['fund_type'] && $merchant_code['fund_type'] != 'FC') {
			$one_fc_price .= '1 FC = ' . $merchant_code['fund_type'] . ' ';
			$one_fc_price .= rtrim(rtrim($merchant_code['open_order']['one_fc_price'], '0'), '.');
		}
		$amount_string = str_replace('.00', '', $amount);

		if(isset($merchant_code['open_order']['merchant_payout']) && $merchant_code['open_order']['merchant_payout'] != null) {
			$merchant_payout = $merchant_code['open_order']['merchant_payout'];
		}

		$response = array(
			'tid' => $tid,
			//'asdf'=>$sid,
			'volume_trade_txt' => (isset($merchant_code['open_order']['volume_trade'])) ? $merchant_code['open_order']['volume_trade'] : '' . '<span>FC</span>',
			'local_amount' => '(' . $currency . ' ' . rtrim(rtrim($amount, '0'), '.') . ')',
			'fiat_currency' => (isset($merchant_code['open_order']['fiat_currency'])) ? $merchant_code['open_order']['fiat_currency'] : '',
			'one_fc_price' => $one_fc_price,
			'merchant_payout' => (isset($merchant_payout)) ? $merchant_payout : '',
			'qr_code' => $qr_code,
			'refresh_second' => (isset($merchant_code['open_order']['refresh_second'])) ? $merchant_code['open_order']['refresh_second'] : '',
			'ef_address' => $merchant_code['ef_address'],
			'status' => $status,
			'message' => $message
		);
		
		wp_send_json($response);
	}
	
	private static function _fc_get_merchant_code($amount, $currency, $sid, $transactionid = '') {  // Added $transactionid = '' for auto reload 22122021
		$status = 0;
		$open_order = null;
		$transaction = null;
		$qr_code = null;
		$volume_trade = null;
		
		$merchant = self::_fc_get_merchant_details();
		
		if(isset($merchant['mid']) && intval($merchant['mid']) == 0) {
			wc_fc_write_log('MERCHANT_FAILED - Merchant not found ');
			return array(
				'status' => 0,
				'open_order' => '',
				'transaction' => '',
				'qr_code' => '',
				'fund_type' => '',
				'ef_address' => '',
				'errorCode' => 'MERCHANT_FAILED',
				'error' => 'Unable to find the merchant'
			);
		}
		
		$merchant_ef_address = self::_fc_get_merchant_address();
		if(empty($merchant_ef_address)) {
			wc_fc_write_log('EF_ADDRESS_FAILED - Merchant EF address not found ');
			return array(
				'status' => 0,
				'open_order' => '',
				'transaction' => '',
				'qr_code' => '',
				'fund_type' => '',
				'ef_address' => '',
				'errorCode' => 'EF_ADDRESS_FAILED',
				'error' => 'Unable to find the merchant wallet address'
			);
		}
		
		$merchantId = $merchant['mid'];
		list($currency, $fund_type) = wc_fc_payment_gateway()->get_currency_and_fund_type($merchant['mid']);
		$error = '';
		$errorCode = '';
		
		$open_order = wc_fc_payment_gateway()->get_open_order($merchantId, $amount, $currency, $fund_type);
		$status = $open_order['status'];
		$error = $open_order['message'];
		if($status == 1) {
			$log = 'SUCCESS Open order success for ammount : ' . $amount . ', currency:' . $currency .  ', fund_type :' . $fund_type;
			$volume_trade = $open_order['volume_trade'];
			$barcode_text = $open_order['barcode_text'];
			if ($fund_type == 'BTC' || $fund_type == 'USDT') {
				if(empty($open_order['other'])) {
					$log = 'ERROR! Open order failed - No other data for ammount : ' . $amount . ', currency:' . $currency .  ', fund_type :' . $fund_type;
					$status = 0;
					$errorCode = 'OPEN_ORDER_CONVERSION_FAILED';
				}
			}
			wc_fc_write_log($errorCode  . ' - ' .  $log);
		} else {
			$errorCode = 'OPEN_ORDER_FAILED';
			wc_fc_write_log($errorCode . ' - Open order failed for ammount : ' . $amount . ', currency:' . $currency .  ', fund_type :' . $fund_type);
			wc_fc_write_log($errorCode  . ' - ' .  $error);

		}
		if($status == 1) {
			$transaction = wc_fc_payment_gateway()->get_transaction_request($merchantId, $amount, $currency, $fund_type, $volume_trade, $sid, $barcode_text);
			
			$status = $transaction['status'];
			$error = $transaction['message'];
			if($status == 1) {
				if($transactionid != ''){ // Added for auto reload 22122021
					$transaction['transaction_request_id'] = $transactionid;
				}
					wc_fc_write_log('SUCCESS Transaction request id success ' . $transaction['transaction_request_id']);
					WC()->session->set('tid', $transaction['transaction_request_id']);
					WC()->session->set('sid', $sid);
					$qr_code = self::_fc_get_merchant_qrcode($merchant, $open_order, $amount, $currency, $fund_type, $merchant_ef_address, $transaction['transaction_request_id']);

			} else {
				$errorCode = 'TRANSACTION_FAILED';
				wc_fc_write_log($errorCode . ' - Transaction request id failed for ammount : ' . $amount . ', currency:' . $currency .  ',fund_type :' . $fund_type . ', volume_trade :' . $volume_trade .', : signal r id ' . $sid);
				wc_fc_write_log($errorCode . ' - ' .  $error);
			}
		}
		
		return array(
			'status' => $status,
			'open_order' => $open_order,
			'transaction' => $transaction,
			'qr_code' => $qr_code,
			'fund_type' => $fund_type,
			'ef_address' => $merchant_ef_address,
			'errorCode' => $errorCode,
			'error' => $error
		);
	}
	
	private static function _fc_get_merchant_qrcode($merchant, $open_order, $amount, $currency, $fund_type, $fc_address, $transaction_request_id) {
		
		$m_id = $merchant['mid'];
		$m_location = $merchant['location'];
		$m_picture = $merchant['profile'];
		$m_name = $merchant['organisation']['name'];
		$m_address = $merchant['organisation']['address'];
		$m_website = $merchant['organisation']['website'];
		$volume_trade = $open_order['volume_trade'];
		$one_fc_price = $open_order['one_fc_price'];
		$barcodetext = $open_order['barcode_text'];

		$qrfind = array("{m_id}", "{m_nm}", "{m_add}", "{m_web}", "{m_loc}", "{m_img}", "{fcadd}", "{trn_id}");
		$qrreplace = array($m_id, $m_name, $m_address, $m_website, $m_location, $m_picture, $fc_address, $transaction_request_id);
		$qr_code = str_replace($qrfind, $qrreplace, $barcodetext);

		//$qr_code = "m_id:$m_id||m_nm:$m_name||m_add:$m_address||m_web:$m_website||m_loc:$m_location||m_img:$m_picture||curr:$currency||fund:$fund_type||vol:$volume_trade||fcadd:$fc_address||fcval:$amount||1_fc:$one_fc_price||trn_id:$transaction_request_id";
		return $qr_code;
	}
	
	private static function _fc_get_merchant_details() {
		
		$merchant = null;
		$merchantId = wc_fc_payment_gateway()->get_fc_settings(WC_FC_MID);
		if(!(intval($merchantId) > 0 )) {
			return $merchant;
		}
		
		if ( WC()->session !== null ) {
			wc_fc_write_log('Getting merchant details from session');
			$merchant = WC()->session->get( 'merchant_' .$merchantId);
			//echo '<pre>';print_r($merchant);echo '</pre>';
		}
		if(empty($merchant)) {
			$username =  wc_fc_payment_gateway()->get_fc_settings(WC_FC_MERCHANT_USERNAME);
			$password =  wc_fc_payment_gateway()->get_fc_settings(WC_FC_MERCHANT_PASSWORD);
			
			if ($username && $password) {
				$user = wc_fc_payment_gateway()->login($username, $password);
				if (isset($user['status']) && $user['status'] == 1) {
					wc_fc_write_log('Setting merchant details in session');
					WC()->session->set('merchant_' . $merchantId, $user);
					$merchant = $user;
				}
			}
		}
		return $merchant;
	}
	
	private static function _fc_get_merchant_details_from_api() {
		
		$merchant = null;
		$merchantId = wc_fc_payment_gateway()->get_fc_settings(WC_FC_MID);
		//echo '===>'.$merchantId;
		if(!(intval($merchantId) > 0 )) {
			return $merchant;
		}
		$username =  wc_fc_payment_gateway()->get_fc_settings(WC_FC_MERCHANT_USERNAME);
		$password =  wc_fc_payment_gateway()->get_fc_settings(WC_FC_MERCHANT_PASSWORD);
		
		if ($username && $password) {
			$user = wc_fc_payment_gateway()->login($username, $password);
			//echo '<pre>';print_r($user);echo '</pre>';
			if (isset($user['status']) && $user['status'] == 1) {
				wc_fc_write_log('Setting merchant details in session');
				WC()->session->set('merchant_' . $merchantId, $user);
				$merchant = $user;
			}
		}
		return $merchant;
	}
	
	private static function _fc_get_merchant_address() {
		$merchant_address = null;
		$merchantId = wc_fc_payment_gateway()->get_fc_settings(WC_FC_MID);
		if(!(intval($merchantId) > 0 )) {
			return $merchant_address;
		}
		
		
		/*
		if ( WC()->session !== null ) {
			$merchant_address = WC()->session->get( 'merchant_address_' . $merchantId );
			wc_fc_write_log('Getting EF address from session :' . $merchant_address);
		}
		*/
		if(empty($merchant_address)) {
			$merchant_email = wc_fc_payment_gateway()->get_fc_settings(WC_FC_EMAIL);
			$merchant_fund_type = wc_fc_payment_gateway()->get_fc_settings(WC_FC_FUND_TYPE);

			if(empty($merchant_email) || empty($merchant_fund_type)) {
				$merchant = self::_fc_get_merchant_details();
				if(isset($merchant['email']) && !empty($merchant['email'])) {
					$merchant_email = $merchant['email'];
				}
				if(isset($merchant['organisation']['fund_type']) && !empty($merchant['organisation']['fund_type'])) {
					$merchant_fund_type = $merchant['organisation']['fund_type'];
				}
			}
			$merchant_address = wc_fc_payment_gateway()->get_ef_address($merchant_email, $merchant_fund_type);
			
			if(!empty($merchant_address)) {
				wc_fc_write_log('Setting EF address in session ' . $merchant_address);
				WC()->session->set( 'merchant_address_' . $merchantId , $merchant_address);
			}
		}
		return $merchant_address;
	}
	public function fc_admin_body_class($classes) {
		$pageURL = $_SERVER["SERVER_NAME"].$_SERVER["REQUEST_URI"];
		if(strpos($pageURL, 'section=fc_payment_gateway') !== false){
			$classes .= ' fc_admin_settings';
		}
		return $classes;
	}
}