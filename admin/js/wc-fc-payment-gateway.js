(function( $ ) {
    'use strict';

    /**
     * All of the code for your admin-facing JavaScript source
     * should reside in this file.
     *
     * Note: It has been assumed you will write jQuery code here, so the
     * $ function reference has been prepared for usage within the scope
     * of this function.
     *
     * This enables you to define handlers, for when the DOM is ready:
     *
     * $(function() {
	 *
	 * });
     *
     * When the window is loaded:
     *
     * $( window ).load(function() {
	 *
	 * });
     *
     * ...and/or other possibilities.
     *
     * Ideally, it is not considered best practise to attach more than a
     * single DOM-ready or window-load handler for a particular page.
     * Although scripts in the WordPress core, Plugins and Themes may be
     * practising this, we should strive to set a better example in our own work.
     */

    var fcMode = '#woocommerce_fc_payment_gateway_wc_fc_mode';
    var fcApiUi = '#woocommerce_fc_payment_gateway_wc_fc_api_url_ui';
    var fcApi = '#woocommerce_fc_payment_gateway_wc_fc_api_url';
    var fcFundType = '#woocommerce_fc_payment_gateway_wc_fc_fund_type';

    $(document).ready(function () {
        setApiUrl($(fcMode).val());
        $(fcMode).change(function () {
            setApiUrl($(this).val());
        });
        setFundType();
    });

    function setFundType() {
        var c = $('#woo-fc-currency').text();
        $('.cointype').text(c);
        $('.current_base').text(c != 'USD' ? ' based on the current ' + c + ' > FC > USDT rate' : '');
    }

    function setApiUrl(v) {
        $(fcApiUi).val(v);
        $(fcApiUi).select2().trigger('change');
        var apiData = $(fcApiUi).select2('data');
        if(apiData && apiData[0]) {
            $(fcApi).val(apiData[0].text);
        }
    }
})( jQuery );