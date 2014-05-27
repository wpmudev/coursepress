function withdraw_confirmed() {
    return confirm(front_vars.withdraw_alert);
}

function withdraw() {
    if (withdraw_confirmed()) {
        return true;
    } else {
        return false;
    }
}

function check_for_mandatory_answers() {

    var mandatory_errors = 0;

    /* Input Text Element validation */
    jQuery('input[type=text]').each(function() {
        if (jQuery(this).attr("data-mandatory") == 'yes') {

            var element_val = jQuery(this).val();

            if (element_val.trim() == '') {
                mandatory_errors++;
            }
        }
    });

    /* Input Textarea Element validation */
    jQuery('textarea').each(function() {
        if (jQuery(this).attr("data-mandatory") == 'yes') {

            var element_val = jQuery(this).val();

            if (element_val.trim() == '') {
                mandatory_errors++;
            }
        }
    });

    /* Input File Element validation */
    jQuery('input[type=file]').each(function() {
        if (jQuery(this).attr("data-mandatory") == 'yes') {

            var element_val = jQuery(this).val();

            if (element_val.trim() == '') {
                mandatory_errors++;
            }
        }
    });

    /* Checkbox Input Element validation*/
    jQuery('.checkbox_answer_group').each(function() {
        if (jQuery(this).attr("data-mandatory") == 'yes') {
            if (jQuery('input[type=checkbox]:checked').length == 0) {
                mandatory_errors++;
            }
        }
    });

    /* Radio Button Input Element validation*/
    jQuery('.radio_answer_group').each(function() {
        if (jQuery(this).attr("data-mandatory") == 'yes') {
            if (jQuery('input[type=radio]:checked').length == 0) {
                mandatory_errors++;
            }
        }
    });

    if (mandatory_errors == 0) {
        return true;
    } else {
        jQuery('.mandatory_message').show("slow");
        return false;
    }
}

jQuery(document).ready(function() {
    jQuery('.save_elements_message_ok').delay(2000).fadeOut('slow');

    /* Submit data on pagination */
    jQuery('.module-pagination a').click(function(e) {
        e.preventDefault();

        //if(check_for_mandatory_answers()){

        //var action = jQuery("#modules_form").attr("action");
        jQuery('#go_to_page').val(jQuery(this).html());

        //jQuery("#modules_form").attr("action", action + 'page/' + jQuery(this).html() + '/');

        jQuery('.apply-button-enrolled').click();
        //return false;
        //}
    });

    jQuery('.submit-elements-data-button').click(function(e) {
        //e.preventDefault();

        var next_page = 0;
        var action = jQuery("#modules_form").attr("action");
        var direct_url = '';
        
        if (e.originalEvent) {//clicked button directly, not pagination

            var active_page = jQuery('#navigation-pagination .active a').html();
            var last_page = jQuery('#navigation-pagination li:last-child a').html();
            

            if (active_page != last_page) {
                next_page = parseInt(active_page) + 1;
            } else {
                direct_url = front_vars.units_archive_url;
                next_page = parseInt(last_page);// done button + 1;
            }
        } else {
            next_page = jQuery('#go_to_page').val();
        }

        //if (!isNaN(active_page)) {}
        if (direct_url != '') {
            //jQuery("#modules_form").attr("action", direct_url);
            jQuery("#modules_form").attr("action", action);
        } else {
            jQuery("#modules_form").attr("action", action + 'page/' + parseInt(next_page) + '/');
        }

        //return false;
    });

});