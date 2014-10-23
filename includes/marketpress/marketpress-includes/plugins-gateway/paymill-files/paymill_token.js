// CreateToken call below
var PAYMILL_PUBLIC_KEY = paymill_token.public_key;

jQuery(document).ready(function($) {
    var is_error = false;
    
    $(document).ready(function () {
    
        function PaymillResponseHandler(error, result) {

            if (error) {
                if(error.apierror == 'field_invalid_card_cvc'){
                    jQuery("#paymill_checkout_errors").text(paymill_token.invalid_cvc);
                }else if(error.apierror == 'field_invalid_card_exp'){
                    jQuery("#paymill_checkout_errors").text(paymill_token.expired_card);
                }else if(error.apierror == 'field_invalid_card_holder'){
                    jQuery("#paymill_checkout_errors").text(paymill_token.invalid_cardholder);
                }else{
                    jQuery("#paymill_checkout_errors").text(error.apierror);
                }
            } else {
                jQuery("#paymill_checkout_errors").text("");
                var form = jQuery("#mp_payment_form");
                // Token
                var token = result.token;
                // Insert Paymill token field into form in order to post it
                form.append("<input type='hidden' name='paymillToken' value='" + token + "'/>");
                jQuery("#mp_payment_form").get(0).submit();
            }
            jQuery('#mp_payment_confirm').show();
            jQuery("#mp_payment_confirm").removeAttr("disabled");
            jQuery('#paymill_processing').hide();
        }

        jQuery("#mp_payment_form").submit(function (event) {
            
            jQuery('#paymill_processing').show();
            
            // Deactivate submit button on click
            jQuery('#mp_payment_confirm').attr("disabled", "disabled");
            
            if (false == paymill.validateCardNumber(jQuery('.card-number').val())) {
                jQuery("#paymill_checkout_errors").text(paymill_token.invalid_cc_number);
                jQuery('#mp_payment_confirm').show();
                jQuery("#mp_payment_confirm").removeAttr("disabled");
                is_error = true;
                jQuery('#paymill_processing').hide();
                return false;
            }
            
            if (false == paymill.validateExpiry(jQuery('.card-expiry-month').val(), jQuery('.card-expiry-year').val())) {
                jQuery("#paymill_checkout_errors").text(paymill_token.invalid_expiration);
                jQuery('#mp_payment_confirm').show();
                jQuery("#mp_payment_confirm").removeAttr("disabled");
                is_error = true;
                jQuery('#paymill_processing').hide();
                return false;
            }
     
            paymill.createToken({
                number:jQuery('.card-number').val(),
                exp_month:jQuery('.card-expiry-month').val(),
                exp_year:jQuery('.card-expiry-year').val(),
                cvc:jQuery('.card-cvc').val(),
                cardholdername:jQuery('.card-holdername').val(),
                amount:jQuery('.amount').val(),
                currency:jQuery('.currency').val()
            }, PaymillResponseHandler);
            return false;
        });
    });

    jQuery("#mp_payment_form").submit(function(event) {
		
        // We need to only process if the payment 
        // type is Paymill or Paymill payment gateway is the only option

        // If we have the radio buttons allowing the user to select the payment method? ...
        // IF the length is zero then Paymill or some other payment gateway is the only one defined. 
        if ( jQuery('input.mp_choose_gateway').length ) {
			
            // If the payment option selected is not Paymill then return and bypass input validations
            if ( jQuery('input.mp_choose_gateway:checked').val() != "paymill" ) {
                return true;
            }
        }
		
        //clear errors
        jQuery("#paymill_checkout_errors").empty();
  
        if (is_error) return false;
        // disable the submit button to prevent repeated clicks
        jQuery('#mp_payment_confirm').attr("disabled", "disabled").hide();	
        jQuery('#paymill_processing').show();
    });
});