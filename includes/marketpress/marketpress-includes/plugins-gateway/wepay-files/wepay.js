if (wepay_script.mode == "staging") {
    WePay.set_endpoint("stage");
} else {
    WePay.set_endpoint("production");
}

jQuery(document).ready(function($) {
    $("#mp_payment_form").submit(function(event) {

        if ($('input.mp_choose_gateway').length) {

            // If the payment option selected is not stripe then return and bypass input validations
            if ($('input.mp_choose_gateway:checked').val() != "wepay") {
                return true;
            }
        }

        //clear errors
        $("#wepay_checkout_errors").empty();

        var is_error = false;

        var response = WePay.credit_card.create({
            "client_id": wepay_script.client_id,
            "user_name": $('.card-holdername').val(),
            "email": wepay_script.email,
            "cc_number": $('.card-number').val(),
            "cvv": $('.card-cvc').val(),
            "expiration_month": $('.card-expiry-month').val(),
            "expiration_year": $('.card-expiry-year').val(),
            "address":
                    {
                        "address1": wepay_script.address,
                        "city": wepay_script.city,
                        "state": wepay_script.state,
                        "country": wepay_script.country,
                        "zip": wepay_script.zip
                    }
        }, function(data) {
            if (data.error) {
                // handle error response
                $("#wepay_checkout_errors").append('<div class="mp_checkout_error">' + data.error_description + '</div>');
                jQuery('#wepay_processing').hide();
                is_error = true;
            } else {
                jQuery("#mp_payment_form").append("<input type='hidden' name='payment_method_id' value='" + data.credit_card_id + "' />");
                jQuery("#mp_payment_form").get(0).submit();
                return true;
                //all good, submit the form
            }
        });

        if (is_error) {
            return false;
            jQuery('#mp_payment_confirm').removeAttr("disabled").show();
        }

        // disable the submit button to prevent repeated clicks
        $('#mp_payment_confirm').attr("disabled", "disabled").hide();
        $('#wepay_processing').show();

        return false; // submit form callback
    });
});