var wooFcConnection;

var wooFcConnectionId;

var wooFcTransactionId;

var wooFcIntervalInSec;

var wooFcSetInterval;

var wooFcRunDuration;

var wooFcQrCode;

var wooFcRepo = null;

if(wooFc) {

    wooFcConnection = new signalR.HubConnectionBuilder()

        .configureLogging(signalR.LogLevel.Debug)

        .withUrl(wooFc.signalRUrl, {

            skipNegotiation: false,

            transport: signalR.HttpTransportType.None

        }).withAutomaticReconnect([0, 3000, 5000, 10000, 15000, 30000])

        .build();



    wooFcConnection.on("sendmessage", function (type, message) {

        wooFcRepo = JSON.parse(message);

        fcSetResponse(wooFcRepo);

    });



    async function start() {

        try {

            await

            wooFcConnection.start();

            wooFcConnectionId = wooFcConnection.connectionId;

        } catch (err) {

            console.log(err);

        }

    };



    start();



}



function fcSetResponse(message) {
    
    console.log('fcSetResponse',message);

    if (message && message.TransactionRequestID && wooFcTransactionId == message.TransactionRequestID) {

        //var showConversion = (message.FundsType.toUpperCase() === 'BTC' || message.FundsType.toUpperCase() === 'USDT');

        jQuery('#woo-fc-sentreceived-value .woo-fc-volume-trade').text(message.VolumeTrade);

        jQuery('#woo-fc-sentreceived-currencyvalue .currencyvalue').text('(' + message.SelectedCurrency + ' ' + message.Amount + ')');

        jQuery('#woo-fc-transaction-id').text(message.TransactionRequestID);

        jQuery('#created-on').text(message.CreatedOn);

        /*if(showConversion) {

            jQuery('#woo-fc-converted').text('Converted to ' + message.ConvertedPrice + ' ' + message.FundsType + ' (' + message.SelectedCurrency + ' ' + message.Amount + ')').show();

        } else {

            jQuery('#woo-fc-converted').text('').hide();

        }*/

        jQuery("#woo-fc-qrcode, #woo-fc-qrcode-text, #woo-fc-qrcode-status").hide();

        jQuery("#woo-fc-pay-id").val(message.TransactionPayID);

        jQuery("#place_order").hide().trigger('click');

     } else {

        jQuery("#woo-fc-qrcode, #woo-fc-qrcode-text").hide();

        jQuery("#place_order").show();

    }

    jQuery('#woo-fc-pay-qr-section').hide();

    jQuery('#woo-fc-transaction-section').show();

    jQuery('#woo-fc-refresh-btn').hide();

}



(function( $, window, document ) {

	'use strict';



	/**

	 * All of the code for your public-facing JavaScript source

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



	toggleOrderButton();



    $(document).ready(function () {

        toggleOrderButton();



        $('form.checkout, form#order_review').on('click', 'input[name="payment_method"]', toggleOrderButton);

        $('form.checkout, form#order_review').on('change', 'input[name="payment_method"]', toggleOrderButton);



        $(document).on('click', '#wc-fc-payment-gateway-btn, #woo-fc-refresh-btn', function () {

            var errorarray = [];
            $('form[name="checkout"] .validate-required, .validate-postcode').each(function(){
                var form_required_val = $(this).find(':input').val();
                var labeltitle = 'Shipping ';
                if($(this).closest('form').find('.woocommerce-billing-fields').length > 0){
                    labeltitle = 'Billing '
                }
                if(form_required_val == ''){
                    var alertmessage = '<strong>'+labeltitle+$(this).find('label').text().replace('*', '')+'</strong> is a required field.';
                    errorarray.push(alertmessage);
                    $(this).addClass('validate-required');
                }else{
                    var isemailfield = $(this).find(':input').attr('type');
                    //email validation
                    if(isemailfield == 'email'){
                        var regex = /^([a-zA-Z0-9_.+-])+\@(([a-zA-Z0-9-])+\.)+([a-zA-Z0-9]{2,4})+$/;
                        if(!regex.test(form_required_val)){
                            var alertmessage = 'Invalid '+labeltitle.toLowerCase()+$(this).find('label').text().replace('*', '').toLowerCase();
                            errorarray.push(alertmessage);
                            $(this).addClass('validate-required');
                        }
                    }/*else if(isemailfield == 'tel'){
                        if (/\D/g.test(form_required_val)){
                            var alertmessage = 'Invalid '+labeltitle.toLowerCase()+$(this).find('label').text().replace('*', '').toLowerCase();
                            errorarray.push(alertmessage);
                            $(this).addClass('validate-required');
                        }
                    }*/else{
                        $(this).removeClass('validate-required');
                    }
                }
            });

            if(errorarray.length > 0){
                $('.woocommerce-NoticeGroup').remove();
                var warnningmsg = '<div class="woocommerce-NoticeGroup woocommerce-NoticeGroup-checkout"><ul class="woocommerce-error" role="alert">';
                $.each(errorarray,function(index,value){
                    warnningmsg += '<li>'+value+'</li>';
                }); 
                warnningmsg += '</ul></div>';
                $('form[name="checkout"]').prepend(warnningmsg);
                $('html, body').animate({
                    scrollTop: $(".woocommerce-error").offset().top
                }, 2000);
            }else{
                $('.woocommerce-NoticeGroup').remove();

                fcGetQrCode();
            }

        });



        $(document.body).on('checkout_error', function () {

            $( '#place_order' ).parent().children('button').show();

            $( '#place_order' ).parent().children('[type="button"]').show();

            $( '#place_order' ).parent().children('[type="submit"]').show();

        });



    });



    function fcGetQrCode() {

        var a = 'body';



        $.ajax({

			url: $('#wc-fc-payment-gateway-merchant-code-url').attr('data-value'),

            type: 'post',

            data: {

            	amount: $('#wc-fc-payment-gateway-amount').val(),

                currency: $('#wc-fc-payment-gateway-currency').val(),

                sid: wooFcConnectionId,

                previous_tid: $('#woo-fc-get-transaction-id').data('transactionid'), // Added for auto reload 22122021

        	},

            beforeSend: function() {
                
                if($('#woo-fc-get-transaction-id').data('transactionid') == '0'){
				$(a).block({

					'css' : {'backgroundColor': 'transparent', 'border': 'none', 'width': '64px', 'height': '64px'},

					'message' : '<?xml version="1.0" encoding="UTF-8" standalone="no"?><svg xmlns:svg="http://www.w3.org/2000/svg" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" version="1.0" width="64px" height="64px" viewBox="0 0 128 128" xml:space="preserve"><g><path d="M78.75 16.18V1.56a64.1 64.1 0 0 1 47.7 47.7H111.8a49.98 49.98 0 0 0-33.07-33.08zM16.43 49.25H1.8a64.1 64.1 0 0 1 47.7-47.7V16.2a49.98 49.98 0 0 0-33.07 33.07zm33.07 62.32v14.62A64.1 64.1 0 0 1 1.8 78.5h14.63a49.98 49.98 0 0 0 33.07 33.07zm62.32-33.07h14.62a64.1 64.1 0 0 1-47.7 47.7v-14.63a49.98 49.98 0 0 0 33.08-33.07z" fill="#000000" fill-opacity="1"/><animateTransform attributeName="transform" type="rotate" from="0 64 64" to="-90 64 64" dur="1800ms" repeatCount="indefinite"></animateTransform></g></svg>'});
                }
                    
                    jQuery('#woo-fc-refresh-rate').html('<div class="loader">Loading...</div>');

			}

			

        }).done(function( response ) {

            $(a).unblock();

            clearFcMessage();

            hideFcRefresh();

console.log('asdf done',response);

            if(response.volume_trade_txt && response.local_amount) {

                var t = '<div class="pay-value"><span class="volume-trade">' + response.volume_trade_txt + '</span></div><div class="currency-value">' + response.local_amount + '</div>';

                t += (response.merchant_payout ? '<div id="merchant-payout">' + response.merchant_payout + '</div>' : '');

                t += (response.fiat_currency ? '<div id="fiat-currency">' + response.fiat_currency + '</div>' : '');

                $('#woo-fc-conversion').html(t);

            }

            

            if(response.refresh_second) {

                wooFcIntervalInSec = response.refresh_second;

                $('#woo-fc-refresh-rate').text(response.refresh_second);

                showFcRefresh();

            }



            if(response.ef_address) {

                $('#deposite-address').text(response.ef_address);

            }



            if(response && response.status === 1) {

                if(response.tid) {

                    wooFcTransactionId = response.tid;

                    //add tid //22122021
                    $('#woo-fc-get-transaction-id').data('transactionid',wooFcTransactionId);
                }

                if(response.qr_code) {

                    if(!wooFcQrCode) {

                        wooFcQrCode = new QRCode(document.getElementById("woo-fc-qrcode"), {

                            text: response.qr_code,

                            correctLevel: QRCode.CorrectLevel.L

                        });

                    } else {

                        wooFcQrCode.clear();

                        wooFcQrCode.makeCode(response.qr_code);

                    }

                }

                $('#woo-fc-qrcode-text').show();

                hideFcButton();



                

                if(wooFcSetInterval) {

                    clearInterval(wooFcSetInterval);

                }



                if(wooFcIntervalInSec > 0) {

                    wooFcSetInterval = setInterval(fcGetQrCode, wooFcIntervalInSec * 1000);

                }



                wooFcRunDuration = setInterval(fcRunDuration, 1000);

                

            } else {

                var fcMessage = 'Unknown issue with ForestCoin Payment';

                if(response.message) {

                    fcMessage = response.message;

                }

                setFcMessage(fcMessage);

                hideFcButton();

                $("#woo-pay-qr-inner").hide();

                $("#woo-fc-qrcode").html('');

                $("#woo-fc-qrcode-text").hide();

                /*$('#place_order').show();*/

            }

            $('#wc-fc-payment-gateway-script-wrapper').show();

        })

    }



    function fcRunDuration() {

        var t = parseInt($('#woo-fc-refresh-rate').text());

        if(t > 0) {

            $('#woo-fc-refresh-rate').text(t-1);

        } else {

            clearInterval(wooFcRunDuration);

        }

    }



    function fcPlaceOrder() {

        $('#place_order').trigger('click');

    }



    function showFcButton() {

        $('#wc-fc-payment-gateway-btn').show();

    }

    function hideFcButton() {

        $('#wc-fc-payment-gateway-btn').hide();

    }



    function setFcMessage(fcMessage) {

        $('#woo-fc-qrcode-status').text(fcMessage).addClass('error woo-fc-error');

    }



    function clearFcMessage() {

        $('#woo-fc-qrcode-status').text('').removeClass('error woo-fc-error');

    }



    function showFcRefresh() {

        $('#woo-fc-refresh-text').show();

        $('#woo-fc-refresh-btn').show();

    }



    function hideFcRefresh() {

        $('#woo-fc-refresh-text').hide();

        $('#woo-fc-refresh-btn').hide();

    }



    function toggleOrderButton() {

        if ($('#payment_method_fc_payment_gateway').is(':checked')) {

            if(wooFcRepo != null) {

                fcSetResponse(wooFcRepo);

                hideFcRefresh();

                hideFcButton();

                $('#wc-fc-payment-gateway-checkout-wrapper').toggle(true);

                return;

            }

            if(!$('#woo-fc-qrcode-status').hasClass('woo-fc-error')) {

                $('#place_order').parent().children('button').hide();

                $('#place_order').parent().children('[type="button"]').hide();

                $('#place_order').parent().children('[type="submit"]').hide();

            }

            $('#wc-fc-payment-gateway-checkout-wrapper').toggle(true);

        } else {

            $( '#place_order' ).parent().children('button').show();

            $( '#place_order' ).parent().children('[type="button"]').show();

            $( '#place_order' ).parent().children('[type="submit"]').show();

            $('#wc-fc-payment-gateway-checkout-wrapper').toggle(false);

        }

    }

    //add checkout page zipcode required field span text.
    $(window).load(function(){
        jQuery('.validate-postcode').find('label .optional').addClass('required-cust').text('*').addClass('validate-required-cust');
    });
    $(document).on('change','.woocommerce-billing-fields .country_to_state,.woocommerce-shipping-fields .country_to_state',function(){
        
        jQuery('.validate-postcode').find('label .optional').addClass('required').text('*').addClass('validate-required-cust');
    });

})( jQuery, window, document );