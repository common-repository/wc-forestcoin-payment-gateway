<?php
return array(
	
	'enabled' => array(
		'title'   => __( 'Enable/Disable', 'wc-fc-payment-gateway' ),
		'description' => __( 'Enable/Disable Forestcoin payments', 'wc-fc-payment-gateway' ),
		'type'    => 'checkbox',
		'default' => 'yes',
	),

	WC_FC_MODE => array(
		'title'       => __( 'Test or Live Mode', 'wc-fc-payment-gateway' ),
		'type'        => 'select',
		'options'     => array(
			'test' => __( 'Test Mode', 'wc-fc-payment-gateway' ),
			'live' => __( 'Live Mode', 'wc-fc-payment-gateway' ),
		),
		'class'       => 'wc-enhanced-select',
		'description' => __( 'Select LIVE mode when ready to accept Forestcoin payments from the public.', 'wc-fc-payment-gateway' ),
		'default'     => 'test',
		'desc_tip'    => false,
	),
	
	'wc_fc_api_url_ui' => array(
		'title'       => __( 'API Url*', 'wc-fc-payment-gateway' ),
		'type'        => 'select',
		'options'     => array(
			'test' => __( 'https://uat.forestcoin.earth/api/', 'wc-fc-payment-gateway' ),
			'live' => __( 'https://admin.forestcoin.earth/api/', 'wc-fc-payment-gateway' ),
		),
		'class'       => 'wc-enhanced-select',
		'readonly'    => true,
		'disabled'    => true,
		'description' => __( 'Forestcoin API Url', 'wc-fc-payment-gateway' ),
		'desc_tip'    => false,
	),
	
	WC_FC_MERCHANT_USERNAME => array(
		'title'       => __( 'Merchant Username*', 'wc-fc-payment-gateway' ),
		'type'        => 'text',
		'description' => __( 'Forestcoin Login Email', 'wc-fc-payment-gateway' ),
		'desc_tip'    => false,
	),
	
	WC_FC_MERCHANT_PASSWORD => array(
		'title'       => __( 'Merchant Password*', 'wc-fc-payment-gateway' ),
		'type'        => 'password',
		'description' => __( 'Forestcoin Password', 'wc-fc-payment-gateway' ),
		'desc_tip'    => false,
	),
	WC_FC_FUND_TYPE => array(
		'title'       => __( 'Receive Payment as', 'wc-fc-payment-gateway' ),
		'type'        => 'select',
		'options'     => '',
		'class'       => 'wc-enhanced-select',
		'description' => '',
		'desc_tip'    => false,
	),
	
	WC_FC_DEBUG_LOG_ENABLED => array(
		'title'   => __( 'Enable debug log', 'wc-fc-payment-gateway' ),
		'label'   => __( 'Enable debug log', 'wc-fc-payment-gateway' ),
		'type'    => 'checkbox',
		'default' => 'no',
	),

	WC_FC_DEBUG_LOG_CLEAR_INTERVAL => array(
		'title'       => __( 'Clear debug log interval', 'wc-fc-payment-gateway' ),
		'label' => __( 'In Days', 'wc-fc-payment-gateway' ),
		'description' => __( 'It will maintain the logs for given x days', 'wc-fc-payment-gateway' ),
		'type'        => 'text',
		'class'        => 'input-small',
		'default'     => '9',
	),
	WC_FC_EMAIL => array(
		'type'    => 'hidden',
	),
	
	WC_FC_API_URL => array(
		'type'    => 'hidden',
	),

);
