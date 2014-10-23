jQuery(function($) {

    // Firstly, set the publishable key
    //
    // This can either be your live publishable key or test publishable key, depending
    // on which script you included above

    Pin.setPublishableKey(pin_vars.publishable_api_key);

    // Now we can call Pin.js on form submission to retrieve a card token and submit
    // it to the server

    var $form = jQuery('#mp_payment_form'),
            $submitButton = jQuery("#mp_payment_confirm"),
            $errors = jQuery('#pin_checkout_errors');

    $form.submit(function(e) {

        e.preventDefault();
        $errors.hide();

        // Disable the submit button to prevent multiple clicks
        $submitButton.attr({disabled: true});

        // Fetch details required for the createToken call to Pin
        var card = {
            number: $('#cc-number').val(),
            name: $('#cc-name').val(),
            expiry_month: $('#cc-expiry-month').val(),
            expiry_year: $('#cc-expiry-year').val(),
            cvc: $('#cc-cvc').val(),
            address_line1: $('#address-line1').val(),
            address_line2: $('#address-line2').val(),
            address_city: $('#address-city').val(),
            address_state: $('#address-state').val(),
            address_postcode: $('#address-postcode').val(),
            address_country: $('#address-country').val()
        };

        // Request a token for the card from Pin
        Pin.createToken(card, handlePinResponse);
    });

    function handlePinResponse(response) {
        var $form = jQuery('#mp_payment_form');

        if (response.response) {
            // Add the card token and ip address of the customer to the form
            // You will need to post these to Pin when creating the charge.
            $('<input>')
                    .attr({type: 'hidden', name: 'card_token'})
                    .val(response.response.token)
                    .appendTo($form);
            $('<input>')
                    .attr({type: 'hidden', name: 'ip_address'})
                    .val(response.ip_address)
                    .appendTo($form);

            // Resubmit the form
            $form.get(0).submit();

        } else {
            var $errorList = $errors.find('ul');

            $errors.find('h3').text(response.error_description);
            $errorList.empty();

            if (response.messages) {
                $.each(response.messages, function(index, errorMessage) {
                    $('<li>')
                            .html('<font color="red">' + errorMessage.message + '</font>')
                            .appendTo($errorList);
                });
            }

            $errors.show();
            $submitButton.removeAttr('disabled');
        }
    }
    ;
});